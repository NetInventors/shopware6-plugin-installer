<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField\FieldSet;

use NetInventors\Shopware6PluginInstaller\IdFieldTrait;

trait CustomFieldSetTrait
{
    use IdFieldTrait;

    /** @var list<string>|null */
    protected array|null $relatedEntitiesOverride = null;

    /**
     * @param list<string> $entities
     */
    public function setRelatedEntities(array $entities): void
    {
        $this->relatedEntitiesOverride = $entities;
    }

    /**
     * @return list<string>
     */
    public function getRelatedEntities(): array
    {
        return $this->relatedEntitiesOverride ?? $this->getDefaultRelatedEntities();
    }

    /**
     * @return list<string>
     */
    abstract protected function getDefaultRelatedEntities(): array;
}
