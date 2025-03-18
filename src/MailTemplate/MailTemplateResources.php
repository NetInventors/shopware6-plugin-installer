<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use Symfony\Component\Finder\SplFileInfo;

final readonly class MailTemplateResources
{
    public function __construct(
        public SplFileInfo|null $html,
        public SplFileInfo|null $plain,
    ) {
    }

    public function hasHtml(): bool
    {
        return null !== $this->html;
    }

    public function hasPlain(): bool
    {
        return null !== $this->plain;
    }
}
