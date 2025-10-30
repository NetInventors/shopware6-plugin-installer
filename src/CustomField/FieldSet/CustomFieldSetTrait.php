<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField\FieldSet;

trait CustomFieldSetTrait
{
    protected string|null $id = null;

    #[\Override]
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    #[\Override]
    public function getId(): string|null
    {
        return $this->id;
    }

    /** @var list<string>|null */
    protected array|null $relatedEntitiesOverride = null;

    /**
     * @param list<string> $entities
     */
    #[\Override]
    public function setRelatedEntities(array $entities): void
    {
        $this->relatedEntitiesOverride = $entities;
    }

    /**
     * @return list<string>
     */
    #[\Override]
    public function getRelatedEntities(): array
    {
        return $this->relatedEntitiesOverride ?? $this->getDefaultRelatedEntities();
    }

    /**
     * @return list<string>
     */
    abstract protected function getDefaultRelatedEntities(): array;
}
