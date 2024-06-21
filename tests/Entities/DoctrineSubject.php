<?php

namespace Tests\Entities;

use Orkestra\Entities\AbstractEntity;
use Doctrine\ORM\Mapping as Doctrine;
use Orkestra\Entities\Attributes\Faker;

/**
 * @property-read ?int $id
 * @property-read string $value
 */
#[Doctrine\Entity]
#[Doctrine\Table(name: 'subjects')]
/**
 * @property-read ?int $id
 * @property-read string $value
 */
class DoctrineSubject extends AbstractEntity
{
    #[Doctrine\Id]
    #[Doctrine\GeneratedValue(strategy: 'AUTO')]
    #[Doctrine\Column(type: 'integer')]
    protected ?int $id;

    public function __construct(
        #[Doctrine\Column(type: 'string')]
        #[Faker(method: 'word')]
        protected string $value
    ) {
        //
    }
}
