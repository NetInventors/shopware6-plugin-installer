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

class FlowBuilderInstaller implements InstallerInterface
{
    private readonly EntityRepository $flowRepository;

    private readonly FlowEventExistsStateInjector $flowEventExistsStateInjector;

    private readonly FlowEventCollection $flowEventCollection;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly string $directory,
    ) {
        /** @var EntityRepository $flowRepository */
        $flowRepository = $this->container->get('flow.repository');

        $this->flowRepository               = $flowRepository;
        $this->flowEventExistsStateInjector = new FlowEventExistsStateInjector($this->flowRepository);
        $this->flowEventCollection          = FlowEventCollectionFactory::create($this->container, $this->directory);
    }

    public function install(InstallContext $installContext): void
    {
        $this->insertNonExistentFlows($installContext->getContext());
    }

    public function postInstall(InstallContext $installContext): void
    {
    }

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
