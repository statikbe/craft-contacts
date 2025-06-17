<?php

namespace statikbe\contacts\migrations;

use craft\db\Migration;
use statikbe\contacts\records\FilterRecord;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if (!$this->db->tableExists(FilterRecord::tableName())) {
            $this->createTable(FilterRecord::tableName(), [
                'id' => $this->primaryKey(),
                'label' => $this->string()->notNull(),
                'conditionConfig' => $this->mediumText(),
                'ownerId' => $this->integer()->null(),
                'shared' => $this->boolean()->defaultValue(false),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createIndex(null, FilterRecord::tableName(), ['ownerId'], false);
            $this->createIndex(null, FilterRecord::tableName(), ['shared'], false);
        }

        $this->addForeignKeys();

        return true;
    }

    private function addForeignKeys(): void
    {

        // $name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
        $this->addForeignKey(
            $this->db->getForeignKeyName(),
            FilterRecord::tableName(),
            'ownerId',
            '{{%users}}',
            'id',
            'SET NULL'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        // Place uninstallation code here...

        return true;
    }
}
