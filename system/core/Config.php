<?php
class CI_Config
{
    protected $config = [];

    public function load($file)
    {
        $file_path = APPPATH.'config/'.$file.'.php';
        if (!file_exists($file_path)) {
            return [];
        }

        $config = [];
        $autoload = [];
        include $file_path;

        $values = [];
        if (isset($config) && is_array($config)) {
            $values = $config;
        } elseif (isset($autoload) && is_array($autoload)) {
            $values = $autoload;
        }

        if (!empty($values)) {
            $this->config = array_merge($this->config, $values);
        }

        return $values;
    }

    public function item($item, $index = '')
    {
        if ($index === '') {
            return $this->config[$item] ?? null;
        }

        return $this->config[$index][$item] ?? null;
    }

    public function set_item($item, $value)
    {
        $this->config[$item] = $value;
    }

    public function slash_item($item)
    {
        $value = $this->item($item);
        if ($value === null) {
            return null;
        }
        return rtrim($value, '/').'/';
    }
}

