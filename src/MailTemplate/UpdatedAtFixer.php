<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\MailTemplate;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Shopware\Core\Framework\Uuid\Uuid;

class UpdatedAtFixer
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * @throws Exception
     */
    public function fix(EntityDefinition $entityDefinition, EntityWrittenContainerEvent $event): void
    {
        $table   = $entityDefinition->getEntityName();
        $records = $event->getPrimaryKeys($table);

        /** @var string|array<string, string> $record */
        foreach ($records as $record) {
            if (!\is_array($record)) {
                $record = [ 'id' => $record ];
            }

            $primaryKey = [];

            foreach ($record as $column => $value) {
                $field = $entityDefinition->getField($column);

                if (!$field instanceof StorageAware) {
                    continue;
                }

                $storageName              = $field->getStorageName();
                $primaryKey[$storageName] = Uuid::fromHexToBytes($value);
            }

            $this->connection->update($table, [ 'updated_at' => null ], $primaryKey);
        }
    }
}
