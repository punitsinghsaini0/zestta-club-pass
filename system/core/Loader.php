<?php
class CI_Loader
{
    protected $CI;

    public function __construct($CI)
    {
        $this->CI = $CI;
    }

    public function library($library, $params = [])
    {
        $class = 'CI_'.ucfirst($library);
        $path = BASEPATH.'libraries/'.ucfirst($library).'.php';
        if (!file_exists($path)) {
            $path = APPPATH.'libraries/'.ucfirst($library).'.php';
        }
        if (!file_exists($path)) {
            show_error('Unable to load the requested library: '.$library);
        }
        require_once $path;
        if (!class_exists($class)) {
            $class = ucfirst($library);
        }
        $instance = new $class($params);
        $this->CI->$library = $instance;
        return $instance;
    }

    public function helper($helper)
    {
        $file = $helper.'_helper.php';
        $paths = [APPPATH.'helpers/'.$file, BASEPATH.'helpers/'.$file];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return true;
            }
        }
        show_error('Unable to load the requested helper: '.$helper);
    }

    public function model($model)
    {
        $file = APPPATH.'models/'.$model.'.php';
        if (!file_exists($file)) {
            show_error('Unable to locate the requested model: '.$model);
        }
        require_once $file;
        if (!class_exists($model)) {
            show_error('Model class '.$model.' not found.');
        }
        $instance = new $model();
        if (method_exists($instance, 'set_super_object')) {
            $instance->set_super_object($this->CI);
        }
        $this->CI->$model = $instance;
        return $instance;
    }

    public function view($view, array $vars = [], $return = false)
    {
        $view_file = APPPATH.'views/'.$view.'.php';
        if (!file_exists($view_file)) {
            show_error('Unable to load the requested file: '.$view);
        }

        extract($vars);
        ob_start();
        include $view_file;
        $buffer = ob_get_clean();

        if ($return) {
            return $buffer;
        }

        $this->CI->output->append_output($buffer);
        return $this;
    }

    public function database(array $params = [])
    {
        $this->CI->config->load('database');
        require_once BASEPATH.'database/DB.php';
        $dbConfig = $params ?: $this->CI->config->item('database');
        if (!$dbConfig) {
            show_error('Database configuration not found.');
        }
        $db = DB($dbConfig);
        $this->CI->db = $db;
        return $db;
    }
}

