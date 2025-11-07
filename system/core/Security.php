<?php
class CI_Security
{
    protected $config;
    protected $input;
    protected $csrf_token_name;
    protected $csrf_cookie_name;
    protected $csrf_expire;

    public function __construct(CI_Config $config, CI_Input $input)
    {
        $this->config = $config;
        $this->input = $input;
        $this->csrf_token_name = $config->item('csrf_token_name') ?? 'csrf_token';
        $this->csrf_cookie_name = $config->item('csrf_cookie_name') ?? 'csrf_cookie';
        $this->csrf_expire = (int)($config->item('csrf_expire') ?? 7200);
        $this->csrf_verify();
    }

    public function get_csrf_hash()
    {
        if (isset($_SESSION[$this->csrf_token_name])) {
            return $_SESSION[$this->csrf_token_name];
        }
        $token = bin2hex(random_bytes(16));
        $_SESSION[$this->csrf_token_name] = $token;
        setcookie($this->csrf_cookie_name, $token, time() + $this->csrf_expire, '/', '', is_https(), true);
        return $token;
    }

    protected function csrf_verify()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if ($this->input->method(true) !== 'POST') {
            $this->get_csrf_hash();
            return;
        }

        $token = $_SESSION[$this->csrf_token_name] ?? null;
        $posted = $_POST[$this->csrf_token_name] ?? null;

        if (!$token || !$posted || !hash_equals($token, $posted)) {
            show_error('The action you have requested is not allowed due to invalid CSRF token.', 403);
        }

        unset($_POST[$this->csrf_token_name]);
        $this->get_csrf_hash();
    }
}

