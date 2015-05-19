<?php
define('VALIDATE_ERROR_BASE', -1000);
define('VALIDATE_ERROR_EXIST', VALIDATE_ERROR_BASE - 1);
define('VALIDATE_ERROR_MIN', VALIDATE_ERROR_BASE - 2);
define('VALIDATE_ERROR_MAX', VALIDATE_ERROR_BASE - 3);
define('VALIDATE_ERROR_MINLEN', VALIDATE_ERROR_BASE - 4);
define('VALIDATE_ERROR_MAXLEN', VALIDATE_ERROR_BASE - 5);
define('VALIDATE_ERROR_WRONG_FORMAT', VALIDATE_ERROR_BASE - 6);
define('VALIDATE_ERROR_INCLUDE_VALUE', VALIDATE_ERROR_BASE - 7);
define('VALIDATE_ERROR_EXCLUDE_VALUE', VALIDATE_ERROR_BASE - 8);
define('VALIDATE_ERROR_CUSTOM', VALIDATE_ERROR_BASE - 9);

define('VALIDATE_DATATYPE_BASE', 1000);
define('VALIDATE_DATATYPE_INT', VALIDATE_DATATYPE_BASE + 1);
define('VALIDATE_DATATYPE_STRING', VALIDATE_DATATYPE_BASE + 2);
define('VALIDATE_DATATYPE_UIN', VALIDATE_DATATYPE_BASE + 3);
define('VALIDATE_DATATYPE_EMAIL', VALIDATE_DATATYPE_BASE + 4);
define('VALIDATE_DATATYPE_URL', VALIDATE_DATATYPE_BASE + 5);
define('VALIDATE_DATATYPE_TEL', VALIDATE_DATATYPE_BASE + 6);
define('VALIDATE_DATATYPE_MOBILE', VALIDATE_DATATYPE_BASE + 7);
define('VALIDATE_DATATYPE_ZIPCODE', VALIDATE_DATATYPE_BASE + 8);
define('VALIDATE_DATATYPE_NUMBER', VALIDATE_DATATYPE_BASE + 9);
define('VALIDATE_DATATYPE_DATETIME', VALIDATE_DATATYPE_BASE + 10);
define('VALIDATE_DATATYPE_CUSTOM', VALIDATE_DATATYPE_BASE + 11);
define('VALIDATE_DATATYPE_CUSTOM_BASE', VALIDATE_DATATYPE_BASE + 12);

class ValidateException extends Exception
{
    private $validate_config;
    private $default_message;

    function __construct($validate_config)
    {
        $this->validate_config = array();
        if(isset($validate_config))
        $this->validate_config = $validate_config;
        $this->default_message = array(
            VALIDATE_ERROR_EXIST => "%name%不能为空",
            VALIDATE_ERROR_MIN => "%name%必须大于或等于%min%",
            VALIDATE_ERROR_MAX => "%name%必须小于或等于%max%",
            VALIDATE_ERROR_MINLEN => "%name%的长度必须大于或等于%minlen%",
            VALIDATE_ERROR_MAXLEN => "%name%的长度必须小于或等于%maxlen%",
            VALIDATE_ERROR_WRONG_FORMAT => "%name%的格式不正确",
            VALIDATE_ERROR_INCLUDE_VALUE => "无效的%name%",
            VALIDATE_ERROR_EXCLUDE_VALUE => "无效的%name%",
            VALIDATE_ERROR_CUSTOM => "%name%的格式不正确"
        );
        $msg = $this->validate_config["msg"];
        if(!isset($msg))
            $msg = $this->default_message[$validate_config["type"]];
        foreach($this->validate_config as $key => $value)
        {
            $msg = str_replace('%'.$key.'%', $value, $msg);
        }
        $this->message = $msg;
    }
    public function getField()
    {
        $field = $this->validate_config["field"];
        return (isset($field) ? $field : "");
    }
    public function getFieldName()
    {
        $name = $this->validate_config["name"];
        return (isset($name) ? $name : "");
    }
    public function getType()
    {
        $type = $this->validate_config["type"];
        return (isset($type) ? $type : "");
    }
    public function getValue()
    {
        $value = $this->validate_config["value"];
        return (isset($value) ? $value : "");
    }
}

