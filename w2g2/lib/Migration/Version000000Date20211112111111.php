<?php

declare(strict_types=1);

namespace OCA\w2g2\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version000000Date20211112111111 extends SimpleMigrationStep {

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     */
    public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {}

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     * @return null|ISchemaWrapper
     */
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('locks_w2g2')) {
            $table = $schema->createTable('locks_w2g2');

            $table->addColumn('file_id', 'integer', [
                'autoincrement' => false,
                'notnull' => true,
                'unsigned' => true,
                'length' => 8,
            ]);
            $table->addColumn('locked_by', 'string', [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('created', 'datetime', [
                'notnull' => false,
            ]);

            $table->setPrimaryKey(['file_id']);
        }

        return $schema;
    }

    /**
     * @param IOutput $output
     * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
     * @param array $options
     */
    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {}
}
