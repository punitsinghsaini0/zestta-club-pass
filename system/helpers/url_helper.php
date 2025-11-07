<?php
function site_url($uri = '')
{
    $base = config_item('base_url');
    if (!$base) {
        $base = base_url();
    }
    return rtrim($base, '/').'/'.ltrim($uri, '/');
}

function base_url($uri = '')
{
    $base = config_item('base_url');
    if (!$base) {
        $scheme = is_https() ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $path = rtrim(str_replace(basename($script), '', $script), '/');
        $base = $scheme.$host.$path.'/';
    }
    return rtrim($base, '/').'/'.ltrim($uri, '/');
}

function redirect($uri, $method = 'location', $code = 302)
{
    switch ($method) {
        case 'refresh':
            header("Refresh:0;url={$uri}");
            break;
        default:
            header("Location: {$uri}", true, $code);
            break;
    }
    exit;
}

