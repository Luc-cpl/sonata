<?php

namespace Tests\Entities;

use Sonata\Entities\Abstracts\AbstractUser;
use Doctrine\ORM\Mapping as Doctrine;
use Orkestra\Entities\Attributes\Faker;
use Sonata\Entities\Partials\HasCreatedAt;
use Sonata\Entities\Partials\HasUpdatedAt;

#[Doctrine\Entity]
#[Doctrine\Table(name: 'users')]
#[Doctrine\Index(columns: ['email'])]
#[Doctrine\HasLifecycleCallbacks]
class DoctrineUser extends AbstractUser
{
	use HasCreatedAt, HasUpdatedAt;
}