<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder;

use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent\FlowEventCollection;
use Shopware\Core\Content\Flow\FlowEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;

final readonly class FlowEventExistsStateInjector
{
    public function __construct(
        private EntityRepository $flowRepository,
    ) {
    }

    public function injectFlowEventExistsState(
        FlowEventCollection $installableFlows,
        Context $context,
    ): FlowEventCollection {
        if (0 === $installableFlows->count()) {
            return $installableFlows;
        }

        $criteria = new Criteria();

        $criteria
            ->addFilter(new EqualsAnyFilter('eventName', $installableFlows->getEventNames()))
        ;

        $flowEntities = $this->flowRepository->search($criteria, $context);

        /** @var FlowEntity $flowEntity */
        foreach ($flowEntities as $flowEntity) {
            $eventName       = $flowEntity->getEventName();
            $installableFlow = $installableFlows->get($eventName);

            /**
             * $installableFlow cannot be null.
             * We use all the event names to fetch the records from the database,
             * traverse them and read the matching installable flow event.
             *
             * @psalm-suppress PossiblyNullReference
             */
            $installableFlow->setId($flowEntity->getId());
        }

        return $installableFlows;
    }
}
