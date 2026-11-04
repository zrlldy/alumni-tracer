<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SystemExpiredException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'The system license is invalid or expired.'
        );
    }

    public function render(Request $request): Response
    {
        return response()->view(
            'errors.system-expired',
            [],
            503,
        );
    }
}