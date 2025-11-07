<?php
defined('BASEPATH') or exit('No direct script access allowed');

$base_url = '';
if (isset($_SERVER['HTTP_HOST'])) {
    $scheme = is_https() ? 'https://' : 'http://';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $path = rtrim(str_replace(basename($script), '', $script), '/');
    $base_url = $scheme.$_SERVER['HTTP_HOST'].$path.'/';
}

$config = [
    'base_url' => $base_url,
    'index_page' => '',
    'uri_protocol' => 'REQUEST_URI',
    'charset' => 'UTF-8',
    'enable_hooks' => false,
    'subclass_prefix' => 'MY_',
    'composer_autoload' => false,
    'permitted_uri_chars' => 'a-z 0-9~%.:_\-'
];

$config['csrf_protection'] = true;
$config['csrf_token_name'] = 'csrf_token';
$config['csrf_cookie_name'] = 'csrf_cookie';
$config['csrf_expire'] = 7200;
$config['csrf_regenerate'] = true;
$config['csrf_exclude_uris'] = [];

$config['global_xss_filtering'] = true;
$config['cookie_prefix'] = '';
$config['cookie_domain'] = '';
$config['cookie_path'] = '/';
$config['cookie_secure'] = false;
$config['cookie_httponly'] = true;

$config['encryption_key'] = '';
$config['time_reference'] = 'local';
$config['proxy_ips'] = '';

$config['log_threshold'] = 0;
$config['log_path'] = '';
$config['log_file_extension'] = '';
$config['log_file_permissions'] = 0644;
$config['log_date_format'] = 'Y-m-d H:i:s';

$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'ci_session';
$config['sess_expiration'] = 7200;
$config['sess_save_path'] = sys_get_temp_dir();
$config['sess_match_ip'] = false;
$config['sess_time_to_update'] = 300;
$config['sess_regenerate_destroy'] = false;

