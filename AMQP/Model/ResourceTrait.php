<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait ResourceTrait
 */
trait ResourceTrait
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @suppress PhanReadOnlyProtectedProperty
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