class Validator
{
    private $config;
    private $validator_config;
    private $custom_config;
    private $default_message;
    function __construct($config = array())
    {
        $this->config = $config;
        $this->validator_config = array(
            VALIDATE_DATATYPE_INT => '_validateInt',
            VALIDATE_DATATYPE_STRING => '_validateString',
            VALIDATE_DATATYPE_UIN => '_validateUin',
            VALIDATE_DATATYPE_EMAIL => '_validateEmail',
            VALIDATE_DATATYPE_URL => '_validateUrl',
            VALIDATE_DATATYPE_TEL => '_validateTel',
            VALIDATE_DATATYPE_MOBILE => '_validateMobile',
            VALIDATE_DATATYPE_ZIPCODE => '_validateZipCode',
            VALIDATE_DATATYPE_NUMBER => '_validateNumber',
            VALIDATE_DATATYPE_DATETIME => '_validateDatetime',
            VALIDATE_DATATYPE_CUSTOM => '_validateCustom'
        );
        $this->custom_config = array();
    }

    public function Validate($data)
    {
        if(isset($data))
        {
            foreach($this->config as $conf)
            {
                $field = $conf["field"];
                $datatype = $conf["datatype"];
                $required = $conf["required"];
                $include_values = $conf["include_values"];
                $exclude_values = $conf["exclude_values"];

                if(isset($field) && isset($datatype))
                {
                    $value = $data[$field];
                    if(isset($required) && $required == true)
                    {
                        $this->_validateExist($value, $conf);
                    }

                    if(isset($include_values))
                    {
                        $this->_validateIncludeValues($value, $conf);
                    }
                    if(isset($exclude_values))
                    {
                        $this->_validateExcludeValues($value, $conf);
                    }
                    if($value == "")
                        continue;

                    $validator_config = $this->validator_config;
                    $custom_config = $this->custom_config;

                    $custom_validator = $custom_config[$datatype];
                    if(isset($value) && isset($custom_validator) && is_callable($custom_validator))
                    {
                        $custom_validator($value, $conf);
                    }
                    else
                    {
                        $validator = $validator_config[$datatype];
                        if(isset($value) && isset($validator))
                        {
                            $this->$validator($value, $conf);
                        }
                    }
                }
                else
                {
                    throw new Exception("field和datatype不能为空");
                }
            }
        }
    }

    public function AddConfig($config)
    {
        $this->config[] = $config;
    }

    public function RegisterValidator($type, $validator)
    {
        $this->custom_config[$type] = $validator;
    }

