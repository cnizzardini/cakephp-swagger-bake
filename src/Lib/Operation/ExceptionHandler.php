<?php

namespace SwaggerBake\Lib\Operation;

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

        $pieces = explode(' ', trim($exceptionClass));
        if (count($pieces) == 1) {
            $exceptionClass = reset($pieces);
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
                    $this->code = $instance->getCode();
                    $this->assignMessage($instance);
                }
            }
        } catch(Exception $e) {

        }

        $this->message = empty($this->message) ? 'Unknown Error' : $this->message;
        $this->code = $this->code < 400 ? 500 : $this->code;
    }

    /**
     * Assigns ExceptionHandler::message using the Exception $instance argument
     *
     * @param $instance
     */
    private function assignMessage($instance) : void
    {
        $this->message = trim($instance->getMessage());
        if (!empty($this->message)) {
            return;
        }

        $class = get_class($instance);
        $pieces = explode('\\', $class);
        if (!empty($pieces)) {
            $this->message = end($pieces);
            return;
        }

        $this->message = $class;
    }

    /**
     * @return int
     */
    public function getCode() : int
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage() : string
    {
        return $this->message;
    }
}