<?php

declare(strict_types=1);

namespace MauticPlugin\EcommerceConnectorBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Mautic\IntegrationsBundle\Migration\AbstractMigration;

class Version_1_1_0 extends AbstractMigration
{
    private const TABLE = 'ecommerce_orders';

    private const DATE_ADDED_INDEX = 'ecommerce_date_added';

    protected function isApplicable(Schema $schema): bool
    {
        try {
            $table = $schema->getTable($this->concatPrefix(self::TABLE));
        } catch (SchemaException) {
            return false;
        }

        return $this->needsDecimalColumn($table) || !$table->hasIndex(self::DATE_ADDED_INDEX);
    }

    protected function up(): void
    {
        $tableName = $this->concatPrefix(self::TABLE);
        $table     = $this->entityManager->getConnection()->createSchemaManager()->introspectSchema()->getTable($tableName);

        if ($this->needsDecimalColumn($table)) {
            $this->addSql("ALTER TABLE `{$tableName}` MODIFY `order_total` DECIMAL(19,4) NOT NULL");
        }

        if (!$table->hasIndex(self::DATE_ADDED_INDEX)) {
            $this->addSql('CREATE INDEX `'.self::DATE_ADDED_INDEX."` ON `{$tableName}` (`date_added`)");
        }
    }

    private function needsDecimalColumn(Table $table): bool
    {
        if (!$table->hasColumn('order_total')) {
            return false;
        }

        return 'decimal' !== $table->getColumn('order_total')->getType()->getName();
    }
}
