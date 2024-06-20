<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

trait MailTemplateUpdatedAtTrait
{
    protected \DateTimeInterface|null $updatedAt = null;

    public function setUpdatedAt(\DateTimeInterface|null $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): \DateTimeInterface|null
    {
        return $this->updatedAt;
    }

    public function wasUpdated(): bool
    {
        return $this->updatedAt instanceof \DateTimeInterface;
    }
}
