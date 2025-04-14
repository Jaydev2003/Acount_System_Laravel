<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Constants\HttpStatusCode;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class RoleController extends Controller
{
    public function createRole(Request $request)
    {
        try {
            $token = $request->header('Authorization');
            
            if (!$token) {
                return response()->json([
                    'status' => HttpStatusCode::BAD_REQUEST,
                    'message' => 'Token is required'
                ]);
            }
    
            $user = JWTAuth::parseToken()->authenticate();
        }catch (TokenExpiredException $e) {
            return response()->json([
                'status' => HttpStatusCode::UNAUTHORIZED,
                'message' => 'Token has expired. Please login again.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => HttpStatusCode::UNAUTHORIZED,
                'message' => 'Unauthorized: Please login first'
            ]);
        }
        if (!$user->hasPermission('Add')) {
            return response()->json([
                'status' => HttpStatusCode::FORBIDDEN,
                'message' => 'Unauthorized: You do not have permission to create users'
            ]);
        }
    
        $isAdmin = DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->where('role_user.user_id', $user->id)
            ->where('roles.name', 'admin')
            ->exists();
    
        if (!$isAdmin) {
            return response()->json([
                'status' => HttpStatusCode::UNAUTHORIZED,
                'message' => 'Unauthorized: You do not have permission to Create Role'
            ]);
        }
    
        $validate = Validator::make($request->all(), [
            'role_name' => 'required|string|unique:roles,name|max:255',
        ]);
    
        if ($validate->fails()) {
            return response()->json([
                'errors' => $validate->errors(),
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Validation errors'
            ]);
        }
    
        $role = Role::create([
            'name' => $request->role_name,
        ]);
    
        return response()->json([
            'status' => HttpStatusCode::SUCCESS,
            'message' => 'Role created successfully',
            'data' => $role,
            
        ]);
    }
    
    public function getPermission()
    {
        try {

            $permission = Permission::getPermission();
            $roles = Role::with('permissions')->get();
    
            $formattedRoles = $roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'role_name' => $role->name,
                    'permissions' => $role->permissions->groupBy('groupby')->map(function ($permissions) {
                        return $permissions->pluck('name'); 
                    })
                ];
            });
    
            return response()->json([
                'status' => HttpStatusCode::SUCCESS,
                'message' => 'Roles with grouped permissions retrieved successfully',
                'data' => $formattedRoles,
                'permission'=> $permission
            ]);
    
        }catch (TokenExpiredException $e) {
            return response()->json([
                'status' => HttpStatusCode::UNAUTHORIZED,
                'message' => 'Token has expired. Please login again.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'An error occurred while fetching roles with permissions: ' . $e->getMessage()
            ]);
        }
    }

    

public function getRole(Request $request)
{ 
    try {
        $role = Role::with('permissions')->find($request->role_id);

        if (!$role) {
            return response()->json([
               'status' => HttpStatusCode::NOT_FOUND,
               'message' => 'Role not found'
            ]);
        }

        return response()->json([
           'status' => HttpStatusCode::SUCCESS,
           'message' => 'Role retrieved successfully',
            'data' => $role
        ]);
    }catch (TokenExpiredException $e) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Token has expired. Please login again.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
           'status' => HttpStatusCode::INTERNAL_SERVER_ERROR,
           'message' => 'An error occurred while fetching role: '. $e->getMessage()
        ]);
    }
}
    public function assignpermission(Request $request)
{
    try {
        $token = $request->header('Authorization');
        
        if (!$token) {
            return response()->json([
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Token is required'
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();
    }catch (TokenExpiredException $e) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Token has expired. Please login again.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Unauthorized: Please login first'
        ]);
    }

    $isAdmin = DB::table('role_user')
        ->join('roles', 'role_user.role_id', '=', 'roles.id')
        ->where('role_user.user_id', $user->id)
        ->where('roles.name', 'admin')
        ->exists();

    if (!$isAdmin) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Unauthorized: Only admins can assign permissions to roles'
        ]);
    }

    $validate = Validator::make($request->all(), [
        'role_id' => 'required|exists:roles,id',
        'groupby_id' => 'required|exists:permissions,groupby'
    ]);

    if ($validate->fails()) {
        return response()->json([
            'errors' => $validate->errors(),
            'status' => HttpStatusCode::BAD_REQUEST,
            'message' => 'Validation errors'
        ]);
    }

    $role = Role::find($request->role_id);

    if (!$role) {
        return response()->json([
            'status' => HttpStatusCode::NOT_FOUND,
            'message' => 'Role not found'
        ]);
    }

    $permissions = Permission::where('groupby', $request->groupby_id)->pluck('id');

    $role->permissions()->sync($permissions);

    return response()->json([
        'status' => HttpStatusCode::SUCCESS,
        'message' => 'Permissions assigned to role successfully'
    ]);
}

    
    
    
public function updateRole(Request $request)
{
    try {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json([
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Token is required'
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();
    } catch (TokenExpiredException $e) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Token has expired. Please login again.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Unauthorized: Please login first'
        ]);
    }

    if (!$user->hasPermission('Edit')) {
        return response()->json([
            'status' => HttpStatusCode::FORBIDDEN,
            'message' => 'Unauthorized: You do not have permission to edit roles'
        ]);
    }

    try {
        $validatedData = $request->validate([
            'role_id' => 'required|exists:roles,id',
            'role_name' => 'required|string|max:255|unique:roles,name,' . $request->role_id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $role = Role::findOrFail($validatedData['role_id']);

        $role->update([
            'name' => $validatedData['role_name'],
        ]);

        if (!empty($validatedData['permissions'])) {
            $role->permissions()->sync($validatedData['permissions']);
        }

        return response()->json([
            'status' => HttpStatusCode::SUCCESS,
            'message' => 'Role and permissions updated successfully',
            'data' => $role,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => HttpStatusCode::INTERNAL_SERVER_ERROR,
            'message' => 'An error occurred while updating the role: ' . $e->getMessage(),
        ]);
    }
}


    
    
public function deleteRole(Request $request)
{
    try {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json([
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Token is required'
            ]);
        }

        $user = JWTAuth::parseToken()->authenticate();
    } catch (TokenExpiredException $e) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Token has expired. Please login again.'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => HttpStatusCode::UNAUTHORIZED,
            'message' => 'Unauthorized: Please login first'
        ]);
    }

    if (!$user->hasPermission('Delete')) {
        return response()->json([
            'status' => HttpStatusCode::FORBIDDEN,
            'message' => 'Unauthorized: You do not have permission to delete roles'
        ]);
    }

    try {
        $validatedData = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($validatedData['role_id']);

        $role->permissions()->detach();
        $role->delete();

        return response()->json([
            'status' => HttpStatusCode::SUCCESS,
            'message' => 'Role deleted successfully'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => HttpStatusCode::INTERNAL_SERVER_ERROR,
            'message' => 'An error occurred while deleting the role: ' . $e->getMessage(),
        ]);
    }
}

    
}
