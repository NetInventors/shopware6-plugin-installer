<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder;

use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent\FlowEventCollection;
use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent\FlowEventCollectionFactory;
use NetInventors\Shopware6PluginInstaller\InstallerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\DependencyInjection\ContainerInterface;

final readonly class FlowBuilderInstaller implements InstallerInterface
{
    private EntityRepository $flowRepository;

    private FlowEventExistsStateInjector $flowEventExistsStateInjector;

    private FlowEventCollection $flowEventCollection;

    public function __construct(
        private ContainerInterface $container,
        private string $directory,
    ) {
        /** @var EntityRepository $flowRepository */
        $flowRepository = $this->container->get('flow.repository');

        $this->flowRepository               = $flowRepository;
        $this->flowEventExistsStateInjector = new FlowEventExistsStateInjector($this->flowRepository);
        $this->flowEventCollection          = FlowEventCollectionFactory::create($this->container, $this->directory);
    }

    #[\Override]
    public function install(InstallContext $installContext): void
    {
        $this->insertNonExistentFlows($installContext->getContext());
    }

    #[\Override]
    public function postInstall(InstallContext $installContext): void
    {
    }

    #[\Override]
    public function activate(ActivateContext $activateContext): void
    {
    }

    private function insertNonExistentFlows(Context $context): void
    {
        $flows = [];

        $installableFlows = $this->flowEventExistsStateInjector->injectFlowEventExistsState(
            $this->flowEventCollection,
            $context,
        );

        foreach ($installableFlows as $flow) {
            if (null !== $flow->getId()) {
                continue;
            }

            $sequences = [];

            foreach ($flow->getSequences() as $sequence) {
                $sequences[] = $sequence->build($context);
            }

            $flows[] = [
                'id'        => Uuid::randomHex(),
                'name'      => $flow->getName(),
                'eventName' => $flow::getEventName(),
                'active'    => $flow->isActive(),
                'sequences' => $sequences,
            ];
        }

        if ([] !== $flows) {
            $this->flowRepository->create($flows, $context);
        }
    }
}
