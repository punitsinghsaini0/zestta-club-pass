<?php
class CI_Output
{
    protected $output = '';
    protected $status_code = 200;
    protected $content_type = 'text/html; charset=UTF-8';

    public function set_output($output)
    {
        $this->output = $output;
        return $this;
    }

    public function set_status_header($code)
    {
        $this->status_code = (int)$code;
        return $this;
    }

    public function set_content_type($mime)
    {
        $this->content_type = $mime;
        return $this;
    }

    public function append_output($data)
    {
        $this->output .= $data;
        return $this;
    }

    public function get_output()
    {
        return $this->output;
    }

    public function send_output()
    {
        http_response_code($this->status_code);
        header('Content-Type: '.$this->content_type);
        echo $this->output;
    }
}

