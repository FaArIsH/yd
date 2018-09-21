<?php

namespace Oishy\Model;

use Oishy\Core\Db;

/**
*
*/
class InstallerModel
{

    public static function isInstalled()
    {
        $state = null;
        if (is_null($state)) {
            @clearstatcache();
            $state = (bool)is_file(apppath('config/db.php'));
        }

        return $state;
    }

    public static function isLocked()
    {
        static $state = null;
        if (is_null($state)) {
            @clearstatcache();
            $state = (bool)is_file(apppath('installer.lock'));
        }
        return $state;
    }

    public static function writeDbFile($dsn, $username, $password)
    {
        $db_file = apppath('config/db.php');
        $demo_file = apppath('config/db-sample.php');
        $data = file_get_contents($demo_file);
        $data = str_replace([
            '%dsn%', '%username%', '%password%'
        ], [
            $dsn, $username, $password
        ], $data);

        return file_put_contents($db_file, $data);
    }

    public static function dumpSql($username, $password)
    {
        $username = trim($username);
        $password = password_hash($password, PASSWORD_DEFAULT);
        $success = true;
        try {
            Db::query("DROP TABLE IF EXISTS `mi_apis`");
            Db::query("
                CREATE TABLE `mi_apis` (
                `id` int(11) NOT NULL,
                `api_key` varchar(255) COLLATE utf8_unicode_ci NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            Db::query("DROP TABLE IF EXISTS `mi_options`");
            Db::query("
                CREATE TABLE `mi_options` (
                `name` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
                `value` text COLLATE utf8_unicode_ci NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

            Db::query("DROP TABLE IF EXISTS `mi_queries`;");
            Db::query("
                CREATE TABLE `mi_queries` (
                `id` int(11) NOT NULL,
                `query` varchar(255) COLLATE utf8_unicode_ci NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
            Db::query("
                REPLACE INTO `mi_options` (`name`, `value`) VALUES
                ('username', '{$username}'),
                ('password', '{$password}'),
                ('site_name', 'MiTube'),
                ('site_tagline', 'The YouTube API Engine You Deserve!'),
                ('site_desc', 'The YouTube API Engine you deserve and also the one you need right now.'),
                ('videos_per_page', '6'),
                ('api_mode', '1'),
                ('homepage_queries', 'Top Trailers, Free Movies, New Games'),
                ('permalink_category', '/category/%id%/%slug%'),
                ('permalink_channel', '/channel/%id%/%slug%'),
                ('permalink_search', '/search/%query%'),
                ('permalink_single', '/watch/%slug%/%id%'),
                ('dmca_channel_ids', ''),
                ('dmca_search_queries', ''),
                ('dmca_ids', ''),
                ('adcode_header', ''),
                ('adcode_single', ''),
                ('adcode_download', ''),
                ('theme_desktop', 'mi-red'),
                ('theme_mobile', 'mi-red'),
                ('theme_logo', 'logo.png');");

            Db::query("
                ALTER TABLE `mi_apis`
                ADD PRIMARY KEY (`id`),
                ADD UNIQUE KEY `api_key` (`api_key`),
                MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
            Db::query("
                ALTER TABLE `mi_options`
                ADD UNIQUE KEY `name` (`name`);");
            Db::query("
                ALTER TABLE `mi_queries`
                ADD PRIMARY KEY (`id`),
                ADD UNIQUE KEY `query` (`query`),
                MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
        } catch (\PDOException $e) {
            $success = false;
        }

        return $success;
    }

    public static function lock()
    {
        $lock_file = apppath('installer.lock');
        return file_put_contents($lock_file, 'Installer locked successfully xD');
    }
}
