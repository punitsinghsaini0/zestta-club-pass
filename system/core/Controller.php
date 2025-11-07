<?php
class CI_Controller
{
    protected $CI;
    protected static $instance;
    public $load;
    public $input;
    public $output;
    public $config;
    public $security;
    public $router;
    public $uri;
    public $session;
    public $db;

    public function __construct()
    {
    }

    public function set_super_object($CI)
    {
        $this->CI = $CI;
        self::$instance = $CI;
        $this->load = $CI->load;
        $this->input = $CI->input;
        $this->output = $CI->output;
        $this->config = $CI->config;
        $this->security = $CI->security;
        $this->router = $CI->router;
        $this->uri = $CI->uri;
        $this->session = $CI->session ?? null;
        $this->db = $CI->db ?? null;
    }

    public static function &get_instance()
    {
        return self::$instance;
    }

    public function __get($name)
    {
        if (isset($this->CI->$name)) {
            return $this->CI->$name;
        }
        return null;
    }
}

