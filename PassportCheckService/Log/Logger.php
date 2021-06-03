<?php

namespace App\PassportCheckService\Log;

use Psr\Log\LoggerInterface;

/**
 * Class Logger.
 */
class Logger
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Logger constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param string $message
     */
    public function info(string $message)
    {
        $this->logger->info($message);
    }

    /**
     * @param string $message
     */
    public function error(string $message)
    {
        $this->logger->error($message);
    }
}
