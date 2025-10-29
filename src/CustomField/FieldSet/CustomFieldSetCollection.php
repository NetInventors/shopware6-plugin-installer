<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\CustomField\FieldSet;

use Shopware\Core\Framework\Struct\Collection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @extends Collection<CustomFieldSetInterface>
 */
final class CustomFieldSetCollection extends Collection
{
    /**
     * @param list<class-string<CustomFieldSetInterface>> $installableFieldSets
     */
    public function __construct(ContainerInterface $container, array $installableFieldSets = [])
    {
        foreach ($installableFieldSets as $className) {
            $fieldSet = new $className($container);

            $this->set($fieldSet::getName(), $fieldSet);
        }
    }

    /**
     * @return list<string>
     */
    public function getSetNames(): array
    {
        /** @psalm-suppress DeprecatedProperty */
        return \array_map(
            static fn (CustomFieldSetInterface $fieldSet): string => $fieldSet::getName(),
            \array_values($this->elements),
        );
    }
}
