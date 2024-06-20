<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate\Language;

class DefaultLanguageTemplate extends AbstractLanguageTemplate
{
    /**
     * @param list<LanguageVariantTemplate> $translations
     */
    public function __construct(
        private readonly string $id,
        private readonly string|null $typeId,
        string $languageId,
        string|null $subject,
        string $contentPlain,
        string $contentHtml,
        string $senderName,
        string|null $description,
        private array $translations = [],
    ) {
        parent::__construct($languageId, $subject, $contentPlain, $contentHtml, $senderName, $description);
    }

    public function addLanguageVariant(LanguageVariantTemplate $languageVariant): void
    {
        $this->translations[] = $languageVariant;
    }

    /**
     * @return array<string, mixed|null>
     */
    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'mailTemplateTypeId' => $this->typeId,
            'translations'       => \array_merge(
                [ parent::toArray() ],
                \array_map(
                    static fn (LanguageVariantTemplate $languageVariant): array => $languageVariant->toArray(),
                    $this->translations,
                ),
            ),
        ];
    }
}
