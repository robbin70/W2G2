<?php

namespace OCA\w2g2\Db;

use OCP\IDbConnection;
use OCP\AppFramework\Db\Mapper;

class FileMapper extends Mapper
{
    public function __construct(IDbConnection $db)
    {
        parent::__construct($db, 'locks_w2g2', '\OCA\w2g2\Db\Lock');
    }

    public function get($fileId)
    {
        if ( ! $fileId) {
            return null;
        }

        $query = "SELECT * FROM *PREFIX*" . "filecache" . " WHERE fileid = ?";

        $file = $this->db->executeQuery($query, [$fileId])
            ->fetch();

        if ($file && count($file) > 0) {
            return $file;
        }

        return null;
    }
}
