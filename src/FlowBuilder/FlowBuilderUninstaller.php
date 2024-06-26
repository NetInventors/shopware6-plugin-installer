<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\FlowBuilder;

use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent\FlowEventCollection;
use NetInventors\Shopware6PluginInstaller\FlowBuilder\FlowEvent\FlowEventCollectionFactory;
use NetInventors\Shopware6PluginInstaller\UninstallerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FlowBuilderUninstaller implements UninstallerInterface
{
    private readonly EntityRepository $flowRepository;

    private readonly FlowEventCollection $flowEventCollection;

    public function __construct(
        private readonly ContainerInterface $container,
        private readonly string $directory,
    ) {
        /** @var EntityRepository $flowRepository */
        $flowRepository = $this->container->get('flow.repository');

        $this->flowRepository      = $flowRepository;
        $this->flowEventCollection = FlowEventCollectionFactory::create($this->container, $this->directory);
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('eventName', $this->flowEventCollection->getEventNames()));

        $flowIds = $this->flowRepository->searchIds($criteria, $uninstallContext->getContext())->getIds();

        $data = \array_map(
            static fn ($id) => [ 'id' => $id ],
            $flowIds,
        );

        if ([] === $data) {
            return;
        }

        $this->flowRepository->delete($data, $uninstallContext->getContext());
    }

    public function deactivate(DeactivateContext $deactivateContext): void
    {
    }
}
