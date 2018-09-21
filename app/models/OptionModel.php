<?php

namespace Oishy\Model;

use Oishy\Core\Config;
use Oishy\Core\Db;
use Oishy\Driver\Registry;

/**
* Model for Site Options
*
*/
class OptionModel
{
    protected static $autoloaded = false;
    protected static $table = 'mi_options';

    public static function get($name, $default = false)
    {
        self::autoloadOptions();

        // See if we can load from the registry
        $registry_value = Registry::get("site.option.{$name}", null);
        if ($registry_value) {
            return $registry_value;
        }

        $invalid_options = Registry::get('site.invalid_options', []);

        if (in_array($name, $invalid_options)) {
            return $default;
        }

        $sql = Db::select(['value'])->from(self::$table)->where('name', '=', $name);
        $stmt = $sql->execute();

        if (!$stmt->rowCount()) {
            // We will mark this invalid option for current session
            $invalid_options[] = $name;
            Registry::set('site.invalid_options', $invalid_options);
            return $default;
        }
        $option = $stmt->fetch();
        return $option['value'];
    }

    public static function update($name, $value)
    {
        $name = trim($name);

        if (empty($name)) {
            return false;
        }
        $token = get_secure_token(10);
        $exists = self::get($name, $token);

        if ($exists === $token) {
            return self::add($name, $value);
        }

        $sql = Db::update(['value' => $value])
               ->table(self::$table)->where('name', '=', $name);
        $affected_rows = $sql->execute();

        if (!$affected_rows) {
            return false;
        }

        // Instant update on the registry as well
        Registry::set("site.option.{$name}", $value);
        return true;
    }

    public static function add($name, $value)
    {
        $name = trim($name);

        if (empty($name)) {
            return false;
        }

        $token = get_secure_token(10);

        $exists = self::get($name, $token);

        if ($exists !== $token) {
            return self::update($name, $value);
        }

        $stmt = Db::insert(['name', 'value'])
                       ->into(self::$table)
                       ->values([$name, $value]);

        if (!$stmt->execute(false)) {
            return false;
        }

        return true;
    }

    public static function autoLoadOptions()
    {
        if (static::$autoloaded) {
            return false;
        }
        $sql = Db::select(['name', 'value'])->from(self::$table);
        $stmt = $sql->execute();
        $loaded_options = 0;
        foreach ($stmt->fetchAll() as $option) {
            $name = $option['name'];
            $key = "site.option.{$option['name']}";
            Registry::set($key, $option['value']);
            $loaded_options++;
        }

        self::$autoloaded = true;

        return $loaded_options;
    }

    public static function getThemeList()
    {
        // Skip searching the directory for multiple calls
        if (Registry::get('site.theme_list', false)) {
            return Registry::get('site.theme_list', []);
        }
        $themes = [];
        $query = @glob(Config::get('template.directory') . '/*', GLOB_ONLYDIR);
        if (!is_array($themes) || !empty($themes)) {
            return $themes;
        }
        foreach ($query as $theme) {
            $themes[] = basename($theme);
        }
        Registry::set('site.theme_list', $themes);
        return $themes;
    }
}
