<?php

namespace App\PassportCheckService\Model;

/**
 * Class CurlResponse.
 */
class CurlResponse
{
    /**
     * @var int
     */
    private $httpCode;

    /**
     * @var array
     */
    private $data;

    /**
     * CurlResponse constructor.
     *
     * @param int   $httpCode
     * @param array $data
     */
    public function __construct(int $httpCode, array $data)
    {
        $this->httpCode = $httpCode;
        $this->data     = $data;
    }

    /**
     * @param int $httpCode
     *
     * @return $this
     */
    public function setHttpCode(int $httpCode): self
    {
        $this->httpCode = $httpCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
