<?php

namespace Oishy\Model;

use Oishy\Core\Db;
use Oishy\Driver\Registry;

/**
* Model for Search Tags Related Operations
*
*/
class TagModel
{
    protected static $table = 'mi_queries';

    public static function add($query)
    {
        $query = trim($query);

        if (empty($query)) {
            return false;
        }

        $query = strip_tags($query);

        $sql = 'INSERT IGNORE INTO ' . self::$table . ' (query) VALUES (:query)';
        $stmt = Db::prepare($sql);
        $parameters = [':query' => $query];

        if ($stmt->execute($parameters)) {
            return true;
        }

        return false;
    }


    public static function getTotalTagCount()
    {
        return Db::query('SELECT COUNT(id) FROM ' . self::$table)->fetchColumn();
    }

    public static function listTags($start = 0, $limit = 5)
    {
        return Db::select(['query'])->from(self::$table)
        ->limit($start, $limit)
        ->execute()
        ->fetchAll();
    }
}
