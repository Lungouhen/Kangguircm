<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Response;
use App\Core\Session;
use App\Core\Csrf;

class AuthMiddleware
{
    public function handle(): bool
    {
        Session::start();
        
        if (!Session::has('user_id')) {
            http_response_code(401);
            Response::redirect('/login');
            return false;
        }
        
        return true;
    }
}
