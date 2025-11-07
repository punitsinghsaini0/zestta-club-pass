<?php
class CI_URI
{
    protected $segments = [];
    protected $config;

    public function __construct(CI_Config $config)
    {
        $this->config = $config;
        $this->parse_uri();
    }

    protected function parse_uri()
    {
        $path = '/';
        if (isset($_SERVER['PATH_INFO'])) {
            $path = $_SERVER['PATH_INFO'];
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        } elseif (is_cli() && isset($_SERVER['argv'][1])) {
            $path = $_SERVER['argv'][1];
        }

        $path = trim($path, '/');
        if ($path === '') {
            $this->segments = [];
            return;
        }

        $this->segments = array_values(array_filter(explode('/', $path)));
    }

    public function segment($n)
    {
        $index = $n - 1;
        return $this->segments[$index] ?? null;
    }

    public function rsegment_array()
    {
        return $this->segments;
    }

    public function total_segments()
    {
        return count($this->segments);
    }
}

