<?php
namespace Oishy\Core;

/**
 * Base Controller
 *
 * This is the "base controller class". All other "real" controllers extend this class.
 *
 * @version 1.0
 * @since 0.1
 */
class Controller
{
    /**
     * Construct the (base) controller. This happens when a real controller is constructed, like in
     * the constructor of IndexController when it says: parent::__construct();
     *
     * You better use it so start sessions, checking auto-login cookies and log back the user in,
     * change the view directory or whatever you want!
     */
    public function __construct()
    {
        // Start a session
        Session::start();

        // Create the view object
        View::init(Config::get('view.directory'), Config::get('view.extension'));
    }
}
