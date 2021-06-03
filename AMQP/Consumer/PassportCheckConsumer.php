<?php

namespace App\AMQP\Consumer;

use App\Entity\Manager\RabbitMQVariablesManager;
use App\Entity\RabbitMQVariables;
use App\AMQP\Exception\AMQPMessageBodyInvalidException;
use App\AMQP\Exception\AMQPMessageDecodeFailedException;
use App\DBAL\Types\InnPassportCheckServiceStatusType;
use App\DBAL\Types\PersonalDataStatusType;
use App\Entity\Manager\PersonalDataManager;
use App\Entity\PersonalData;
use App\PassportCheckService\DBAL\Types\PassportCheckResultStatusType;
use App\PassportCheckService\Model\PassportCheckResult;
use App\PassportCheckService\PassportCheckService;
use App\Entity\ClientUpload;
use App\Entity\Manager\ClientUploadManager;
use DateTime;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

/**
 * Class PassportCheckConsumer.
 */
class PassportCheckConsumer implements ConsumerInterface
{
    public const MESSAGE_EXPIRATION_TIME_DEFAULT                  = 10000;
    public const MESSAGE_EXPIRATION_TIME_BY_BORN_DEFAULT          = 86400; // seconds in day
    public const PROCESS_POSSIBILITY_COOLDOWN                     = 9;
    public const PROCESS_POSSIBILITY_COOLDOWN_BY_CAPTURE_REQUIRED = 90;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProducerInterface
     */
    private $delayedProducer;

    /**
     * @var PassportCheckService
     */
    private $passportCheckService;

    /**
     * @var PersonalDataManager
     */
    private $personalDataManager;

    /**
     * @var ClientUploadManager
     */
    private $clientUploadManager;

    /**
     * @var RabbitMQVariablesManager
     */
    private $rabbitMQVariablesManager;

    /**
     * @var int
     *
     * @description Value in milliseconds.
     */
    private $messageExpirationTime = self::MESSAGE_EXPIRATION_TIME_DEFAULT;

    /**
     * @var int
     *
     * @description Value in seconds.
     */
    private $messageExpirationTimeByBorn = self::MESSAGE_EXPIRATION_TIME_DEFAULT;

    /**
     * @var int
     *
     * @description Value in seconds.
     */
    private $processPossibilityCooldown = self::PROCESS_POSSIBILITY_COOLDOWN;

    /**
     * @var int
     *
     * @description Value in seconds.
     */
    private $processPossibilityCooldownByCaptureRequired = self::PROCESS_POSSIBILITY_COOLDOWN_BY_CAPTURE_REQUIRED;

    /**
     * @var RabbitMQVariables|null
     */
    private $rabbitMQVariables;

    /**
     * PassportCheckConsumer constructor.
     *
     * @param LoggerInterface          $passportCheckServiceLogger
     * @param ProducerInterface        $delayedProducer
     * @param PassportCheckService     $passportCheckService
     * @param PersonalDataManager      $personalDataManager
     * @param ClientUploadManager      $clientUploadManager
     * @param RabbitMQVariablesManager $rabbitMQVariablesManager
     */
    public function __construct(
        LoggerInterface $passportCheckServiceLogger,
        ProducerInterface $delayedProducer,
        PassportCheckService $passportCheckService,
        PersonalDataManager $personalDataManager,
        ClientUploadManager $clientUploadManager,
        RabbitMQVariablesManager $rabbitMQVariablesManager
    ) {
        $this->logger                   = $passportCheckServiceLogger;
        $this->delayedProducer          = $delayedProducer;
        $this->passportCheckService     = $passportCheckService;
        $this->personalDataManager      = $personalDataManager;
        $this->clientUploadManager      = $clientUploadManager;
        $this->rabbitMQVariablesManager = $rabbitMQVariablesManager;
    }

    /**
     * @param int $messageExpirationTime
     *
     * @return $this
     */
    public function setMessageExpirationTime(int $messageExpirationTime): self
    {
        $this->messageExpirationTime = $messageExpirationTime;

        return $this;
    }

    /**
     * @param int $messageExpirationTimeByBorn
     *
     * @return $this
     */
    public function setMessageExpirationTimeByBorn(int $messageExpirationTimeByBorn): self
    {
        $this->messageExpirationTimeByBorn = $messageExpirationTimeByBorn;

        return $this;
    }

