<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Exception;
use phpDocumentor\Reflection\DocBlock\Tags\Throws;

/**
 * Class ExceptionHandler
 *
 * @package SwaggerBake\Lib\Operation
 */
class ExceptionHandler
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $code = '500';

    /**
     * @param \phpDocumentor\Reflection\DocBlock\Tags\Throws $throw Throws
     */
    public function __construct(Throws $throw)
    {
        $exceptionClass = $throw->getType()->__toString();
        $this->message = trim($throw->getDescription()->getBodyTemplate());

        if (substr($exceptionClass, 0, 1) == '\\') {
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
            "\Cake\Datasource\Exception\\" . $exceptionClass,
        ];

        try {
            foreach ($trying as $try) {
                if (class_exists($try)) {
                    $instance = new $try();
                    $this->code = $instance->getCode();
                    $this->assignMessage($instance);
                }
            }
        } catch (Exception $e) {
        }

        $this->message = empty($this->message) ? 'Unknown Error' : $this->message;

        $this->code = $this->code < 400 ? 500 : $this->code;
    }

    /**
     * Assigns ExceptionHandler::message using the Exception $instance argument
     *
     * @param \Exception $instance Exception
     * @return void
     */
    private function assignMessage(Exception $instance): void
    {
        if (!empty($this->message)) {
            return;
        }

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
     * @return string|int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
