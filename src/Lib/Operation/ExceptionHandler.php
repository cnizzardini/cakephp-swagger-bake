<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Operation;

use Cake\Core\Exception\CakeException;
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
        $exceptionClass = $this->findExceptionClass($throw);
        $this->message = trim($throw->getDescription()->getBodyTemplate());

        $http404s = [
            'MissingActionException',
            'PageOutOfBoundsException',
            'RecordNotFoundException',
            'MissingControllerException',
            'MissingRouteException',
        ];

        if (in_array($exceptionClass, $http404s)) {
            $this->code = '404';
        } else {
            $fqnList = [
                $exceptionClass,
                '\\' . $exceptionClass,
                "\Cake\Http\Exception\\" . $exceptionClass,
                "\Cake\Datasource\Exception\\" . $exceptionClass,
            ];

            $results = array_filter($fqnList, function ($fqn) {
                return class_exists($fqn);
            });

            foreach ($results as $exception) {
                $instance = new $exception();
                if ($instance instanceof CakeException && $instance->getCode() > 0) {
                    $this->code = (string)$instance->getCode();
                }
                $this->assignMessage($instance);
            }
        }

        $this->message = empty($this->message) ? 'Unknown Error' : $this->message;

        $this->code = $this->code < 400 ? 500 : $this->code;
    }

    /**
     * @param \phpDocumentor\Reflection\DocBlock\Tags\Throws $throw Throws
     * @return string
     */
    private function findExceptionClass(Throws $throw): string
    {
        $exceptionClass = $throw->getType()->__toString();
        if (substr($exceptionClass, 0, 1) == '\\') {
            $exceptionClass = substr($exceptionClass, 1);
        }

        $pieces = explode(' ', trim($exceptionClass));
        if (count($pieces) == 1) {
            $exceptionClass = reset($pieces);
        }

        return $exceptionClass;
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
