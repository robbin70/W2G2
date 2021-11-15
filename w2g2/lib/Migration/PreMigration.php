<?php

namespace OCA\w2g2\Migration;

use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use OCP\ILogger;

class PreMigration implements IRepairStep {
    /** @var ILogger */
    protected $logger;

    protected $tableName;
    protected $tempTableName;
    protected $db;

    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;

        $this->tableName = "oc_locks_w2g2";
        $this->tempTableName = "oc_locks_w2g2_temp";

        $this->db = \OC::$server->getDatabaseConnection();
    }

    /**
     * Returns the step's name
     */
    public function getName()
    {
        return 'Database migration!';
    }

    /**
     * @param IOutput $output
     * @return void
     */
    public function run(IOutput $output)
    {
        if ( ! $this->isOldVersion()) {
            return;
        }

        // Old database version. Migrate.
        $this->createTempTable();
        $this->insertDataInTempTable();
        $this->dropOldTable();
    }

    /**
     * Check if the app is still using the old database version.
     *
     * @return bool
     */
    protected function isOldVersion()
    {
        $appVersion = \OCP\App::getAppVersion('w2g2');

        return version_compare($appVersion, '1.0.0') < 1;
    }

    /**
     * Create a temporary table to store the old table data until the migration to the new format is done.
     *
     */
    protected function createTempTable()
    {
        /**
         * cleanup temp table (issue #68)
         */
        $cleanupStatement = "DROP TABLE IF EXISTS " . $this->tempTableName;
        $this->db->executeQuery($cleanupStatement);

        /**
        * create temp table
        */

        $createStatement = "CREATE TABLE " .
            $this->tempTableName .
            " (name varchar(255) PRIMARY KEY, locked_by varchar(255), created TIMESTAMP null DEFAULT null)";

        $this->db->executeQuery($createStatement);
    }

    /**
     * Insert the data from the old table into the temporary one until the migration to the new format is done.
     *
     */
    protected function insertDataInTempTable()
    {
        $insertStatement = "INSERT INTO " . $this->tempTableName . " (name, locked_by, created)" .
            " SELECT file_id, locked_by, created FROM " . $this->tableName;

        $this->db->executeQuery($insertStatement);
    }

    /**
     * Drop the old table and the new one will be created by the Nextcloud migration.
     *
     */
    protected function dropOldTable()
    {
        $dropStatement = "DROP TABLE " . $this->tableName;

        $this->db->executeQuery($dropStatement);
    }
}