    private function _validateExist($data, $config)
    {
        if(!isset($data) || $data == "")
        {
            $config["type"] = VALIDATE_ERROR_EXIST;
            throw new ValidateException($config);
        }
    }
    private function _validateInt($data, $config)
    {
        if(!(is_numeric($data) && ((int)$data) == $data))
        {
            $config["type"] = VALIDATE_ERROR_WRONG_FORMAT;
            throw new ValidateException($config);
        }
        $this->_validateRange($data, $config);
    }
    private function _validateString($data, $config)
    {
        if(!is_string($data))
        {
            $config["type"] = VALIDATE_ERROR_WRONG_FORMAT;
            throw new ValidateException($config);
        }
        $this->_validateLength($data, $config);
    }
    private function _validateUin($data, $config)
    {
        $this->_validateRegexp($data, $config, '/^[1-9]\d{4,11}$/');
    }
    private function _validateUrl($data, $config)
    {
        $this->_validateRegexp($data, $config, '/^http:\/\/[A-Za-z0-9-_]+\.[A-Za-z0-9]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"\"])*$/');
    }
    private function _validateEmail($data, $config)
    {
        $this->_validateRegexp($data, $config, '/^(?:[\w-]+\.?)*[\w-]+@(?:[\w-]+\.)+[\w]{2,3}$/');
    }
    private function _validateTel($data, $config)
    {
        $this->_validateRegexp($data, $config, '/^[+]{0,1}(\d){1,3}[ ]?([-]?((\d)|[ ]){1,12})+$/');
    }
    private function _validateMobile($data, $config)
    {
        $this->_validateRegexp($data, $config, '/^1[358]\d{9}$/');
    }
    private function _validateZipCode($data, $config)
    {
        $this->_validateRegexp($data, $config, '/^(\d){6}$/');
    }
    private function _validateNumber($data, $config)
    {
        $this->_validateRegexp($data, $config, '/^\d*$/');
        $this->_validateLength($data, $config);
    }
    private function _validateDatetime($data, $config)
    {
        if($data == "0000-00-00 00:00:00" || $data == "0000-00-00") return;
        $stamp = strtotime($data);
        if($stamp == false)
        {
            $config["type"] = VALIDATE_ERROR_WRONG_FORMAT;
            throw new ValidateException($config);
        }

        $month = date('m', $stamp);
        $day   = date('d', $stamp);
        $year  = date('Y', $stamp);
        if(!checkdate($month, $day, $year))
        {
            $config["type"] = VALIDATE_ERROR_WRONG_FORMAT;
            throw new ValidateException($config);
        }
        $this->_validateDatetimeRange($stamp, $config);
    }
    private function _validateCustom($data, $config)
    {
        $validator = $config["validator"];
        if(!$validator($data, $config))
        {
            $config["type"] = VALIDATE_ERROR_CUSTOM;
            throw new ValidateException($config);
        }
    }
    private function _validateRange($data, $config)
    {
        $min = $config["min"];
        $max = $config["max"];
        if($min && $data < $min)
        {
            $config["type"] = VALIDATE_ERROR_MIN;
            throw new ValidateException($config);
        }
        if($max && $data > $max)
        {
            $config["type"] = VALIDATE_ERROR_MAX;
            throw new ValidateException($config);
        }
    }
    private function _validateDatetimeRange($data, $config)
    {
        $min = strtotime($config["min"]);
        $max = strtotime($config["max"]);
        if($min && $data < $min)
        {
            $config["type"] = VALIDATE_ERROR_MIN;
            throw new ValidateException($config);
        }
        if($max && $data > $max)
        {
            $config["type"] = VALIDATE_ERROR_MAX;
            throw new ValidateException($config);
        }
    }
    private function _validateLength($data, $config)
    {
        $minlen = $config["minlen"];
        $maxlen = $config["maxlen"];
        $strlen = mb_strlen($data, "ASCII");
        if($minlen && $strlen < $minlen)
        {
            $config["type"] = VALIDATE_ERROR_MINLEN;
            throw new ValidateException($config);
        }
        if($maxlen && $strlen > $maxlen)
        {
            $config["type"] = VALIDATE_ERROR_MAXLEN;
            throw new ValidateException($config);
        }
    }
    private function _validateRegexp($data, $config, $regexp)
    {
        if(preg_match($regexp, $data) <= 0)
        {
            $config["type"] = VALIDATE_ERROR_WRONG_FORMAT;
            throw new ValidateException($config);
        }
    }
    private function _validateIncludeValues($data, $config)
    {
        $values = $config["include_values"];
        foreach($values as $value)
        {
            if($value == $data)
                return;
        }
        $config["type"] = VALIDATE_ERROR_INCLUDE_VALUE;
        throw new ValidateException($config);
    }
    private function _validateExcludeValues($data, $config)
    {
        $values = $config["exclude_values"];
        foreach($values as $value)
        {
            if($value == $data)
            {
                $config["type"] = VALIDATE_ERROR_EXCLUDE_VALUE;
                throw new ValidateException($config);
            }
        }
    }
}
?>
