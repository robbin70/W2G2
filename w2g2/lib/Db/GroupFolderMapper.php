<?php

namespace OCA\w2g2\Db;

use OCP\IDbConnection;
use OCP\AppFramework\Db\Mapper;

class GroupFolderMapper extends Mapper
{
    public function __construct(IDbConnection $db)
    {
        parent::__construct($db, 'locks_w2g2', '\OCA\w2g2\Db\Lock');
    }

    public function get()
    {
        $groupFolderName = "__groupfolders";

        $query = "SELECT * FROM *PREFIX*" . "filecache" . " WHERE name = ? AND path = ?";

        $results = $this->db->executeQuery($query, [$groupFolderName, $groupFolderName])
            ->fetchAll();

        if (count($results) > 0) {
            return $results[0]['fileid'];
        }

        return null;
    }

    public function getMountPoints($folderId)
    {
        if ( ! is_numeric($folderId)) {
            return [];
        }

        $query = "SELECT mount_point FROM *PREFIX*" . 'group_folders' . " WHERE folder_id=?";

        return $this->db->executeQuery($query, [$folderId])
            ->fetchAll();
    }
}
