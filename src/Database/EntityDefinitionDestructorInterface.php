<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;

interface EntityDefinitionDestructorInterface
{
    public function getEntityDefinition(): EntityDefinition;

    /**
     * @return list<class-string<self>>
     */
    public function getDependencies(): array;

    /**
     * @throws Exception
     */
    public function destruct(Connection $connection): void;
}
