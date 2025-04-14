<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class permissionRole extends Model
{
    protected $table = 'permission_role';

     public static function getPermissions($slug , $role_id)
     {
           return permissionRole::select('permission_role.id')
                  ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('permission_role.role_id', '=', $role_id)
                ->where('permissions.slug', '=', $slug)
                ->count();
    }
}
