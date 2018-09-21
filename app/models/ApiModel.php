<?php

namespace Oishy\Model;

use Oishy\Core\Db;
use Oishy\Driver\Registry;

/**
* Model for API Key Related Operations
*
*/
class ApiModel
{
    protected static $table = 'mi_apis';

    public static function add($key)
    {
        $key = trim($key);

        if (empty($key)) {
            return false;
        }

        $stmt = Db::insert(['api_key'])->into(self::$table)->values([$key]);

        if ($stmt->execute(false)) {
            return true;
        }

        return false;
    }

    public static function delete($id)
    {
        $id = (int)$id;

        if (!$id) {
            return $id;
        }
        $stmt = Db::delete()->from(self::$table)->where('id', '=', $id);
        return $stmt->execute();
    }

    public static function exists($key)
    {
        $key = trim($key);

        if (empty($key)) {
            return false;
        }
        $sql = Db::select(['id'])->from(self::$table)->where('api_key', '=', $key);
        $stmt = $sql->execute();

        if ($stmt->rowCount()) {
            return true;
        }

        return false;
    }

    public static function getTotalApiKeyCount()
    {
        return Db::query('SELECT COUNT(id) FROM ' . self::$table)->fetchColumn();
    }

    public static function listApi($start = 0, $limit = 5)
    {
        return Db::select(['id', 'api_key'])->from(self::$table)
        ->limit($start, $limit)
        ->execute()
        ->fetchAll();
    }

    public static function getApiKey()
    {
        if (Registry::get('site.api_key')) {
            return Registry::get('site.api_key');
        }

        $mode = (int)OptionModel::get('api_mode');

        $api = '';

        if ($mode === 1) {
            $api = self::getRandomApi();
        } else {
            $api = self::getHourlyApi();
        }

        if ($api) {
            Registry::set('site.api_key', $api);
        }

        return $api;
    }

    public static function getRandomApi()
    {
        $sql = Db::query('SELECT FLOOR(RAND() * COUNT(*)) AS offset FROM ' . self::$table);
        $offset = $sql->fetch()['offset'];
        $result = Db::query('SELECT api_key FROM ' . self::$table . ' LIMIT ' . $offset . ', 1');
        return $result->fetch()['api_key'];
    }

    public static function getHourlyApi()
    {
        $hour = date('G');
        $raw_apis = self::listApi(0, 24);

        if (empty($raw_apis)) {
            return '';
        }

        $apis = [];
        $i = 1;

        foreach ($raw_apis as $api) {
            $apis[$i] = $api['api_key'];
            $i++;
        }

        $api = '';

        switch ($hour) {
            case '0':
                $api = isset($apis['1']) ? $apis['1'] : '';
                break;

            case '1':
                $api = isset($apis['2']) ? $apis['2'] : '';
                break;

            case '2':
                $api = isset($apis['3']) ? $apis['3'] : '';
                break;

            case '3':
                $api = isset($apis['4']) ? $apis['4'] : '';
                break;

            case '4':
                $api = isset($apis['5']) ? $apis['5'] : '';
                break;

            case '5':
                $api = isset($apis['6']) ? $apis['6'] : '';
                break;

            case '6':
                $api = isset($apis['7']) ? $apis['7'] : '';
                break;

            case '7':
                $api = isset($apis['8']) ? $apis['8'] : '';
                break;

            case '8':
                $api = isset($apis['9']) ? $apis['9'] : '';
                break;

            case '9':
                $api = isset($apis['10']) ? $apis['10'] : '';
                break;

            case '10':
                $api = isset($apis['11']) ? $apis['11'] : '';
                break;

            case '11':
                $api = isset($apis['12']) ? $apis['12'] : '';
                break;

            case '12':
                $api = isset($apis['13']) ? $apis['13'] : '';
                break;

            case '13':
                $api = isset($apis['14']) ? $apis['14'] : '';
                break;

            case '14':
                $api = isset($apis['15']) ? $apis['15'] : '';
                break;

            case '15':
                $api = isset($apis['16']) ? $apis['16'] : '';
                break;

            case '16':
                $api = isset($apis['17']) ? $apis['17'] : '';
                break;

            case '17':
                $api = isset($apis['18']) ? $apis['18'] : '';
                break;

            case '18':
                $api = isset($apis['19']) ? $apis['19'] : '';
                break;

            case '19':
                $api = isset($apis['20']) ? $apis['20'] : '';
                break;

            case '20':
                $api = isset($apis['21']) ? $apis['21'] : '';
                break;

            case '21':
                $api = isset($apis['22']) ? $apis['22'] : '';
                break;

            case '22':
                $api = isset($apis['23']) ? $apis['23'] : '';
                break;
            case '23':
                $api = isset($apis['24']) ? $apis['24'] : '';
                break;
        }

        if (empty($api)) {
            // Fallback
            $api = get_random_value($apis);
        }
        return $api;
    }
}
