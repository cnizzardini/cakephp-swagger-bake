<?php

namespace SwaggerBake\Lib;

use Exception;

class ExceptionHandler
{
    private $message = 'Unknown Error';
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
                    $this->message = trim($instance->getMessage());
                    if (empty($this->message)) {
                        $this->message = get_class($instance);
                    }
                    $this->code = $instance->getCode();
                }
            }
        } catch(Exception $e) {

        }

        $this->message = empty($this->message) ? 'Unknown Error' : $this->message;
        $this->code = $this->code < 400 ? 500 : $this->code;
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