<?php

namespace App\PassportCheckService\Model;

use App\Entity\PersonalData;

/**
 * Class PassportData.
 */
class PassportData
{
    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var string
     */
    private $patronymic;

    /**
     * @var string
     */
    private $birthdate;

    /**
     * @var string
     */
    private $series;

    /**
     * @var string
     */
    private $number;

    /**
     * @var string
     */
    private $issuedAt;

    /**
     * @param PersonalData $personalData
     *
     * @return $this
     *
     * @psalm-suppress PossiblyNullArgument
     */
    public static function createFromPersonalData(PersonalData $personalData): self
    {
        $passportData = new self();
        $passportData
            ->setFirstname($personalData->getPassportFirstname())
            ->setLastname($personalData->getPassportLastname())
            ->setPatronymic($personalData->getPassportPatronymic())
            ->setBirthdate($personalData->getPassportBirthdate()->format('d.m.Y'))
            ->setSeries($personalData->getPassportSeries())
            ->setNumber($personalData->getPassportNumber())
            ->setIssuedAt($personalData->getPassportIssuedAt()->format('d.m.Y'))
        ;

        return $passportData;
    }

    /**
     * @param string $firstname
     *
     * @return $this
     */
    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname(): string
    {
        return $this->firstname;
    }

    /**
     * @param string $lastname
     *
     * @return $this
     */
    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastname(): string
    {
        return $this->lastname;
    }

    /**
     * @param string $patronymic
     *
     * @return $this
     */
    public function setPatronymic(string $patronymic): self
    {
        $this->patronymic = $patronymic;

        return $this;
    }

    /**
     * @return string
     */
    public function getPatronymic(): string
    {
        return $this->patronymic;
    }

    /**
     * @param string $birthdate
     *
     * @return $this
     */
    public function setBirthdate(string $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * @return string
     */
    public function getBirthdate(): string
    {
        return $this->birthdate;
    }

    /**
     * @param string $series
     *
     * @return $this
     */
    public function setSeries(string $series): self
    {
        $this->series = $series;

        return $this;
    }

    /**
     * @return string
     */
    public function getSeries(): string
    {
        return $this->series;
    }

    /**
     * @param string $number
     *
     * @return $this
     */
    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * @param string $issuedAt
     *
     * @return $this
     */
    public function setIssuedAt(string $issuedAt): self
    {
        $this->issuedAt = $issuedAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getIssuedAt(): string
    {
        return $this->issuedAt;
    }

    /**
     * @return string
     */
    public function getSeriesAndNumberFormatted(): string
    {
        return sprintf(
            '%s %s %s',
            substr($this->getSeries(), 0, 2),
            substr($this->getSeries(), 2, 2),
            $this->getNumber()
        );
    }
}
