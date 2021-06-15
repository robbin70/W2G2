<?php

namespace OCA\w2g2\Db;

class FavoriteMapper
{
    protected $categoryMapper;

    public function __construct(CategoryMapper $categoryMapper)
    {
        $this->categoryMapper = $categoryMapper;
    }

    public function getUsersForFile($fileId)
    {
        $usersIds = [];
        $categoriesResult = $this->categoryMapper->getCategoriesForFile($fileId);

        foreach ($categoriesResult as $category) {
            $result = $this->categoryMapper->getFavoriteByCategoryId($category["categoryid"]);

            if (count($result) > 0) {
                $usersIds[] = $result[0]["uid"];
            }
        }

        return $usersIds;
    }
}
