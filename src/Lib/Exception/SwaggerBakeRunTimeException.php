<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Exception;

use Cake\Core\Exception\CakeException;
use Throwable;

/**
 * Class SwaggerBakeRunTimeException
 *
 * @package SwaggerBake\Lib\Exception
 */
class SwaggerBakeRunTimeException extends CakeException
{
    /**
     * Constructor.
     *
     * Allows you to create exceptions that are treated as framework errors and disabled
     * when debug mode is off.
     *
     * @param array|string $message Either the string of the error message, or an array of attributes
     *   that are made available in the view, and sprintf()'d into Exception::$_messageTemplate
     * @param int|null $code The error code
     * @param \Throwable|null $previous the previous exception.
     */
    public function __construct($message = '', ?int $code = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
