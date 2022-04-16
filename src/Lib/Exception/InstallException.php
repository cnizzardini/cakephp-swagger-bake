<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Exception;

use Exception;
use Throwable;

class InstallException extends Exception
{
    private ?string $question = null;

    /**
     * @inheritDoc
     */
    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string|null
     */
    public function getQuestion(): ?string
    {
        return $this->question;
    }

    /**
     * @param string $question Present the user with a question on the exception, this provides the user to resolve
     *  the exception and continue with installation.
     * @return $this
     */
    public function setQuestion(string $question)
    {
        $this->question = $question;

        return $this;
    }
}
