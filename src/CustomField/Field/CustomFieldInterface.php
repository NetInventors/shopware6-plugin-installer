<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField\Field;

/**
 * @psalm-type FieldConfig = array<string, mixed>
 */
interface CustomFieldInterface
{
    public function getName(): string;

    public function getType(): string;

    /** @return FieldConfig */
    public function getConfig(): array;

    public function isActive(): bool;

    public function isAllowCartExpose(): bool;

    public function isAllowCustomerRead(): bool;

    public function isAllowCustomerWrite(): bool;

    public function getId(): string|null;

    public function setId(string $id): void;
}
