<?php

namespace Tests\Entities;

use Orkestra\Entities\AbstractEntity;
use Doctrine\ORM\Mapping as Doctrine;
use Orkestra\Entities\Attributes\Faker;
use Sonata\Interfaces\Entity\IdentifiableInterface;

/**
 * @property-read ?int $id
 * @property-read string $value
 */
#[Doctrine\Entity]
#[Doctrine\Table(name: 'users')]
class DoctrineUser extends AbstractEntity implements IdentifiableInterface
{
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'AUTO')]
    #[Doctrine\Column(type: 'integer')]
    protected ?int $id = null;

    public function __construct(
        #[Doctrine\Column(type: 'string')]
        #[Faker(method: 'word')]
        protected string $value
    ) {
        //
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
