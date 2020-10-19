<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Spatie\Permission\Contracts\Permission as ContractsPermission;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    #Roles and permissions helpers
    
    # 1-method to add permisssion
    public function addPermisssion(Request $request, string $guard_name="api") {
        $validator = \Validator::make($request->all(), [
            'name' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $permission = Permission::firstOrCreate(
            ['name' => $request->name],
            ['guard_name' => $request->has('guard_name') ? $request->guard_name : $guard_name],
        );

        return response()->json(['permission' => $permission]);
    }
    
    #----------------------------------------------------

    # 2- method to add role
    public function addRole(Request $request, string $guard_name="api") {
        $validator = \Validator::make($request->all(), [
            'name' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $role = Role::firstOrCreate(
            ['name'       => $request->name],
            ['guard_name' => $request->has('guard_name') ? $request->guard_name : $guard_name], 
        );

        return response()->json(['role' => $role]);
    }

    #----------------------------------------------------
    
    # 3- method to assign permission|s to a role
    public function assignPermissionToRole(Role $role, Request $request) {
        $validator = \Validator::make($request->all(), [
            'permissions' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }
        
        $role->syncPermissions($request->permissions);

        return response()->json(['role-permissions' => $role->permissions]);
    }

    #----------------------------------------------------

    # 4- method to assign role|s to a user
    public function assignRoleToUser(User $user, Request $request, string $guard_name="api") {
        $validator = \Validator::make($request->all(), [
            'roles' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        }

        $user->syncRoles($request->roles);

        return response()->json(['user_roles' => $user->roles]);
    }

    #----------------------------------------------------
    
    # 6- method check if user has permission to ...
    public function checkIfHasPermissionTo(User $user, Request $request, string $guard_name="api") {
        $user->hasPermissionTo($request->permission, $request->has('guard_name') ? $request->guard_name : $guard_name);
    }

    #----------------------------------------------------

    # 7- method check if user has role of ...
    public function checkIfHasRole(User $user, Request $request, string $guard_name="api") {
        $user->hasRole($request->name, $request->has('guard_name') ? $request->guard_name : $guard_name);
    }
}
