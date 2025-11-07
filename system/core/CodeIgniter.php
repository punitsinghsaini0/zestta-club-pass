<?php
/**
 * Simplified CodeIgniter 3 bootstrap.
 */

if (!defined('APPPATH') || !defined('BASEPATH')) {
    show_error('Application path constants not defined.');
}

require_once BASEPATH.'core/Common.php';
require_once BASEPATH.'core/Exceptions.php';
require_once BASEPATH.'core/Config.php';
require_once BASEPATH.'core/URI.php';
require_once BASEPATH.'core/Router.php';
require_once BASEPATH.'core/Input.php';
require_once BASEPATH.'core/Security.php';
require_once BASEPATH.'core/Output.php';
require_once BASEPATH.'core/Loader.php';
require_once BASEPATH.'core/Controller.php';
require_once BASEPATH.'core/Model.php';

class CI_Application
{
    /** @var CI_Config */
    public $config;
    /** @var CI_URI */
    public $uri;
    /** @var CI_Router */
    public $router;
    /** @var CI_Input */
    public $input;
    /** @var CI_Security */
    public $security;
    /** @var CI_Output */
    public $output;
    /** @var CI_Loader */
    public $load;
    /** @var CI_Session */
    public $session;
    /** @var CI_DB */
    public $db;

    public function __construct()
    {
        $this->config = new CI_Config();
        $this->config->load('config');

        $this->input = new CI_Input($this->config);
        $this->security = new CI_Security($this->config, $this->input);
        $this->output = new CI_Output();
        $this->uri = new CI_URI($this->config);
        $this->router = new CI_Router($this->config, $this->uri);

        $this->load = new CI_Loader($this);

        $this->autoloadResources();
    }

    protected function autoloadResources()
    {
        $autoload = $this->config->load('autoload');
        if (!is_array($autoload)) {
            return;
        }

        if (!empty($autoload['helper'])) {
            foreach ($autoload['helper'] as $helper) {
                $this->load->helper($helper);
            }
        }

        if (!empty($autoload['libraries'])) {
            foreach ($autoload['libraries'] as $library) {
                if ($library === 'database') {
                    $this->db = $this->load->database();
                } else {
                    $this->$library = $this->load->library($library);
                }
            }
        }
    }

    public function run()
    {
        $controller = $this->router->fetch_class();
        $method = $this->router->fetch_method();

        $controller_file = APPPATH.'controllers/'.$controller.'.php';
        if (!file_exists($controller_file)) {
            show_error('Unable to locate the requested controller: '.$controller, 404);
        }

        require_once $controller_file;

        if (!class_exists($controller)) {
            show_error('Controller class '.$controller.' not found in file.');
        }

        $class = new $controller();
        $class->set_super_object($this);

        if (!method_exists($class, $method)) {
            show_error('The controller method you are trying to access is not available: '.$method, 404);
        }

        $response = call_user_func_array([$class, $method], $this->router->fetch_parameters());

        if ($response !== null) {
            $this->output->set_output($response);
        }

        $this->output->send_output();
    }
}

$app = new CI_Application();
$app->run();

