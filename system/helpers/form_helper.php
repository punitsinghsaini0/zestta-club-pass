<?php
function form_open($action = '', $attributes = [])
{
    $attr = '';
    foreach ($attributes as $key => $value) {
        $attr .= sprintf(' %s="%s"', htmlspecialchars($key), htmlspecialchars($value));
    }

    $token_field = '';
    if (config_item('csrf_protection')) {
        $CI = get_instance();
        $token_name = config_item('csrf_token_name');
        $token_value = $CI ? $CI->security->get_csrf_hash() : '';
        $token_field = sprintf('<input type="hidden" name="%s" value="%s"/>', htmlspecialchars($token_name), htmlspecialchars($token_value));
    }

    return sprintf('<form action="%s" method="post"%s>%s', htmlspecialchars($action), $attr, $token_field);
}

function form_close()
{
    return '</form>';
}

function form_input($name, $value = '', $attributes = [])
{
    $attr = '';
    foreach ($attributes as $key => $val) {
        $attr .= sprintf(' %s="%s"', htmlspecialchars($key), htmlspecialchars($val));
    }
    return sprintf('<input type="text" name="%s" value="%s"%s/>', htmlspecialchars($name), htmlspecialchars($value), $attr);
}

