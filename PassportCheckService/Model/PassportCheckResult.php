<?php

namespace App\PassportCheckService\Model;

use App\PassportCheckService\DBAL\Types\PassportCheckResultStatusType;

/**
 * Class PassportCheckResult.
 */
class PassportCheckResult
{
    /**
     * @var string|null
     */
    private $inn;

    /**
     * @var bool|null
     */
    private $captchaRequired;

    /**
     * @var string
     */
    private $status;

    /**
     * @var array|null
     */
    private $errors;

    /**
     * @var string|null
     */
    private $encodedResponse;

    /**
     * @param CurlResponseData $curlResponseData
     *
     * @return $this
     */
    public static function createValid(CurlResponseData $curlResponseData): self
    {
        $result = new self();
        $result
            ->setInn($curlResponseData->getInn())
            ->setCaptchaRequired($curlResponseData->isCaptchaRequired())
            ->setStatus(PassportCheckResultStatusType::VALID)
        ;

        return $result;
    }

    /**
     * @param CurlResponseData $curlResponseData
     *
     * @return $this
     */
    public static function createInvalid(CurlResponseData $curlResponseData): self
    {
        $result = new self();
        $result
            ->setCaptchaRequired($curlResponseData->isCaptchaRequired())
            ->setStatus(PassportCheckResultStatusType::INVALID)
        ;

        return $result;
    }

    /**
     * @param string      $status
     * @param string|null $encodedResponse
     *
     * @return $this
     */
    public static function createFailedResult(string $status, string $encodedResponse = null): self
    {
        $result = new self();
        $result
            ->setStatus($status)
            ->setEncodedResponse($encodedResponse)
        ;

        return $result;
    }

    /**
     * @param string|null $encodedResponse
     *
     * @return $this
     */
    public static function createInvalidResponseData(?string $encodedResponse): self
    {
        $result = new self();
        $result
            ->setStatus(PassportCheckResultStatusType::INVALID_RESPONSE_DATA)
            ->setEncodedResponse($encodedResponse)
        ;

        return $result;
    }

    /**
     * @param string|null $inn
     *
     * @return $this
     */
    public function setInn(?string $inn): self
    {
        $this->inn = $inn;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInn(): ?string
    {
        return $this->inn;
    }

    /**
     * @param bool|null $captchaRequired
     *
     * @return $this
     */
    public function setCaptchaRequired(?bool $captchaRequired): self
    {
        $this->captchaRequired = $captchaRequired;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getCaptchaRequired(): ?bool
    {
        return $this->captchaRequired;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param array|null $errors
     *
     * @return $this
     */
    public function setErrors(?array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * @param string|null $encodedResponse
     *
     * @return $this
     */
    public function setEncodedResponse(?string $encodedResponse): self
    {
        $this->encodedResponse = $encodedResponse;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEncodedResponse(): ?string
    {
        return $this->encodedResponse;
    }
}
