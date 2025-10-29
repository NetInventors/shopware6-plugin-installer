<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField\FieldSet;

use NetInventors\Shopware6PluginInstaller\CustomField\Field\CustomFieldInterface;

interface CustomFieldSetInterface
{
    public static function getName(): string;

    /** @return array{de-DE:string, en-GB:string} */
    public function getLabels(): array;

    public function isTranslated(): bool;

    /** @return list<string> */
    public function getRelatedEntities(): array;

    /**
     * @param list<string> $entities
     */
    public function setRelatedEntities(array $entities): void;

    /** @return list<CustomFieldInterface> */
    public function getFields(): array;

    public function isActive(): bool;

    public function isGlobal(): bool;

    public function getId(): string|null;

    public function setId(string $id): void;
}
