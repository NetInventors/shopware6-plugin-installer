<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder;

use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent\FlowEventCollection;
use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent\FlowEventCollectionFactory;
use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent\FlowEventInterface;
use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowSequence\FlowSequenceInterface;
use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowSequence\RetainFlowSequenceAwareInterface;
use NetInventors\Shopware6PluginInstaller\UninstallerInterface;
use Shopware\Core\Content\Flow\Aggregate\FlowSequence\FlowSequenceEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class FlowBuilderUninstaller implements UninstallerInterface
{
    private EntityRepository $flowRepository;

    private EntityRepository $flowSequenceRepository;

    private FlowEventCollection $flowEventCollection;

    public function __construct(
        private ContainerInterface $container,
        private string $directory,
    ) {
        /** @var EntityRepository $flowRepository */
        $flowRepository = $this->container->get('flow.repository');

        $this->flowRepository      = $flowRepository;
        $this->flowEventCollection = FlowEventCollectionFactory::create($this->container, $this->directory);

        /** @var EntityRepository $flowSequenceRepository */
        $flowSequenceRepository       = $this->container->get('flow_sequence.repository');
        $this->flowSequenceRepository = $flowSequenceRepository;
    }

    #[\Override]
    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $context     = $uninstallContext->getContext();
        $data        = [];
        $actionNames = [];
        $eventNames  = [];

        /** @var FlowEventInterface $flowEvent */
        foreach ($this->flowEventCollection->getElements() as $flowEvent) {
            $flowEventName = $flowEvent::getEventName();
            $sequences     = $flowEvent->getSequences();

            if ([] === $sequences) {
                continue;
            }

            if (!$this->hasARetainedFlowSequence($sequences)) {
                $eventNames[$flowEventName] = $flowEventName;

                continue;
            }

            foreach ($sequences as $sequence) {
                if (
                    !$sequence instanceof RetainFlowSequenceAwareInterface
                    || !$sequence->shouldRemoveOnUninstall()
                ) {
                    continue;
                }

                $actionName               = $flowEvent::getActionName();
                $actionNames[$actionName] = $actionName;
            }
        }

        if ([] !== $actionNames) {
            $flowIds = $this->removeSequencesAndGetFlowIdsForRemoval($actionNames, $context);

            foreach ($flowIds as $flowId) {
                $data[] = [ 'id' => $flowId ];
            }
        }

        if ([] !== $eventNames) {
            $criteria = new Criteria();

            $criteria->addFilter(new EqualsAnyFilter('eventName', $eventNames));

            $flowIds = $this->flowRepository->searchIds($criteria, $context)->getIds();

            foreach ($flowIds as $flowId) {
                $data[] = [ 'id' => $flowId ];
            }
        }

        if ([] === $data) {
            return;
        }

        $this->flowRepository->delete($data, $context);
    }

    #[\Override]
    public function deactivate(DeactivateContext $deactivateContext): void
    {
    }

    /**
     * @param list<FlowSequenceInterface> $sequences
     */
    private function hasARetainedFlowSequence(array $sequences): bool
    {
        foreach ($sequences as $sequence) {
            if ($sequence instanceof RetainFlowSequenceAwareInterface) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<string> $actionNames
     *
     * @return list<string>|list<array<string, string>>
     */
    private function removeSequencesAndGetFlowIdsForRemoval(array $actionNames, Context $context): array
    {
        $sequenceCriteria = new Criteria();

        $sequenceCriteria->addFilter(new EqualsAnyFilter('actionName', $actionNames));

        $sequenceResult = $this->flowSequenceRepository->search($sequenceCriteria, $context);

        if ($sequenceResult->getTotal() <= 0) {
            return [];
        }

        $data    = [];
        $flowIds = [];

        /** @var FlowSequenceEntity $flowSequence */
        foreach ($sequenceResult->getEntities() as $flowSequence) {
            $flowId           = $flowSequence->getFlowId();
            $data[]           = [ 'id' => $flowSequence->getId() ];
            $flowIds[$flowId] = $flowId;
        }

        $this->flowSequenceRepository->delete($data, $context);

        $flowCriteria = new Criteria();

        $flowCriteria->addFilter(new EqualsFilter('sequences.id', null));
        $flowCriteria->addFilter(new EqualsAnyFilter('id', \array_values($flowIds)));

        return $this->flowRepository->searchIds($flowCriteria, $context)->getIds();
    }
}
