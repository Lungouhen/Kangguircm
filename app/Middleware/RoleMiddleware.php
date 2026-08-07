<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Session;
use App\Models\Role;

class RoleMiddleware
{
    private string $requiredPermission;

    public function __construct(string $permission = '')
    {
        $this->requiredPermission = $permission;
    }

    public function handle(): bool
    {
        Session::start();
        
        $roleId = Session::get('role_id');
        if (!$roleId) {
            http_response_code(403);
            return false;
        }

        if ($this->requiredPermission) {
            $roleModel = new Role();
            if (!$roleModel->hasPermission($roleId, $this->requiredPermission)) {
                http_response_code(403);
                echo json_encode(['error' => 'Insufficient permissions']);
                return false;
            }
        }
        
        return true;
    }
}
