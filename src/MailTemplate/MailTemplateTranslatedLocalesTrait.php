<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

trait MailTemplateTranslatedLocalesTrait
{
    protected MailTemplateTranslatedLocalesCollection|null $translatedLocales = null;

    public function setTranslatedLocales(MailTemplateTranslatedLocalesCollection $collection): void
    {
        $this->translatedLocales = $collection;
    }

    public function getTranslatedLocales(): MailTemplateTranslatedLocalesCollection|null
    {
        return $this->translatedLocales;
    }
}
