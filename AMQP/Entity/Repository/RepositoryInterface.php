<?php

namespace App\Entity\Repository;

use Doctrine\Persistence\ObjectRepository;
use App\Model\ResourceInterface;

/**
 * Interface RepositoryInterface.
 */
interface RepositoryInterface extends ObjectRepository
{
    public const ORDER_ASCENDING  = 'ASC';
    public const ORDER_DESCENDING = 'DESC';

    /**
     * @param ResourceInterface $resource
     *
     * @psalm-suppress MissingReturnType
     */
    public function add(ResourceInterface $resource);

    /**
     * @param ResourceInterface $resource
     *
     * @psalm-suppress MissingReturnType
     */
    public function remove(ResourceInterface $resource);
}
