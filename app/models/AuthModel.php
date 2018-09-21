<?php

namespace Oishy\Model;

use Oishy\Core\Db;
use Oishy\Driver\Registry;
use Oishy\Core\Csrf;
use Oishy\Core\Response;
use Oishy\Core\Session;
use Oishy\Core\Request;
use Oishy\Core\Alert;

/**
* Model for Site Options
*
*/
class AuthModel
{
    protected static $session_name = '___mi_logged_in';

    public static function isLogged()
    {
        return !empty(Session::get(self::$session_name));
    }

    public static function verifyForm()
    {
        // First make sure the csrf token is valid
        if (!Csrf::verify()) {
            Alert::add('error', 'Invalid request. Possible CSRF attempt! Go home kid.');
            return false;
        }

        $errors = [];
        $username = trim(Request::post('username'));
        $password = trim(Request::post('password'));

        // Both fields are required
        if (empty($username) || empty($password)) {
            $errors[] = 'All fields are required!';
        }

        if (!self::login($username, $password)) {
            $errors[] = 'Invalid username/password combination. Please double check your mind!';
        }

        if (!empty($errors)) {
            Alert::add('error', $errors);
            return false;
        }
        // No errors? Seriously?
        // Lets log you in!
        Session::set(self::$session_name, sha1(OptionModel::get('username')));
        return true;
    }

    public static function logOut()
    {
        if (self::isLogged()) {
            Session::remove(self::$session_name);
            Session::regenerate();
            return true;
        }
        return false;
    }

    protected static function login($username, $password)
    {
        if (OptionModel::get('username') === $username) {
            return password_verify($password, OptionModel::get('password'));
        }
        // No luck? Wait, we will still verify the hash to slow down timing attacks
        password_verify($password, get_secure_token());
        return false;
    }
}
