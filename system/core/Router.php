<?php
class CI_Router
{
    protected $class;
    protected $method;
    protected $params = [];
    protected $config;
    protected $uri;

    public function __construct(CI_Config $config, CI_URI $uri)
    {
        $this->config = $config;
        $this->uri = $uri;
        $this->set_routing();
    }

    protected function set_routing()
    {
        $routes = [];
        $routes_file = APPPATH.'config/routes.php';
        if (file_exists($routes_file)) {
            include $routes_file;
        }

        $segments = $this->uri->rsegment_array();
        if (empty($segments)) {
            $default = $routes['default_controller'] ?? 'Welcome/index';
            $segments = explode('/', $default);
        }

        $this->class = ucfirst(array_shift($segments));
        $this->method = $segments ? array_shift($segments) : 'index';
        $this->params = $segments;
    }

    public function fetch_class()
    {
        return $this->class;
    }

    public function fetch_method()
    {
        return $this->method;
    }

    public function fetch_parameters()
    {
        return $this->params;
    }
}

