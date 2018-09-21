<?php

namespace Oishy\Model;

use Oishy\Core\Request;
use Oishy\Core\Csrf;
use Oishy\Core\Alert;
use Oishy\Core\Response;

/**
 * Model for Dashboard Operations
 *
 */
class DashModel
{
    public static function updatePermalinks()
    {
        $errors = [];
        // First make sure the csrf token is valid
        if (!Csrf::verify()) {
            $errors[] = 'Invalid request. Possible CSRF attempt! Go home kid.';
        }

        if (!empty($errors)) {
            Alert::add('error', $errors);
            return false;
        }

        // Reset the errors
        $errors = [];

        $permalink_search = Request::post('permalink_search');
        $permalink_channel = Request::post('permalink_channel');
        $permalink_category = Request::post('permalink_category');
        $permalink_single = Request::post('permalink_single');

        // Make sure we have %query% parameter in it
        if (mb_stripos($permalink_search, '%query%')) {
            OptionModel::update('permalink_search', strip_tags($permalink_search));
        } else {
            $errors[] = 'Search permalink must have <code>%query%</code> parameter in it.';
        }

        // Make sure we have %id% parameter in it
        if (mb_stripos($permalink_channel, '%id%')) {
            OptionModel::update('permalink_channel', strip_tags($permalink_channel));
        } else {
            $errors[] = 'Channel permalink must have <code>%id%</code> parameter in it.';
        }

        // Make sure we have %id% parameter in it
        if (mb_stripos($permalink_category, '%id%')) {
            OptionModel::update('permalink_category', strip_tags($permalink_category));
        } else {
            $errors[] = 'Category permalink must have <code>%id%</code> parameter in it.';
        }

        // Make sure we have %id% parameter in it
        if (mb_stripos($permalink_single, '%id%')) {
            OptionModel::update('permalink_single', strip_tags($permalink_single));
        } else {
            $errors[] = 'Single video permalink must have <code>%id%</code> parameter in it.';
        }


        if (!empty($errors)) {
            Alert::add('error', $errors);
            return false;
        }

        Alert::add('success', 'Permalinks were updated successfully!');
        return true;
    }

    public static function updateSettings()
    {
        // First make sure the csrf token is valid
        if (!Csrf::verify()) {
            Alert::add('error', 'Invalid request. Possible CSRF attempt! Go home kid.');
            return false;
        }

        $options = [
            'site_name' => Request::post('site_name'),
            'site_desc' => Request::post('site_desc'),
            'site_tagline' => Request::post('site_tagline'),
            'homepage_queries' => Request::post('homepage_queries'),
            'videos_per_page' => (int)Request::post('videos_per_page'),
            'related_videos' => (int)Request::post('related_videos'),
            'header_scripts' => Request::post('header_scripts', ''),
            'api_mode' => (int)Request::post('api_mode') === 2 ? 2 : 1
        ];

        foreach ($options as $key => $value) {
            if (!empty($value)) {
                if ($key !== 'header_scripts') {
                    $value = strip_tags($value);
                }
                OptionModel::update($key, $value);
            }
        }
        Alert::add('success', 'Settings were updated successfully!');
        return true;
    }

    public static function updateDmca()
    {
        // First make sure the csrf token is valid
        if (!Csrf::verify()) {
            Alert::add('error', 'Invalid request. Possible CSRF attempt! Go home kid.');
            return false;
        }


        $options = [
            'dmca_channel_ids' => Request::post('dmca_channel_ids', false),
            'dmca_search_queries' => Request::post('dmca_search_queries', false),
            'dmca_ids' => Request::post('dmca_ids', false)
        ];

        foreach ($options as $key => $value) {
            if ($value !== false) {
                OptionModel::update($key, strip_tags($value));
            }
        }
        Alert::add('success', 'DMCA IDs were updated successfully!');
        return true;
    }

    public static function updateAds()
    {
        // First make sure the csrf token is valid
        if (!Csrf::verify()) {
            Alert::add('error', 'Invalid request. Possible CSRF attempt! Go home kid.');
            return false;
        }


        $options = [
            'adcode_header' => Request::post('adcode_header', false),
            'adcode_single' => Request::post('adcode_single', false),
            'adcode_download' => Request::post('adcode_download', false)
        ];

        foreach ($options as $key => $value) {
            if ($value !== false) {
                OptionModel::update($key, $value);
            }
        }
        Alert::add('success', 'Adcodes were updated successfully!');
        return true;
    }


