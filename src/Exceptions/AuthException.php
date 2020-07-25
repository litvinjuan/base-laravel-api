<?php

namespace Walsh\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthException extends HttpException
{
    public static function alreadyAuthenticated(): self
    {
        return new static(401, trans('errors.auth.already-authenticated'));
    }

    public static function callbackError(string $message)
    {
        return new static(400, trans('errors.auth.callback-error', ['message' => $message]));
    }
}
