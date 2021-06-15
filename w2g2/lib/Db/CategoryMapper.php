<?php

namespace OCA\w2g2\Db;

use OCP\IDbConnection;
use OCP\AppFramework\Db\Mapper;

class CategoryMapper extends Mapper
{
    public function __construct(IDbConnection $db)
    {
        parent::__construct($db, 'locks_w2g2', '\OCA\w2g2\Db\Lock');
    }

    /**
     * Get favorite data for the given category, like the userId
     *
     * @param $categoryId
     * @return mixed
     */
    public function getFavoriteByCategoryId($categoryId)
    {
        $favorite = '_$!<Favorite>!$_' ;

        $query = "SELECT * FROM *PREFIX*" . "vcategory" . " WHERE category = ? AND id = ?";

        return $this->db->executeQuery($query, [$favorite, $categoryId])
            ->fetchAll();
    }

    /**
     * Get all categories for the given file, like favorites.
     *
     * @param $fileId
     * @return mixed
     */
    public function getCategoriesForFile($fileId)
    {
        $query = "SELECT * FROM *PREFIX*" . "vcategory_to_object" . " WHERE objid = ?";

        return $this->db->executeQuery($query, [$fileId])
            ->fetchAll();
    }
}
