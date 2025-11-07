<?php
class CI_Exceptions extends Exception
{
    public function show_error($message, $status_code = 500)
    {
        show_error($message, $status_code);
    }

    public function show_404($page = '')
    {
        $message = 'The page you requested was not found.'.($page ? ' ['.$page.']' : '');
        show_error($message, 404);
    }
}

