<?php

namespace App\PassportCheckService;

use App\Entity\PersonalData;
use App\PassportCheckService\DBAL\Types\PassportCheckResultStatusType;
use App\PassportCheckService\Exception\CurlFailedException;
use App\PassportCheckService\Exception\CurlResponseDecodeFailedException;
use App\PassportCheckService\Exception\NotValidPersonalDataException;
use App\PassportCheckService\Form\Type\CurlResponseDataType;
use App\PassportCheckService\Model\CurlResponse;
use App\PassportCheckService\Model\CurlResponseData;
use App\PassportCheckService\Model\PassportCheckResult;
use App\PassportCheckService\Model\PassportData;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PassportCheckService.
 */
class PassportCheckService
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var string
     */
    private $url;

    /**
     * PassportCheckService constructor.
     *
     * @param LoggerInterface      $logger
     * @param FormFactoryInterface $formFactory
     * @param string               $url
     */
    public function __construct(LoggerInterface $logger, FormFactoryInterface $formFactory, string $url)
    {
        $this->logger      = $logger;
        $this->formFactory = $formFactory;
        $this->url         = $url;
    }

    /**
     * @param PersonalData $personalData
     *
     * @throws NotValidPersonalDataException
     * @throws CurlFailedException
     * @throws CurlResponseDecodeFailedException
     *
     * @return PassportCheckResult
     */
    public function checkPassport(PersonalData $personalData): PassportCheckResult
    {
        if (!$personalData->isValidForPassportCheck()) {
            $this->logger->error("PersonalData #{$personalData->getId()} is not valid for passport check process");

            throw new NotValidPersonalDataException();
        }

        $curlResponse = $this->sendRequest(PassportData::createFromPersonalData($personalData));

        return $this->handleCurlResponse($curlResponse);
    }

    /**
     * @param CurlResponse $curlResponse
     *
     * @return PassportCheckResult
     */
    public function handleCurlResponse(CurlResponse $curlResponse): PassportCheckResult
    {
        $curlResponseData = $curlResponse->getData();

        if ($curlResponse->getHttpCode() !== Response::HTTP_OK) {
            return $this->handleNotSuccessCurlResponse($curlResponseData);
        }

        $handledCurlResponseData = new CurlResponseData();
        $form                    = $this->formFactory->create(CurlResponseDataType::class, $handledCurlResponseData);

        $form->submit($curlResponseData);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return PassportCheckResult::createFailedResult(
                PassportCheckResultStatusType::INVALID_RESPONSE_DATA,
                $this->encodeCurlResponseData($curlResponseData)
            );
        }

        if ($handledCurlResponseData->isPassportValid()) {
            return PassportCheckResult::createValid($handledCurlResponseData);
        }

        return PassportCheckResult::createInvalid($handledCurlResponseData);
    }

    /**
     * @param array $curlResponseData
     *
     * @return PassportCheckResult
     */
    private function handleNotSuccessCurlResponse(array $curlResponseData): PassportCheckResult
    {
        if (!isset($curlResponseData['ERRORS']) || count($curlResponseData['ERRORS']) === 0) {
            return PassportCheckResult::createFailedResult(
                PassportCheckResultStatusType::UNKNOWN_ERROR,
                $this->encodeCurlResponseData($curlResponseData)
            );
        }

        $errors = $curlResponseData['ERRORS'];
        if (isset($errors['captcha'])) {
            return PassportCheckResult::createFailedResult(PassportCheckResultStatusType::CAPTURE_REQUIRED);
        }

        return PassportCheckResult::createFailedResult(
            PassportCheckResultStatusType::INVALID_REQUEST_DATA,
            $this->encodeCurlResponseData($curlResponseData)
        );
    }

    /**
     * @param array $curlResponseData
     *
     * @return string|null
     *
     * @phan-suppress PhanPartialTypeMismatchReturn
     */
    private function encodeCurlResponseData(array $curlResponseData): ?string
    {
        $encodedResponse = null;

        try {
            $encodedResponse = json_encode($curlResponseData);
        } catch (Exception $e) {
            $this->logger->error('PassportCheckService failed at encode data of curl response');
        }

        return $encodedResponse;
    }

    /**
     * @param PassportData $passportData
     *
     * @throws CurlFailedException
     * @throws CurlResponseDecodeFailedException
     *
     * @return CurlResponse
     *
     * @psalm-suppress InvalidScalarArgument
     * @phan-suppress PhanPartialTypeMismatchArgumentInternal
     */
    private function sendRequest(PassportData $passportData): CurlResponse
    {
        $requestQuery = $this->buildRequestQuery($passportData);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestQuery);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $responseData = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        curl_close($ch);

        if ($responseData === false) {
            $this->logger->error("Curl failed at send passport check request. Request query: {$requestQuery}");

            throw new CurlFailedException();
        }

        try {
            $jsonData = json_decode($responseData, true);
        } catch (Exception $e) {
            $this->logger->error("Curl failed at decode passport check response. Response: {$responseData}");

            throw new CurlResponseDecodeFailedException();
        }

        return new CurlResponse($httpCode, $jsonData);
    }

    /**
     * @param PassportData $passportData
     *
     * @return string
     */
    private function buildRequestQuery(PassportData $passportData): string
    {
        return http_build_query([
            'fam'          => $passportData->getLastname(),
            'nam'          => $passportData->getFirstname(),
            'otch'         => $passportData->getPatronymic(),
            'bdate'        => $passportData->getBirthdate(),
            'bplace'       => '',
            'doctype'      => '21',
            'docno'        => $passportData->getSeriesAndNumberFormatted(),
            'docdt'        => $passportData->getIssuedAt(),
            'c'            => 'innMy',
            'captcha'      => '',
            'captchaToken' => '',
        ]);
    }
}
