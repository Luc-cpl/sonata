<?php

namespace Sonata\Doctrine\Entities\Traits;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use DateTimeInterface;
use DateTime;

/**
 * Trait to add created_at and updated_at fields to an entity.
 * Please, add the `Doctrine\ORM\Mapping\HasLifecycleCallbacks`
 * attribute to the entity class to make this trait work.
 */
trait HasTimeStampsTrait
{
    #[Column(name: 'created_at', type: 'datetime')]
    protected DateTimeInterface $createdAt;

    #[Column(name: 'updated_at', type: 'datetime')]
    protected DateTimeInterface $updatedAt;

    private bool $useCreateHook = true;

    private bool $useUpdateHook = true;

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
        $this->useCreateHook = false;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
        $this->useUpdateHook = false;
    }

    #[PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->setUpdatedAtValue();
        if ($this->useCreateHook) {
            $this->createdAt = new DateTime();
        }
    }

    #[PreUpdate]
    public function setUpdatedAtValue(): void
    {
        if ($this->useUpdateHook) {
            $this->updatedAt = new DateTime();
        }
    }
}
