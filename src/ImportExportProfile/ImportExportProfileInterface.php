<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\ImportExportProfile;

/** @psalm-type MappingEntry = array{key: string, mappedKey: string} */
interface ImportExportProfileInterface
{
    public static function getTechnicalName(): string;

    public static function getLabel(): string;

    /** @return list<MappingEntry> */
    public function getMapping(): array;

    public function getSourceEntity(): string;

    public function getFileMimeType(): string;

    public function getDelimiter(): string;

    public function getEnclosure(): string;

    public function isSystemDefault(): bool;

    public function getConfig(): array;

    public function getId(): string|null;

    public function setId(string $id): void;
}
