<?php
class CI_Input
{
    protected $config;
    protected $ip_address;

    public function __construct(CI_Config $config)
    {
        $this->config = $config;
        $this->_sanitize_globals();
    }

    protected function _sanitize_globals()
    {
        if ($this->config->item('global_xss_filtering')) {
            $_GET = $this->xss_clean($_GET);
            $_POST = $this->xss_clean($_POST);
            $_COOKIE = $this->xss_clean($_COOKIE);
        }
    }

    public function ip_address()
    {
        if ($this->ip_address !== null) {
            return $this->ip_address;
        }
        $this->ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return $this->ip_address;
    }

    public function get($index = null)
    {
        return $index === null ? $_GET : ($_GET[$index] ?? null);
    }

    public function post($index = null)
    {
        return $index === null ? $_POST : ($_POST[$index] ?? null);
    }

    public function method($upper = false)
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        return $upper ? strtoupper($method) : $method;
    }

    public function xss_clean($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'xss_clean'], $data);
        }
        return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
    }
}

