<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent;

use Shopware\Core\Framework\Struct\Collection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @extends Collection<FlowEventInterface>
 */
final class FlowEventCollection extends Collection
{
    /**
     * @param list<class-string<FlowEventInterface>> $installableFlowEvents
     */
    public function __construct(ContainerInterface $container, array $installableFlowEvents = [])
    {
        foreach ($installableFlowEvents as $className) {
            $flowEvent = new $className($container);

            $this->set($flowEvent::getEventName(), $flowEvent);
        }
    }

    /**
     * @return list<string>
     */
    public function getEventNames(): array
    {
        return \array_map(
            static fn (FlowEventInterface $flowEvent): string => $flowEvent::getEventName(),
            \array_values($this->elements),
        );
    }
}
