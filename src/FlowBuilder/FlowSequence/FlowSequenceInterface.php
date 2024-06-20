<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowSequence;

use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent\FlowEventInterface;
use Shopware\Core\Content\MailTemplate\MailTemplateEntity;
use Shopware\Core\Framework\Context;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface FlowSequenceInterface
{
    public function __construct(ContainerInterface $container, FlowEventInterface $flowEvent);

    public function build(Context $context): array;

    public function getConfig(MailTemplateEntity $mailTemplateEntity, Context $context): array;
}
