<?php

namespace App\Entity;

use App\Model\TimestampableInterface;
use App\Model\TimestampableTrait;
use App\Entity\Media;
use App\DBAL\Types\InnPassportCheckServiceStatusType;
use App\DBAL\Types\PassportVerifyStatusType;
use App\DBAL\Types\PersonalDataSupplyingType;
use App\Model\PersonalDataFrontFormModel;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Model\PersonalDataImportModel;
use App\Model\ResourceInterface;
use App\Model\ResourceTrait;

/**
 * Class PersonalData.
 *
 * @ORM\Table(name="personal_data")
 * @ORM\Entity(repositoryClass="ClientBundle\Entity\Repository\PersonalDataRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class PersonalData implements ResourceInterface, TimestampableInterface
{
    use ResourceTrait;
    use TimestampableTrait;

    public const PASSPORT_VERIFY_CARD = 'personal_data__passport_verify_card';

    public const INN_PASSPORT_CHECK_PROCESS_PRIORITY_INTERNAL   = 1;
    public const INN_PASSPORT_CHECK_PROCESS_PRIORITY_FRONT_FORM = 2;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="inn", type="string", nullable=true)
     */
    private $inn;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passport_firstname", type="string", nullable=true)
     */
    private $passportFirstname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passport_lastname", type="string", nullable=true)
     */
    private $passportLastname;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passport_patronymic", type="string", nullable=true)
     */
    private $passportPatronymic;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="passport_birthdate", type="date", nullable=true)
     */
    private $passportBirthdate;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passport_gender", type="GenderType", nullable=true)
     */
    private $passportGender;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passport_series", type="string", nullable=true)
     */
    private $passportSeries;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passport_number", type="string", nullable=true)
     */
    private $passportNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passport_department_code", type="string", nullable=true)
     */
    private $passportDepartmentCode;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="passport_issued_at", type="date", nullable=true)
     */
    private $passportIssuedAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passport_issued_by", type="string", nullable=true)
     */
    private $passportIssuedBy;

    /**
     * @var string
     *
     * @ORM\Column(name="passport_verify_status", type="PassportVerifyStatusType", options={"default" = PassportVerifyStatusType::NOT_PROCESSED})
     */
    private $passportVerifyStatus = PassportVerifyStatusType::NOT_PROCESSED;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passport_verify_request_id", type="string", nullable=true)
     */
    private $passportVerifyRequestId;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="passport_verify_request_send_at", type="datetime", nullable=true)
     */
    private $passportVerifyRequestSendAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="passport_verify_response_message", type="text", nullable=true)
     */
    private $passportVerifyResponseMessage;

    /**
     * @var string
     *
     * @ORM\Column(name="supplying_type", type="PersonalDataSupplyingType", options={"default" = PersonalDataSupplyingType::CHINA_API})
     */
    private $supplyingType = PersonalDataSupplyingType::CHINA_API;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="supplying_at", type="datetime", nullable=true)
     */
    private $supplyingAt;

    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="Application\Sonata\MediaBundle\Entity\Media", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="external_passport_photo_document_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    private $externalPassportPhotoDocument;

    /**
     * @var bool
     *
     * @ORM\Column(name="blocked", type="boolean", options={"default" = false})
     */
    private $blocked = false;

    /**
     * @var string
     *
     * @ORM\Column(name="inn_passport_check_service_status", type="InnPassportCheckServiceStatusType", options={"default" = InnPassportCheckServiceStatusType::NOT_PROCESSED})
     */
    private $innPassportCheckServiceStatus = InnPassportCheckServiceStatusType::NOT_PROCESSED;

    /**
     * @var string|null
     *
     * @ORM\Column(name="inn_passport_check_service_message", type="text", nullable=true)
     */
    private $innPassportCheckServiceMessage;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="inn_passport_check_service_request_sent_at", type="datetime", nullable=true)
     */
    private $innPassportCheckServiceRequestSentAt;

    /**
     * @return int|string
     */
    public function __toString()
    {
        return $this->getFullname() ?: 'n/a';
    }

    /**
     * @param PersonalDataImportModel $importModel
     * @param string                  $supplyingType
     *
     * @return $this
     */
    public static function createFromImportModel(PersonalDataImportModel $importModel, string $supplyingType): self
    {
        $personalData = new self();
        $personalData
            ->updateFromModel($importModel)
            ->setSupplyingType($supplyingType)
            ->setSupplyingAt(new DateTime())
        ;

        return $personalData;
    }

    /**
     * @param PersonalDataFrontFormModel $personalDataModel
     *
     * @return $this
     */
    public static function createFromFrontModel(PersonalDataFrontFormModel $personalDataModel): self
    {
        $personalData = new self();

        return $personalData->updateFromFrontModel($personalDataModel);
    }

    /**
     * @param PersonalData $personalData
     *
     * @return $this
     *
     * @psalm-suppress InvalidClone
     * @suppress PhanTypePossiblyInvalidCloneNotObject
     */
    public static function copy(PersonalData $personalData): self
    {
        $newPersonalData = new self();
        $newPersonalData
            ->updateData($personalData)
            ->setPassportBirthdate($personalData->getPassportBirthdate() ? clone $personalData->getPassportBirthdate() : null)
            ->setPassportVerifyRequestSendAt($personalData->getPassportVerifyRequestSendAt() ? clone $personalData->getPassportVerifyRequestSendAt() : null)
        ;

        return $newPersonalData;
    }

    /**
     * @param string|null $email
     *
     * @return $this
     */
    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string|null $inn
     *
     * @return $this
     */
    public function setInn($inn): self
    {
        $this->inn = $inn;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * @param string|null $passportFirstname
     *
     * @return $this
     */
    public function setPassportFirstname($passportFirstname): self
    {
        $this->passportFirstname = $passportFirstname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportFirstname()
    {
        return $this->passportFirstname;
    }

    /**
     * @param string|null $passportLastname
     *
     * @return $this
     */
    public function setPassportLastname($passportLastname): self
    {
        $this->passportLastname = $passportLastname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportLastname()
    {
        return $this->passportLastname;
    }

    /**
     * @param string|null $passportPatronymic
     *
     * @return $this
     */
    public function setPassportPatronymic($passportPatronymic): self
    {
        $this->passportPatronymic = $passportPatronymic;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportPatronymic()
    {
        return $this->passportPatronymic;
    }

    /**
     * @param DateTime|null $passportBirthdate
     *
     * @return $this
     */
    public function setPassportBirthdate(?DateTime $passportBirthdate): self
    {
        $this->passportBirthdate = $passportBirthdate;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getPassportBirthdate()
    {
        return $this->passportBirthdate;
    }

    /**
     * @param string|null $passportGender
     *
     * @return $this
     */
    public function setPassportGender($passportGender): self
    {
        $this->passportGender = $passportGender;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportGender()
    {
        return $this->passportGender;
    }

    /**
     * @param string|null $passportSeries
     *
     * @return $this
     */
    public function setPassportSeries($passportSeries): self
    {
        $this->passportSeries = $passportSeries;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportSeries()
    {
        return $this->passportSeries;
    }

    /**
     * @param string|null $passportNumber
     *
     * @return $this
     */
    public function setPassportNumber($passportNumber): self
    {
        $this->passportNumber = $passportNumber;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportNumber()
    {
        return $this->passportNumber;
    }

    /**
     * @param string|null $passportDepartmentCode
     *
     * @return $this
     */
    public function setPassportDepartmentCode($passportDepartmentCode): self
    {
        $this->passportDepartmentCode = $passportDepartmentCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportDepartmentCode()
    {
        return $this->passportDepartmentCode;
    }

    /**
     * @param DateTime|null $passportIssuedAt
     *
     * @return $this
     */
    public function setPassportIssuedAt(?DateTime $passportIssuedAt): self
    {
        $this->passportIssuedAt = $passportIssuedAt;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getPassportIssuedAt()
    {
        return $this->passportIssuedAt;
    }

    /**
     * @param string|null $passportIssuedBy
     *
     * @return $this
     */
    public function setPassportIssuedBy($passportIssuedBy): self
    {
        $this->passportIssuedBy = $passportIssuedBy;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportIssuedBy()
    {
        return $this->passportIssuedBy;
    }

    /**
     * @param string $passportVerifyStatus
     *
     * @return $this
     */
    public function setPassportVerifyStatus($passportVerifyStatus): self
    {
        $this->passportVerifyStatus = $passportVerifyStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassportVerifyStatus()
    {
        return $this->passportVerifyStatus;
    }

    /**
     * @param string|null $passportVerifyRequestId
     *
     * @return $this
     */
    public function setPassportVerifyRequestId($passportVerifyRequestId): self
    {
        $this->passportVerifyRequestId = $passportVerifyRequestId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportVerifyRequestId()
    {
        return $this->passportVerifyRequestId;
    }

    /**
     * @param DateTime|null $passportVerifyRequestSendAt
     *
     * @return $this
     */
    public function setPassportVerifyRequestSendAt(?DateTime $passportVerifyRequestSendAt): self
    {
        $this->passportVerifyRequestSendAt = $passportVerifyRequestSendAt;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getPassportVerifyRequestSendAt()
    {
        return $this->passportVerifyRequestSendAt;
    }

    /**
     * @param string|null $passportVerifyResponseMessage
     *
     * @return $this
     */
    public function setPassportVerifyResponseMessage($passportVerifyResponseMessage): self
    {
        $this->passportVerifyResponseMessage = $passportVerifyResponseMessage;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportVerifyResponseMessage()
    {
        return $this->passportVerifyResponseMessage;
    }

    /**
     * @param string $supplyingType
     *
     * @return $this
     */
    public function setSupplyingType($supplyingType): self
    {
        $this->supplyingType = $supplyingType;

        return $this;
    }

    /**
     * @return string
     */
    public function getSupplyingType()
    {
        return $this->supplyingType;
    }

    /**
     * @param DateTime|null $supplyingAt
     *
     * @return $this
     */
    public function setSupplyingAt(?DateTime $supplyingAt): self
    {
        $this->supplyingAt = $supplyingAt;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getSupplyingAt()
    {
        return $this->supplyingAt;
    }

    /**
     * @param Media|null $externalPassportPhotoDocument
     *
     * @return $this
     */
    public function setExternalPassportPhotoDocument(?Media $externalPassportPhotoDocument): self
    {
        $this->externalPassportPhotoDocument = $externalPassportPhotoDocument;

        return $this;
    }

    /**
     * @return Media|null
     */
    public function getExternalPassportPhotoDocument()
    {
        return $this->externalPassportPhotoDocument;
    }

    /**
     * @param bool $blocked
     *
     * @return $this
     */
    public function setBlocked($blocked): self
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBlocked()
    {
        return $this->blocked;
    }

    /**
     * @param string $innPassportCheckServiceStatus
     *
     * @return $this
     */
    public function setInnPassportCheckServiceStatus($innPassportCheckServiceStatus): self
    {
        $this->innPassportCheckServiceStatus = $innPassportCheckServiceStatus;

        return $this;
    }

    /**
     * @return string
     */
    public function getInnPassportCheckServiceStatus()
    {
        return $this->innPassportCheckServiceStatus;
    }

    /**
     * @param string|null $innPassportCheckServiceMessage
     *
     * @return $this
     */
    public function setInnPassportCheckServiceMessage(?string $innPassportCheckServiceMessage): self
    {
        $this->innPassportCheckServiceMessage = $innPassportCheckServiceMessage;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInnPassportCheckServiceMessage()
    {
        return $this->innPassportCheckServiceMessage;
    }

    /**
     * @param DateTime|null $innPassportCheckServiceRequestSentAt
     *
     * @return $this
     */
    public function setInnPassportCheckServiceRequestSentAt(?DateTime $innPassportCheckServiceRequestSentAt): self
    {
        $this->innPassportCheckServiceRequestSentAt = $innPassportCheckServiceRequestSentAt;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getInnPassportCheckServiceRequestSentAt()
    {
        return $this->innPassportCheckServiceRequestSentAt;
    }

    /**
     * @return string
     */
    public function getPassportBirthdateForSmev(): string
    {
        return $this->passportBirthdate ? $this->passportBirthdate->format('d.m.Y') : '';
    }

    /**
     * @return string
     */
    public function getFullname(): string
    {
        return trim(sprintf(
            '%s %s %s',
            $this->getPassportLastname() ?? '',
            $this->getPassportFirstname() ?? '',
            $this->getPassportPatronymic() ?? ''
        ));
    }

    /**
     * @return string
     */
    public function getPassportDataShort(): string
    {
        return trim(sprintf('%s %s', $this->getPassportSeries() ?? '', $this->getPassportNumber() ?? ''));
    }

    /**
     * @return bool
     */
    public function isChinaApiSupplyingType(): bool
    {
        return $this->getSupplyingType() === PersonalDataSupplyingType::CHINA_API;
    }

    /**
     * @return bool
     */
    public function isChinaManualSupplyingType(): bool
    {
        return $this->getSupplyingType() === PersonalDataSupplyingType::CHINA_MANUAL;
    }

    /**
     * @return bool
     */
    public function isChinaSupplyingType(): bool
    {
        return in_array($this->getSupplyingType(), PersonalDataSupplyingType::$chinaChoices);
    }

    /**
     * @return bool
     */
    public function isRussiaCopySupplyingType(): bool
    {
        return $this->getSupplyingType() === PersonalDataSupplyingType::RUSSIA_COPY;
    }

    /**
     * @return bool
     */
    public function isRussiaFormSupplyingType(): bool
    {
        return $this->getSupplyingType() === PersonalDataSupplyingType::RUSSIA_FORM;
    }

    /**
     * @return bool
     */
    public function isRussiaSupplyingType(): bool
    {
        return in_array($this->getSupplyingType(), PersonalDataSupplyingType::$russiaChoices);
    }

    /**
     * @return bool
     */
    public function isSmevFieldsAndInnFilled(): bool
    {
        return
            $this->getPassportSeries() &&
            $this->getPassportNumber() &&
            $this->getPassportLastname() &&
            $this->getPassportFirstname() &&
            $this->getPassportPatronymic() &&
            $this->getInn()
        ;
    }

    /**
     * @return bool
     */
    public function isSmevCheckRequiredFieldsFilled(): bool
    {
        return
            $this->getPassportSeries() &&
            $this->getPassportNumber() &&
            $this->getPassportLastname() &&
            $this->getPassportFirstname() &&
            $this->getPassportPatronymic() &&
            $this->getPassportBirthdate()
        ;
    }

    /**
     * @return string
     */
    public function getSmevContentAsString(): string
    {
        return sprintf(
            '
                Серия паспорта: %s;
                Номер паспорта: %s;
                Фамилия: %s;
                Имя: %s;
                Отчество: %s;
                Дата рождения: %s;
            ',
            $this->passportSeries ?: 'n/a',
            $this->passportNumber ?: 'n/a',
            $this->passportLastname ?: 'n/a',
            $this->passportFirstname ?: 'n/a',
            $this->passportPatronymic ?: 'n/a',
            $this->passportBirthdate ? $this->passportBirthdate->format('Y-m-d') : 'n/a'
        );
    }

    /**
     * @param PersonalData $personalData
     *
     * @return $this
     */
    public function updateData(PersonalData $personalData): self
    {
        $this
            ->setEmail($personalData->getEmail())
            ->setInn($personalData->getInn())
            ->setPassportFirstname($personalData->getPassportFirstname())
            ->setPassportLastname($personalData->getPassportLastname())
            ->setPassportPatronymic($personalData->getPassportPatronymic())
            ->setPassportBirthdate($personalData->getPassportBirthdate())
            ->setPassportGender($personalData->getPassportGender())
            ->setPassportSeries($personalData->getPassportSeries())
            ->setPassportNumber($personalData->getPassportNumber())
            ->setPassportDepartmentCode($personalData->getPassportDepartmentCode())
            ->setPassportIssuedAt($personalData->getPassportIssuedAt())
            ->setPassportIssuedBy($personalData->getPassportIssuedBy())
            ->setPassportVerifyStatus($personalData->getPassportVerifyStatus())
            ->setPassportVerifyRequestId($personalData->getPassportVerifyRequestId())
            ->setPassportVerifyRequestSendAt($personalData->getPassportVerifyRequestSendAt())
            ->setPassportVerifyResponseMessage($personalData->getPassportVerifyResponseMessage())
            ->setSupplyingType($personalData->getSupplyingType())
            ->setSupplyingAt($personalData->getSupplyingAt())
            ->setExternalPassportPhotoDocument($personalData->getExternalPassportPhotoDocument())
        ;

        return $this;
    }

    /**
     * @param PersonalDataImportModel $model
     *
     * @return $this
     */
    public function updateFromModel(PersonalDataImportModel $model): self
    {
        $this
            ->setEmail($model->getEmail())
            ->setInn($model->getInn())
            ->setPassportFirstname($model->getPassportFirstname())
            ->setPassportLastname($model->getPassportLastname())
            ->setPassportPatronymic($model->getPassportPatronymic())
            ->setPassportBirthdate($model->getPassportBirthdate())
            ->setPassportSeries($model->getPassportSeries())
            ->setPassportNumber($model->getPassportNumber())
            ->setPassportDepartmentCode($model->getPassportDepartmentCode())
            ->setPassportIssuedAt($model->getPassportIssuedAt())
            ->setPassportIssuedBy($model->getPassportIssuedBy())
        ;

        if ($this->getFullname()) {
            $credentials = explode(' ', $this->getFullname());
            $this
                ->setPassportLastname($credentials[0] ?? null)
                ->setPassportFirstname($credentials[1] ?? null)
                ->setPassportPatronymic($credentials[2] ?? null)
            ;
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isValidPassportVerifyStatus(): bool
    {
        return in_array($this->getPassportVerifyStatus(), PassportVerifyStatusType::$choicesOfValid);
    }

    /**
     * @return bool
     *
     * @psalm-suppress PossiblyNullArgument
     */
    public function isValidForPassportCheck(): bool
    {
        if (
            $this->getPassportFirstname() === null
            || $this->getPassportLastname() === null
            || $this->getPassportPatronymic() === null
            || $this->getPassportBirthdate() === null
            || $this->getPassportSeries() === null
            || $this->getPassportNumber() === null
            || $this->getPassportIssuedAt() === null
        ) {
            return false;
        }

        if (strlen($this->getPassportSeries()) !== 4 || strlen($this->getPassportNumber()) !== 6) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isProcessingByInnPassportCheckService(): bool
    {
        return in_array($this->getInnPassportCheckServiceStatus(), [
            InnPassportCheckServiceStatusType::IN_FRONT_FORM_PROCESS,
            InnPassportCheckServiceStatusType::IN_INTERNAL_PROCESS,
        ]);
    }

    /**
     * @return int
     */
    public function getPriorityForPassportCheckQueue(): int
    {
        return $this->isInFrontFormProcessByInnPassportCheck()
            ? self::INN_PASSPORT_CHECK_PROCESS_PRIORITY_FRONT_FORM
            : self::INN_PASSPORT_CHECK_PROCESS_PRIORITY_INTERNAL
        ;
    }

    /**
     * @return bool
     */
    public function isValidInFrontFormByInnPassportCheck(): bool
    {
        return $this->getInnPassportCheckServiceStatus() === InnPassportCheckServiceStatusType::VALID_IN_FRONT_FORM;
    }

    /**
     * @return bool
     */
    public function isInFrontFormProcessByInnPassportCheck(): bool
    {
        return $this->getInnPassportCheckServiceStatus() === InnPassportCheckServiceStatusType::IN_FRONT_FORM_PROCESS;
    }

    /**
     * @return bool
     */
    public function isInInternalProcessByInnPassportCheck(): bool
    {
        return $this->getInnPassportCheckServiceStatus() === InnPassportCheckServiceStatusType::IN_INTERNAL_PROCESS;
    }

    /**
     * Function that reset all fields to default values except that contains in TimestampableTrait and ResourceTrait.
     *
     * @return $this
     */
    public function reset(): self
    {
        return $this
            ->setEmail(null)
            ->setInn(null)
            ->setPassportFirstname(null)
            ->setPassportLastname(null)
            ->setPassportPatronymic(null)
            ->setPassportBirthdate(null)
            ->setPassportGender(null)
            ->setPassportSeries(null)
            ->setPassportNumber(null)
            ->setPassportDepartmentCode(null)
            ->setPassportIssuedAt(null)
            ->setPassportIssuedBy(null)
            ->setPassportVerifyStatus(PassportVerifyStatusType::NOT_PROCESSED)
            ->setPassportVerifyRequestId(null)
            ->setPassportVerifyRequestSendAt(null)
            ->setPassportVerifyResponseMessage(null)
            ->setSupplyingType(PersonalDataSupplyingType::CHINA_API)
            ->setSupplyingAt(null)
            ->setExternalPassportPhotoDocument(null)
            ->setBlocked(false)
            ->setInnPassportCheckServiceStatus(InnPassportCheckServiceStatusType::NOT_PROCESSED)
            ->setInnPassportCheckServiceMessage(null)
            ->setInnPassportCheckServiceRequestSentAt(null)
        ;
    }

    /**
     * @param PersonalDataFrontFormModel $personalDataModel
     *
     * @return $this
     */
    public function updateFromFrontModel(PersonalDataFrontFormModel $personalDataModel): self
    {
        return $this
            ->setEmail($personalDataModel->getEmail())
            ->setInn($personalDataModel->getInn())
            ->setPassportFirstname($personalDataModel->getPassportFirstname())
            ->setPassportLastname($personalDataModel->getPassportLastname())
            ->setPassportPatronymic($personalDataModel->getPassportPatronymic())
            ->setPassportBirthdate($personalDataModel->getPassportBirthdate())
            ->setPassportGender($personalDataModel->getGender())
            ->setPassportSeries($personalDataModel->getPassportSeries())
            ->setPassportNumber($personalDataModel->getPassportNumber())
            ->setPassportDepartmentCode($personalDataModel->getPassportDepartmentCode())
            ->setPassportIssuedAt($personalDataModel->getPassportIssuedAt())
            ->setPassportIssuedBy($personalDataModel->getPassportIssuedBy())
            ->setSupplyingType(PersonalDataSupplyingType::RUSSIA_FORM)
            ->setSupplyingAt(new DateTime())
            ->setExternalPassportPhotoDocument($personalDataModel->getPassportPhoto())
            ->setInnPassportCheckServiceStatus(InnPassportCheckServiceStatusType::NOT_PROCESSED)
        ;
    }
}
