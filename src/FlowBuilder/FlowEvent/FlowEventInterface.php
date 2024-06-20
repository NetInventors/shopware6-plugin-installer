<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent;

use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowSequence\FlowSequenceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface FlowEventInterface
{
    public function __construct(ContainerInterface $container);

    public static function getEventName(): string;

    public static function getActionName(): string;

    public function getName(): string;

    public function isActive(): bool;

    /**
     * @return list<FlowSequenceInterface>
     */
    public function getSequences(): array;

    public function getId(): string|null;

    public function setId(string $id): void;
}
