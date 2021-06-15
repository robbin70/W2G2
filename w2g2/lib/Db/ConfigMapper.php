<?php

namespace OCA\w2g2\Db;

use OCP\IDbConnection;
use OCP\AppFramework\Db\Mapper;

class ConfigMapper extends Mapper
{
    public function __construct(IDbConnection $db)
    {
        parent::__construct($db, 'locks_w2g2', '\OCA\w2g2\Db\Lock');
    }

    public function getColor()
    {
        $defaultColor = "008887";
        $color = $this->get('color');

        return $color ?: $defaultColor;
    }

    public function getFontColor()
    {
        $defaultFontColor = "FFFFFF";
        $fontColor = $this->get('fontcolor');

        return $fontColor ?: $defaultFontColor;
    }

    public function getDirectoryLock()
    {
        $default = "directory_locking_all";
        $value = $this->get('directory_locking');

        return $value ?: $default;
    }

    public function getLockingByNameRule()
    {
        $default = "rule_username";
        $value = $this->get('suffix');

        return $value ?: $default;
    }

    protected function get($configKey)
    {
        $appName = 'w2g2';

        $query = "SELECT * FROM *PREFIX*appconfig where configkey=? and appid=? LIMIT 1";

        $result = $this->db->executeQuery($query, [$configKey, $appName]);
        $row = $result->fetch();

        return $row ? $row['configvalue'] : '';
    }

    public function storeConfig($type, $value)
    {
        $query = "INSERT INTO *PREFIX*appconfig(appid,configkey,configvalue) VALUES('w2g2',?,?)";

        $this->db->executeQuery($query, [$type, $value]);
    }

    public function updateConfig($type, $value)
    {
        $query = "UPDATE *PREFIX*appconfig set configvalue=? WHERE appid='w2g2' and configkey=?";

        $this->db->executeQuery($query, [$value, $type]);
    }
}
