<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\Database;

use Doctrine\DBAL\Connection;

abstract class AbstractEntityDefinitionDestructor implements EntityDefinitionDestructorInterface
{
    public function destruct(Connection $connection): void
    {
        $table = $this->getEntityDefinition()->getEntityName();

        $connection->executeStatement("DROP TABLE IF EXISTS $table");
    }

    public function getDependencies(): array
    {
        return [];
    }
}