    public static function updatePass()
    {
        // First make sure the csrf token is valid
        if (!Csrf::verify()) {
            Alert::add('error', 'Invalid request. Possible CSRF attempt! Go home kid.');
            return false;
        }

        $current_pass = trim(Request::post('current_pass', false));
        $new_pass = trim(Request::post('new_pass', false));
        $new_pass_verify = trim(Request::post('new_pass_verify', false));
        $errors = [];

        if (!password_verify($current_pass, OptionModel::get('password'))) {
            $errors[] = 'Current password doesn\'t match!';
        }

        if (mb_strlen($new_pass) < 6) {
            $errors[] = 'New password must be at least 6 characters long!';
        }

        if ($new_pass !== $new_pass_verify && $new_pass) {
            $errors[] = 'New passwords doesn\'t match with each other!';
        }

        if (!empty($errors)) {
            Alert::add('error', $errors);
            return false;
        }

        if (OptionModel::update('password', password_hash($new_pass, PASSWORD_DEFAULT))) {
            AuthModel::logOut();
            Alert::add('success', 'Password were changed successfully. Please log in again!');
            return Response::redirect('dashboard/login', true, 301)->send(1);
        }

        Alert::add('error', 'Failed to change password. Possible Database error!');
        return false;
    }

    public static function updateTheme()
    {
        // First make sure the csrf token is valid
        if (!Csrf::verify()) {
            Alert::add('error', 'Invalid request. Possible CSRF attempt! Go home kid.');
            return false;
        }

        $theme_mobile = trim(Request::post('theme_mobile', false));
        $theme_desktop = trim(Request::post('theme_desktop', false));

        if (OptionModel::get('theme_mobile') === $theme_mobile) {
            $theme_mobile = false;
        }

        if (OptionModel::get('theme_desktop') === $theme_desktop) {
            $theme_desktop = false;
        }

        $theme_logo = Request::files('theme_logo', false);

        if (!empty($theme_logo['tmp_name'])) {
            $errors = [];
            $fileinfo = pathinfo($theme_logo['name']);
            $ext = mb_strtolower($fileinfo['extension']);

            if (!in_array($ext, ['png', 'jpg', 'gif', 'bmp'])) {
                $errors[] = 'Only PNG, JPG, GIF & BMP is allowed!';
            }

            $size = filesize($theme_logo['tmp_name']);
            $max_file_size = 2 * pow(1024, 2); // 2 MB

            if ($size > $max_file_size) {
                $errors[] = 'Maximum logo file size is 2MB!';
            }
            if (empty($errors)) {
                $filename = oishy_url_slug($fileinfo['filename']) . '.' . $ext;
                $location = basepath('assets/uploads/' . $filename);

                if (move_uploaded_file($theme_logo['tmp_name'], $location)
                    && OptionModel::update('theme_logo', $filename)) {
                    Alert::add('success', 'Theme logo uploaded successfully!');
                } else {
                    $errors[] = 'Failed to upload theme logo! Please check permissions of folders!';
                }
            } else {
                Alert::add('error', $errors);
            }
        }

        if ($theme_mobile) {
            Alert::add('success', 'Mobile theme changed successfully!');
            OptionModel::update('theme_mobile', $theme_mobile);
        }

        if ($theme_desktop) {
            Alert::add('success', 'Desktop theme changed successfully!');
            OptionModel::update('theme_desktop', $theme_desktop);
        }

        return true;
    }

    public static function addApi()
    {
        // First make sure the csrf token is valid
        if (!Csrf::verify()) {
            Alert::add('error', 'Invalid request. Possible CSRF attempt! Go home kid.');
            return false;
        }
        $errors = [];
        $api_key = Request::post('api_key');

        if (empty($api_key) || mb_strlen($api_key) < 39) {
            $errors[] = 'Please insert a valid YouTube v3 Server API Key!';
        }

        if (ApiModel::exists($api_key)) {
            $errors[] = 'The API already exists, no need to add again!';
        }

        if (!empty($errors)) {
            Alert::add('error', $errors);
            return false;
        }

        if (ApiModel::add($api_key)) {
            Alert::add('success', 'API Key was added successfully.');
            return true;
        } else {
            Alert::add('error', 'Failed to add API Key. Possible DB error.');
        }

        return false;
    }

    public static function deleteApi($id)
    {
        // First make sure the csrf token is valid
        if (!Csrf::verify()) {
            Alert::add('error', 'Invalid request. Possible CSRF attempt! Go home kid.');
            return false;
        }
        if (ApiModel::delete($id)) {
            Alert::add('success', 'The API was deleted successfully!');
            return true;
        }
        Alert::add('error', 'Failed to delete the API Key!');
        return false;
    }
}
