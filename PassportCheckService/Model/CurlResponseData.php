<?php

namespace App\PassportCheckService\Model;

/**
 * Class CurlResponseData.
 */
class CurlResponseData
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var string|null
     */
    private $inn;

    /**
     * @var bool
     */
    private $captchaRequired;

    /**
     * @param string $code
     *
     * @return $this
     */
    public function setCode($code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
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
     * @param bool $captchaRequired
     *
     * @return $this
     */
    public function setCaptchaRequired($captchaRequired): self
    {
        $this->captchaRequired = $captchaRequired;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCaptchaRequired()
    {
        return $this->captchaRequired;
    }

    /**
     * @return bool
     */
    public function isPassportValid(): bool
    {
        return $this->getInn() !== null;
    }
}
