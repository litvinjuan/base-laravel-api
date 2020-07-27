<?php

namespace Walsh\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class PasswordException extends HttpException
{
    public static function couldNotSendResetEmail(string $response): self
    {
        return new static(400, trans($response));
    }

    public static function resetError(): self
    {
        return new static(400, trans('errors.password.reset-error'));
    }
}
