<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate\Language;

abstract class AbstractLanguageTemplate implements ArrayableInterface
{
    public function __construct(
        public readonly string $languageId,
        public readonly string|null $subject,
        public readonly string $contentPlain,
        public readonly string $contentHtml,
        public readonly string $senderName,
        public readonly string|null $description,
    ) {
    }

    /**
     * @return array<string, mixed|null>
     */
    public function toArray(): array
    {
        return [
            'languageId'   => $this->languageId,
            'subject'      => $this->subject,
            'contentPlain' => $this->contentPlain,
            'contentHtml'  => $this->contentHtml,
            'senderName'   => $this->senderName,
            'description'  => $this->description,
        ];
    }
}
