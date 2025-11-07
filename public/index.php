<?php
/**
 * Front controller for the simplified CodeIgniter stack.
 */

define('ENVIRONMENT', $_SERVER['CI_ENV'] ?? 'development');

define('FCPATH', __DIR__.DIRECTORY_SEPARATOR);

define('APPPATH', realpath(__DIR__.'/../application/').DIRECTORY_SEPARATOR);

define('BASEPATH', realpath(__DIR__.'/../system/').DIRECTORY_SEPARATOR);

require BASEPATH.'core/CodeIgniter.php';
