<?php
class CI_Session
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function userdata($key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function set_userdata($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function unset_userdata($key)
    {
        unset($_SESSION[$key]);
    }

    public function sess_destroy()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}

