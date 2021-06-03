<?php

namespace App\Model;

use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Class PersonalDataImportModel.
 */
class PersonalDataImportModel
{
    public const API_VALIDATION_GROUP   = 'personal_data_import_model.api';
    public const EXCEL_VALIDATION_GROUP = 'personal_data_import_model.excel';

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $inn;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={PersonalDataImportModel::API_VALIDATION_GROUP})
     */
    protected $passportFirstname;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={PersonalDataImportModel::API_VALIDATION_GROUP})
     */
    protected $passportLastname;

    /**
     * @var string
     *
     * @Assert\NotBlank(groups={PersonalDataImportModel::API_VALIDATION_GROUP})
     */
    protected $passportPatronymic;

    /**
     * @var DateTime
     */
    protected $passportBirthdate;

    /**
     * @var string
     */
    protected $passportSeries;

    /**
     * @var string
     */
    protected $passportNumber;

    /**
     * @var string
     */
    protected $passportDepartmentCode;

    /**
     * @var DateTime
     */
    protected $passportIssuedAt;

    /**
     * @var string
     */
    protected $passportIssuedBy;

    /**
     * @var string|null
     */
    protected $fullName;

    /**
     * @Assert\Callback(groups={PersonalDataImportModel::EXCEL_VALIDATION_GROUP})
     *
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        if ($this->getFullName() || ($this->getPassportFirstname() && $this->getPassportLastname())) {
            return;
        }

        $context
            ->buildViolation('Personal data required. Provide full name or first name and last name')
            ->addViolation()
        ;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $inn
     *
     * @return $this
     */
    public function setInn(string $inn)
    {
        $this->inn = $inn;

        return $this;
    }

    /**
     * @return string
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * @param string $passportFirstname
     *
     * @return $this
     */
    public function setPassportFirstname(string $passportFirstname)
    {
        $this->passportFirstname = $passportFirstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassportFirstname()
    {
        return $this->passportFirstname;
    }

    /**
     * @param string $passportLastname
     *
     * @return $this
     */
    public function setPassportLastname(string $passportLastname)
    {
        $this->passportLastname = $passportLastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassportLastname()
    {
        return $this->passportLastname;
    }

    /**
     * @param string $passportPatronymic
     *
     * @return $this
     */
    public function setPassportPatronymic(string $passportPatronymic)
    {
        $this->passportPatronymic = $passportPatronymic;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassportPatronymic()
    {
        return $this->passportPatronymic;
    }

    /**
     * @param DateTime $passportBirthdate
     *
     * @return $this
     */
    public function setPassportBirthdate(DateTime $passportBirthdate)
    {
        $this->passportBirthdate = $passportBirthdate;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getPassportBirthdate()
    {
        return $this->passportBirthdate;
    }

    /**
     * @param string $passportSeries
     *
     * @return $this
     */
    public function setPassportSeries(string $passportSeries)
    {
        $this->passportSeries = $passportSeries;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassportSeries()
    {
        return $this->passportSeries;
    }

    /**
     * @param string $passportNumber
     *
     * @return $this
     */
    public function setPassportNumber(string $passportNumber)
    {
        $this->passportNumber = $passportNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassportNumber()
    {
        return $this->passportNumber;
    }

    /**
     * @param string $passportDepartmentCode
     *
     * @return $this
     */
    public function setPassportDepartmentCode(string $passportDepartmentCode)
    {
        $this->passportDepartmentCode = $passportDepartmentCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassportDepartmentCode()
    {
        return $this->passportDepartmentCode;
    }

    /**
     * @param DateTime $passportIssuedAt
     *
     * @return $this
     */
    public function setPassportIssuedAt(DateTime $passportIssuedAt)
    {
        $this->passportIssuedAt = $passportIssuedAt;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getPassportIssuedAt()
    {
        return $this->passportIssuedAt;
    }

    /**
     * @param string $passportIssuedBy
     *
     * @return $this
     */
    public function setPassportIssuedBy(string $passportIssuedBy)
    {
        $this->passportIssuedBy = $passportIssuedBy;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassportIssuedBy()
    {
        return $this->passportIssuedBy;
    }

    /**
     * @param string|null $fullName
     *
     * @return $this
     */
    public function setFullName(?string $fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFullName()
    {
        return $this->fullName;
    }
}
