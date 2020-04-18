<?php

namespace SwaggerBake\Lib;

use Exception;

class ExceptionHandler
{
    private $message = '';
    private $code = 500;

    public function __construct(string $exceptionClass)
    {
        if (substr($exceptionClass, 0 , 1) == '\\') {
            $exceptionClass = substr($exceptionClass, 1);
        }

        $trying = [
            $exceptionClass,
            '\\' . $exceptionClass,
            "\Cake\Http\Exception\\" . $exceptionClass,
        ];

        try {
            foreach ($trying as $try) {
                if (class_exists($try)) {
                    $instance = new $try();
                    $this->message = $instance->getMessage();
                    $this->code = $instance->getCode();
                }
            }
        } catch(Exception $e) {

        }

        if ($this->code < 400) {
            $this->message = 'Internal Server Error';
            $this->code = 500;
        }
    }

    public function getCode() : int
    {
        return $this->code;
    }

    public function getMessage() : string
    {
        return $this->message;
    }
}