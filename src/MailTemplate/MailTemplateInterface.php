<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

interface MailTemplateInterface
{
    /**
     * @param list<string> $locales
     */
    public function __construct(string $directory, array $locales);

    public function getName(): string;

    public function getTechnicalName(): string;

    /**
     * @return list<string>
     */
    public function getLocales(): array;

    public function supportsLocale(string $locale): bool;

    public function getResourceLocation(): string;

    /**
     * @return array<string, MailTemplateResources>
     */
    public function getResourceFiles(): array;

    public function getResourceFilesForLocale(string $locale): MailTemplateResources|null;

    public function getDescriptions(): array;

    public function getDescriptionForLocale(string $locale): string|null;

    public function getSubjects(): array;

    public function getSubjectForLocale(string $locale): string|null;

    /**
     * @return array<string, string>
     */
    public function getAvailableEntities(): array;

    public function getSenderName(): string;

    public function getId(): string|null;

    public function setId(string $id): void;

    public function getTypeId(): string|null;

    public function setTypeId(string $typeId): void;

    public function getUpdatedAt(): \DateTimeInterface|null;

    public function setUpdatedAt(\DateTimeInterface|null $updatedAt): void;

    public function wasUpdated(): bool;

    public function getTranslatedLocales(): MailTemplateTranslatedLocalesCollection|null;

    public function setTranslatedLocales(MailTemplateTranslatedLocalesCollection $collection): void;
}
