<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\ImportExportProfile;

use Shopware\Core\Framework\Struct\Collection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @extends Collection<ImportExportProfileInterface>
 */
final class ImportExportProfileCollection extends Collection
{
    /**
     * @param list<class-string<ImportExportProfileInterface>> $installableProfiles
     */
    public function __construct(ContainerInterface $container, array $installableProfiles = [])
    {
        foreach ($installableProfiles as $className) {
            $profile = new $className($container);

            $this->set($profile::getTechnicalName(), $profile);
        }
    }

    /**
     * @return list<string>
     */
    public function getTechnicalNames(): array
    {
        /** @psalm-suppress DeprecatedProperty */
        return \array_map(
            static fn (ImportExportProfileInterface $profile): string => $profile::getTechnicalName(),
            \array_values($this->elements),
        );
    }

    /**
     * @return list<string>
     */
    public function getLabels(): array
    {
        /** @psalm-suppress DeprecatedProperty */
        return \array_map(
            static fn (ImportExportProfileInterface $profile): string => $profile::getLabel(),
            \array_values($this->elements),
        );
    }
}
