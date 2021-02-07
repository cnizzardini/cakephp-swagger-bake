<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Core\Exception\CakeException;
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
    private $message = 'Application Error';

    /**
     * @var string
     */
    private $code = '500';

    /**
     * @param \phpDocumentor\Reflection\DocBlock\Tags\Throws $throw Throws
     */
    public function __construct(Throws $throw)
    {
        $httpCode = 500;
        $exceptionClass = $throw->getType()->__toString();
        $message = $throw->getDescription()->getBodyTemplate();

        if (class_exists($exceptionClass)) {
            $instance = new $exceptionClass();
            if ($instance instanceof CakeException && $instance->getCode() > 0) {
                $httpCode = (int)$instance->getCode();
            }
            if (empty($message)) {
                $class = get_class($instance);
                $pieces = explode('\\', $class);
                if (!empty($pieces)) {
                    $message = end($pieces);
                }
            }
        }

        if ($exceptionClass == '\Cake\Datasource\Exception\RecordNotFoundException') {
            $httpCode = 404;
        }

        $this->code = (string)$httpCode;
        $this->message = $message ?? $this->message;
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
