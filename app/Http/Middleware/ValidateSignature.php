<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\ValidateSignature as BaseValidateSignature;

class ValidateSignature extends BaseValidateSignature
{
    protected $except = [
        //
    ];
}
