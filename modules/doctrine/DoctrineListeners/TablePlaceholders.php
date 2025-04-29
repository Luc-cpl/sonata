<?php

namespace Sonata\Doctrine\DoctrineListeners;

use Orkestra\App;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;

class TablePlaceholders
{
    /**
     * @param array<string,string|callable> $placeholders The list of placeholders to be used in table names.
     */
    public function __construct(
        private App $app,
        private array $placeholders = [],
    ) {
        //
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        /** @var ClassMetadata<object> */
        $classMetadata = $eventArgs->getClassMetadata();

        if (!$classMetadata instanceof ClassMetadata) {
            return;
        }

        if (!$classMetadata->isInheritanceTypeSingleTable() || $classMetadata->getName() === $classMetadata->rootEntityName) {
            $name = $classMetadata->getTableName();
            $classMetadata->setPrimaryTable([
                'name' => $this->applyPlaceholders($name),
            ]);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if (isset($mapping['joinTable']) && is_array($mapping['joinTable']) && isset($mapping['joinTable']['name'])) {
                $name = $mapping['joinTable']['name'];
                $name = $this->applyPlaceholders($name);
                /** @var array<string,array{joinTable?:array{name?:string}}> $associationMappings */
                $associationMappings = &$classMetadata->associationMappings;
                $associationMappings[$fieldName]['joinTable']['name'] = $name;
            }
        }
    }

    private function applyPlaceholders(string $name): string
    {
        foreach ($this->placeholders as $placeholder => $replacement) {
            if (is_callable($replacement)) {
                $replacement = $this->app->call($replacement);
            }
            /** @var string $replacement */
            $name = str_replace('{' . $placeholder . '}', $replacement, $name);
        }
        return $name;
    }
}
