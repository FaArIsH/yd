<?php

/**
 * The Classic Old Installer
 *
 * I know it sucks
 */

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require __DIR__ . '/oishy-config.php';


require_once SYSTEM . 'core/Functions.php';

$composer = require_once ROOT . 'app/vendor/autoload.php';
$composer->addPsr4('Oishy\\Core\\', SYSTEM . '/core');

use Oishy\Core\Alert;
use Oishy\Core\Config;
use Oishy\Core\Request;
use Oishy\Core\Response;
use Oishy\Core\Session;
use Oishy\Model\InstallerModel;

foreach (Config::get('autoload.aliases', []) as $namespace => $directory) {
    $composer->addPsr4($namespace . '\\', $directory);
}


$step = (int)Request::get('step', 0) ? (int)Request::get('step') : 1;
$locked = InstallerModel::isLocked();



Session::start();

if (!$locked) {
    if ($step === 2) {
        $db_host = Request::post('db_host', '');
        $db_name = Request::post('db_name', '');
        $db_username = Request::post('db_username', '');
        $db_password = Request::post('db_password', '');

        $dsn = "mysql:host={$db_host}; dbname={$db_name}; charset=utf8mb4";

        if (empty($db_host) || empty($db_name) || empty($db_username)) {
            Alert::add('error', "Missing required fields!");
            Response::redirect('install.php')->send(1);
        }


        try {
            $db = new PDO($dsn, $db_username, $db_password, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        } catch (PDOException $e) {
            Alert::add('error', "Failed to establish a database connnection. Error: {$e->getMessage()} ");
            Response::redirect('install.php')->send(1);
        }

    // Write the config file
        if (!InstallerModel::writeDbFile($dsn, $db_username, $db_password)) {
            Alert::add('error', "Failed to write the database config file!");
            Response::redirect('install.php')->send(1);
        }

        Alert::add('success', 'All right watson! We are now connected to the database!');
    }

    if ($step === 3) {
        $username = Request::post('username', '');
        $password = Request::post('password', '');
        $err = [];
        if (!preg_match('/^\w{4,}$/', $username)) {
            $err[] = 'Invalid username. Please use only alphanumeric characters!';
        }

        if (mb_strlen($password) < 6) {
            $err[] = 'Password needs to be at-least 6 character long!';
        }

        if (!empty($err)) {
            Alert::add('error', $err);
            Response::redirect('install.php')->send(1);
        }

        if (!InstallerModel::dumpSql($username, $password)) {
            Alert::add('error', 'Failed to dump the SQL queries into database! Please run the installer again!');
            Response::redirect('install.php')->send(1);
        }

        InstallerModel::lock();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge"/>
    <title>Installin' MiTube — Step <?=$step?></title>
    <link rel="shortcut icon" type="image/x-favicon" href="<?=base_url('favicon.ico')?>">
    <link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/spectre.min.css')?>">
    <link rel="stylesheet" type="text/css" href="<?=base_url('assets/css/install.css')?>">
</head>
<body>
    <div class="dash-container">
        <div class="logo-wrap">
            <h1><img src="<?=base_url('assets/img/setup-logo.png')?>" alt="Mi Installer"></h1>
        </div>
        <p>
            <?php
            Alert::show('error');
            Alert::show('success');
            ?>
        </p>
        <?php if ($locked) : ?>
            <div class="toast toast-error">
                Installer is locked. Please delete the lock file from <b>app/installer.lock</b> to unlock the installer again.
            </div>
        </div>
        <?php elseif ($step === 1) : ?>
        <form method="post" action="<?=base_url('install.php?step=2')?>">
            <div class="form-group">
                <label class="form-label" for="db_host">Database Host</label>
                <input type="text" name="db_host" id="db_host" class="form-input">
                <p>
                    Most likely localhost or 127.0.0.1.
                </p>
            </div>
            <div class="form-group">
                <label class="form-label" for="db_name">Database Name</label>
                <input type="text" name="db_name" id="db_name" class="form-input">
                <p>
                    Your database’s name.
                </p>
            </div>
            <div class="form-group">
                <label class="form-label" for="db_username">Database Username</label>
                <input type="text" name="db_username" id="db_username" class="form-input">
                <p>
                    The database user, usually root.
                </p>
            </div>
            <div class="form-group">
                <label class="form-label" for="db_password">Database User Password</label>
                <input type="password" name="db_password" id="db_password" class="form-input">
                <p>
                    The database user password.
                </p>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-blue btn-block">Get started</button>
            </div>
        </form>
        <?php elseif ($step === 2) : ?>
        <form method="post" action="<?=base_url('install.php?step=3')?>">
            <div class="form-group">
                <label class="form-label" for="username">Admin Username</label>
                <input type="text" name="username" id="username" class="form-input">
                <p>
                    Username for admin panel.
                </p>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">Admin Password</label>
                <input type="password" name="password" id="password" class="form-input">
                <p>
                    Password for admin panel
                </p>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-blue btn-block">Finish!</button>
            </div>
        </form>
        <?php elseif ($step === 3) :?>
        <div class="toast toast-success">
            All right! MiTube was installed successfully on this server. You may now log in to the Admin Dashboard and start spinnin'!<br>
            Thanks for purchasing or pirating! See ya!
        </div>
        <br/>
        <div class="form-group">
            <a href="<?=base_url('dashboard')?>" class="btn btn-blue btn-block">Admin Dashboard</a>
        </div>
        <div class="form-group">
            <a href="<?=base_url()?>" class="btn btn-blue btn-block">Visit Site</a>
        </div>

        <?php endif;?>

</div>
</body>
</html>