    /**
     * @param int $processPossibilityCooldown
     *
     * @return $this
     */
    public function setProcessPossibilityCooldown(int $processPossibilityCooldown): self
    {
        $this->processPossibilityCooldown = $processPossibilityCooldown;

        return $this;
    }

    /**
     * @param int $processPossibilityCooldownByCaptureRequired
     *
     * @return $this
     */
    public function setProcessPossibilityCooldownByCaptureRequired(int $processPossibilityCooldownByCaptureRequired): self
    {
        $this->processPossibilityCooldownByCaptureRequired = $processPossibilityCooldownByCaptureRequired;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(AMQPMessage $msg)
    {
        try {
            $messageBody = $this->validateMessageBody($this->decodeMessageBody($msg));
        } catch (Exception $e) {
            $this->logger->error("{$e->getMessage()}. Message body {$msg->getBody()}.");

            return self::MSG_REJECT;
        }

        if ($this->isExpiredByBorn($messageBody['born_timestamp'])) {
            $this->logger->error("Message expired by born time. Message body: {$msg->getBody()}.");

            return self::MSG_REJECT;
        }

        $personalData = $this->getPersonalData($messageBody);
        if ($personalData === null) {
            $this->clearEntityManager();

            return self::MSG_REJECT;
        }

        if (!$personalData->isProcessingByInnPassportCheckService()) {
            $this->logger->error("
                Passport data #{$personalData->getId()} not has processing passport check process. 
                InnPassportCheckServiceStatus: {$personalData->getInnPassportCheckServiceStatus()}. 
                Message was rejected.
            ");

            $this->clearEntityManager();

            return self::MSG_REJECT;
        }

        $this->rabbitMQVariables = $this->rabbitMQVariablesManager->getRabbitMQVariables();
        if (!$this->isAllowForProcessByTiming()) {
            $this->clearEntityManager();

            return $this->rabbitMQVariables->isPassportCheckCaptureRequired()
                ? $this->delayMessageByCapture($messageBody, $personalData->getPriorityForPassportCheckQueue())
                : $this->delayMessageByDefault($messageBody, $personalData->getPriorityForPassportCheckQueue())
            ;
        }

        return $this->process($personalData, $messageBody);
    }

    /**
     * @param PersonalData $personalData
     * @param array        $messageBody
     *
     * @return int
     *
     * @phan-suppress PhanPossiblyFalseTypeArgument
     */
    private function process(PersonalData $personalData, array $messageBody): int
    {
        $requestSentAt = new DateTime();
        $personalData->setInnPassportCheckServiceRequestSentAt($requestSentAt);

        try {
            $passportCheckResult = $this->passportCheckService->checkPassport($personalData);
        } catch (Exception $e) {
            $this->logger->error("
                Can't check passport of personal data #{$personalData->getId()}. 
                Error message: {$e->getMessage()}.
            ");

            $this->clearEntityManager();

            return self::MSG_REJECT;
        }

        $status          = ConsumerInterface::MSG_REJECT;
        $captureRequired = false;

        switch ($passportCheckResult->getStatus()) {
            case PassportCheckResultStatusType::VALID:
            case PassportCheckResultStatusType::INVALID:
                $status = self::MSG_ACK;

                $this->updatePersonalData($personalData, $passportCheckResult);

                break;
            case PassportCheckResultStatusType::INVALID_REQUEST_DATA:
            case PassportCheckResultStatusType::INVALID_RESPONSE_DATA:
                $status = self::MSG_REJECT;

                $this->updatePersonalData($personalData, $passportCheckResult);

                break;
            case PassportCheckResultStatusType::CAPTURE_REQUIRED:
                $status          = $this->delayMessageByCapture($messageBody, $personalData->getPriorityForPassportCheckQueue());
                $captureRequired = true;

                break;
        }

        $this->rabbitMQVariables
            ->setPassportCheckLastRequestSentAtTimestamp($requestSentAt->getTimestamp())
            ->setPassportCheckCaptureRequired($captureRequired)
        ;

        $this->rabbitMQVariablesManager->save($this->rabbitMQVariables);

        $this->logger->info("
            Passport check status: {$passportCheckResult->getStatus()}, personal data #{$personalData->getId()}.
        ");

        $this->clearEntityManager();

        return $status;
    }

    /**
     * @param array $messageBody
     *
     * @return PersonalData|null
     */
    private function getPersonalData(array $messageBody): ?PersonalData
    {
        try {
            $personalData = $this->personalDataManager->getOne(['id' => $messageBody['personal_data_id']]);
        } catch (Exception $e) {
            $this->logger->error("
                Can't get personal data #{$messageBody['personal_data_id']} form database. 
                Error message: {$e->getMessage()}.
            ");

            return null;
        }

        if ($personalData === null) {
            $this->logger->error("Personal data #{$messageBody['personal_data_id']} not exist.");
        }

        return $personalData;
    }

    /**
     * @param PersonalData        $personalData
     * @param PassportCheckResult $passportCheckResult
     */
    private function updatePersonalData(PersonalData $personalData, PassportCheckResult $passportCheckResult)
    {
        $inn = $personalData->getInn();
        if ($passportCheckResult->getStatus() === PassportCheckResultStatusType::VALID) {
            $innPassportCheckServiceMessage = null;
            if ($personalData->getInn() !== null && $personalData->getInn() !== $passportCheckResult->getInn()) {
                $innPassportCheckServiceMessage = "ИНН был изменен с {$personalData->getInn()} на {$passportCheckResult->getInn()}";
            }

            $inn                           = $passportCheckResult->getInn();
            $innPassportCheckServiceStatus = $personalData->isInFrontFormProcessByInnPassportCheck()
                ? InnPassportCheckServiceStatusType::VALID_IN_FRONT_FORM
                : InnPassportCheckServiceStatusType::VALID_INTERNAL
            ;
        } else {
            $innPassportCheckServiceMessage = $passportCheckResult->getEncodedResponse();
            $innPassportCheckServiceStatus  = InnPassportCheckServiceStatusType::FAILED;
            if ($passportCheckResult->getStatus() === PassportCheckResultStatusType::INVALID) {
                $innPassportCheckServiceStatus = $personalData->isInFrontFormProcessByInnPassportCheck()
                    ? InnPassportCheckServiceStatusType::NOT_VALID_IN_FRONT_FORM
                    : InnPassportCheckServiceStatusType::NOT_VALID_INTERNAL
                ;
            }
        }

        $this->updateClientUploadAndPersonalData(
            $personalData,
            $inn,
            $innPassportCheckServiceMessage,
            $innPassportCheckServiceStatus
        );
    }

    /**
     * @param PersonalData $personalData
     * @param string|null  $inn
     * @param string|null  $innPassportCheckServiceMessage
     * @param string       $innPassportCheckServiceStatus
     */
    private function updateClientUploadAndPersonalData(
        PersonalData $personalData,
        ?string $inn,
        ?string $innPassportCheckServiceMessage,
        string $innPassportCheckServiceStatus
    ) {
        $clientUpload = $this->findClientUpload($personalData);
        if ($clientUpload === null) {
            $this->logger->error("
                Error occurred at process passport check AMQP message: clientUpload 
                of personal data {$personalData->getId()} not found.
            ");

            return;
        }

        if (!$clientUpload->isAllowForPersonalDataFrontForm()) {
            $this->logger->error("
                Error occurred at process passport check AMQP message: clientUpload 
                of personal data {$personalData->getId()} in not allow personal data status.
            ");

            $inn                            = null;
            $innPassportCheckServiceMessage = 'Связанная КлиентЗагрузка в недопустимом статусе';
            $innPassportCheckServiceStatus  = $personalData->isInInternalProcessByInnPassportCheck()
                ? InnPassportCheckServiceStatusType::NOT_VALID_INTERNAL
                : InnPassportCheckServiceStatusType::NOT_VALID_IN_FRONT_FORM
            ;
        } elseif ($personalData->isInInternalProcessByInnPassportCheck() && $personalData->isRussiaSupplyingType()) {
            $clientUpload
                ->setPersonalDataStatus(
                    $innPassportCheckServiceStatus === InnPassportCheckServiceStatusType::VALID_INTERNAL
                        ? PersonalDataStatusType::AUTO_PROCESS_COMPLETED
                        : PersonalDataStatusType::AUTO_PROCESS_FAILED
                )
            ;
        }

        $personalData
            ->setInn($inn)
            ->setInnPassportCheckServiceMessage($innPassportCheckServiceMessage)
            ->setInnPassportCheckServiceStatus($innPassportCheckServiceStatus)
        ;

        try {
            $this->clientUploadManager->save($clientUpload);
        } catch (Exception $e) {
            $this->logger->error("
                Error occurred at try save client upload #{$clientUpload->getId()}. 
                Error message: {$e->getMessage()}.
            ");
        }
    }

    /**
     * @param PersonalData $personalData
     *
     * @return ClientUpload|null
     */
    private function findClientUpload(PersonalData $personalData): ?ClientUpload
    {
        try {
            $clientUpload = $this->clientUploadManager->getOne(['personal_data' => $personalData]);
        } catch (Exception $e) {
            $this->logger->error("PersonalData #{$personalData->getId()} has multiple ClientUploads");

            return null;
        }

        return $clientUpload;
    }

    /**
     * @param array $messageBody
     * @param int   $priority
     *
     * @return int
     */
    private function delayMessageByCapture(array $messageBody, int $priority): int
    {
        return $this->delayMessage($messageBody, $this->processPossibilityCooldownByCaptureRequired * 1000, $priority);
    }

    /**
     * @param array $messageBody
     * @param int   $priority
     *
     * @return int
     */
    private function delayMessageByDefault(array $messageBody, int $priority): int
    {
        return $this->delayMessage($messageBody, $this->messageExpirationTime, $priority);
    }

    /**
     * @param array $messageBody
     * @param int   $expirationTime
     * @param int   $priority
     *
     * @return int
     *
     * @phan-suppress PhanPossiblyFalseTypeArgument
     */
    private function delayMessage(array $messageBody, int $expirationTime, int $priority): int
    {
        $this->delayedProducer->publish(json_encode($messageBody), '', [
            'expiration' => $expirationTime,
            'priority'   => $priority,
        ]);

        return self::MSG_ACK;
    }

    /**
     * @param array $messageBody
     *
     * @throws AMQPMessageBodyInvalidException
     *
     * @return array
     */
    private function validateMessageBody(array $messageBody): array
    {
        if (!isset($messageBody['personal_data_id']) || !isset($messageBody['born_timestamp'])) {
            throw new AMQPMessageBodyInvalidException();
        }

        return $messageBody;
    }

    /**
     * @param AMQPMessage $msg
     *
     * @throws AMQPMessageDecodeFailedException
     *
     * @return array
     *
     * @phan-suppress PhanPartialTypeMismatchReturn
     */
    private function decodeMessageBody(AMQPMessage $msg): array
    {
        $messageBody = json_decode($msg->getBody(), true);
        if ($messageBody === false) {
            throw new AMQPMessageDecodeFailedException();
        }

        return $messageBody;
    }

    /**
     * @return bool
     *
     * @phan-suppress PhanPartialTypeMismatchArgumentInternal
     * @psalm-suppress PossiblyNullOperand
     */
    private function isAllowForProcessByTiming(): bool
    {
        $lastSentAtTimestamp = $this->rabbitMQVariables->getPassportCheckLastRequestSentAtTimestamp();
        if ($lastSentAtTimestamp === null) {
            return true;
        }

        $timeDiff = time() - $lastSentAtTimestamp;

        $isCaptureRequired = $this->rabbitMQVariables->isPassportCheckCaptureRequired();
        if ($isCaptureRequired) {
            return $timeDiff >= $this->processPossibilityCooldownByCaptureRequired;
        }

        return $timeDiff >= $this->processPossibilityCooldown;
    }

    /**
     * @param int $messageBornTime
     *
     * @return bool
     */
    private function isExpiredByBorn(int $messageBornTime): bool
    {
        if (time() - $messageBornTime >= $this->messageExpirationTimeByBorn) {
            return true;
        }

        return false;
    }

    /**
     * @description Function that clear EntityManager and RabbitMQVariablesManager::rabbitMQVariables.
     */
    private function clearEntityManager()
    {
        try {
            $this->personalDataManager->getEntityManager()->clear();
        } catch (Exception $e) {
            $this->logger->error('Error occurred at try clear EntityManager.');
        }

        $this->rabbitMQVariablesManager->clearRabbitMQVariables();
    }
}
