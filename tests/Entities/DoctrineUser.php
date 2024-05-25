<?php

namespace Tests\Entities;

use Sonata\Doctrine\Entities\Abstracts\AbstractUser;
use Sonata\Doctrine\Entities\Partials\HasCreatedAt;
use Sonata\Doctrine\Entities\Partials\HasUpdatedAt;
use Doctrine\ORM\Mapping as Doctrine;

#[Doctrine\Entity]
#[Doctrine\Table(name: 'users')]
#[Doctrine\Index(columns: ['email'])]
#[Doctrine\HasLifecycleCallbacks]
class DoctrineUser extends AbstractUser
{
	use HasCreatedAt, HasUpdatedAt;
}