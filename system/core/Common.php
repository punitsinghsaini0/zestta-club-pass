<?php
/**
 * Simplified CodeIgniter 3 compatibility layer - Common utilities
 *
 * This project runs in an offline environment, so we re-implemented the
 * essentials from CodeIgniter 3 to provide a familiar API surface.
 */

define('CI_VERSION', '3.1.13');

define('FCPATH', defined('FCPATH') ? FCPATH : getcwd().DIRECTORY_SEPARATOR);

if (!function_exists('get_instance')) {
    function &get_instance()
    {
        static $CI;
        if ($CI !== null) {
            return $CI;
        }

        if (class_exists('CI_Controller')) {
            $CI = CI_Controller::get_instance();
        }

        return $CI;
    }
}

if (!function_exists('is_cli')) {
    function is_cli()
    {
        return php_sapi_name() === 'cli' || defined('STDIN');
    }
}

if (!function_exists('log_message')) {
    function log_message($level, $message)
    {
        $level = strtoupper($level);
        error_log('[CI] '.$level.': '.$message);
    }
}

if (!function_exists('show_error')) {
    function show_error($message, $status_code = 500)
    {
        http_response_code($status_code);
        echo '<h1>Error</h1><p>'.htmlspecialchars($message, ENT_QUOTES, 'UTF-8').'</p>';
        exit;
    }
}

if (!function_exists('config_item')) {
    function config_item($item)
    {
        static $config;
        if ($config === null) {
            $config = &get_config();
        }
        return $config[$item] ?? null;
    }
}

if (!function_exists('get_config')) {
    function &get_config(array $replace = [])
    {
        static $config;

        if ($config === null) {
            $file_path = APPPATH.'config/config.php';
            if (!file_exists($file_path)) {
                show_error('The configuration file does not exist.');
            }
            $config = [];
            require $file_path;

            if (!isset($config) || !is_array($config)) {
                show_error('Your config file does not appear to be formatted correctly.');
            }
        }

        if (!empty($replace)) {
            foreach ($replace as $key => $val) {
                $config[$key] = $val;
            }
        }

        return $config;
    }
}

if (!function_exists('load_class')) {
    function &load_class($class, $directory = 'core', $param = null)
    {
        static $classes = [];

        $class = trim($class, '/\\');

        if (isset($classes[$class])) {
            return $classes[$class];
        }

        $paths = [APPPATH, BASEPATH];
        foreach ($paths as $path) {
            $file = $path.$directory.'/'.$class.'.php';
            if (file_exists($file)) {
                require_once $file;
                $lower = strtolower($class);
                $class_name = class_exists($class, false) ? $class : 'CI_'.$class;
                if (!class_exists($class_name)) {
                    $class_name = ucfirst($class);
                }
                $classes[$class] = $param !== null ? new $class_name($param) : new $class_name();
                return $classes[$class];
            }
        }

        show_error('Unable to locate the specified class: '.$class.'.php');
    }
}

if (!function_exists('is_https')) {
    function is_https()
    {
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        return isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443';
    }
}

