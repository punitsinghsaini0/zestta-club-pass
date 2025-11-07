<?php
class CI_Model
{
    protected $CI;

    public function set_super_object($CI)
    {
        $this->CI = $CI;
    }

    public function __get($name)
    {
        if (isset($this->CI->$name)) {
            return $this->CI->$name;
        }
        return null;
    }
}

