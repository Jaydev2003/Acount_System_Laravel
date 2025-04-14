<?php
namespace App\Http\Controllers\API;
use App\Mail\UserCredentialsMail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\UserToken;
use App\Constants\HttpStatusCode;
use App\Models\permissionRole;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class UserController extends Controller
{
    public function create(Request $request)
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

        if (!$user->hasPermission('Add-user')) {
            return response()->json([
                'status' => HttpStatusCode::FORBIDDEN,
                'message' => 'Unauthorized: You do not have permission to create users'
            ]);
        }

        $validateUser = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required|exists:roles,id'
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Validation errors',
                'errors' => $validateUser->errors(),
            ]);
        }

        $randomPassword = Str::random(12);
        $hashedPassword = Hash::make($randomPassword);


        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $hashedPassword,
            'role_id' => $request->role_id
        ]);

        Mail::to($request->email)->send(new UserCredentialsMail($newUser, $randomPassword));

        return response()->json([
            'status' => HttpStatusCode::CREATED,
            'message' => 'User created successfully. Login credentials have been sent to their email.',
            'data' => $newUser,
        ]);
    }




    public function display(Request $request)
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
        }  catch (\Exception $e) {
            return response()->json([
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Unauthorized: Please login first'
            ]);
        }


        if (!$user->hasPermission('View-user')) {
            return response()->json([
                'status' => HttpStatusCode::FORBIDDEN,
                'message' => 'Unauthorized: You do not have permission to View users'
            ]);
        }

        $users = User::with('role')->get();

        return response()->json([
            'status' => HttpStatusCode::SUCCESS,
            'message' => 'Users fetched successfully',
            'data' => $users,
        ]);

    }
    public function getUserData(Request $request)
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
    
        if (!$user->hasPermission('View-user')) {
            return response()->json([
                'status' => HttpStatusCode::FORBIDDEN,
                'message' => 'Unauthorized: You do not have permission to view users'
            ]);
        }
    
        try {
            $userData = User::find($request->id);
    
            if (!$userData) {
                return response()->json([
                    'status' => HttpStatusCode::NOT_FOUND,
                    'message' => 'User not found'
                ]);
            }
    
            return response()->json([
                'status' => HttpStatusCode::SUCCESS,
                'message' => 'User fetched successfully',
                'data' => $userData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => HttpStatusCode::INTERNAL_SERVER_ERROR,
                'message' => 'An error occurred while fetching the user: ' . $e->getMessage()
            ]);
        }
    }
    



    // UPDATE
    public function update(Request $request)
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
        }  catch (\Exception $e) {
            return response()->json([
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Unauthorized: Please login first'
            ]);
        }

        if (!$user->hasPermission('Edit-user')) {
            return response()->json([
                'status' => HttpStatusCode::FORBIDDEN,
                'message' => 'Unauthorized: You do not have permission to Update users'
            ]);
        }

        $id = $request->id;

        if (!$id) {
            return response()->json([
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'User ID is required'
            ]);
        }

        $validateUser = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'role_id' => 'required|exists:roles,id'
        ]);

        if ($validateUser->fails()) {
            return response()->json([
                'errors' => $validateUser->errors(),
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Validation errors'
            ]);
        }

        $userToUpdate = User::findOrFail($id);

        $userToUpdate->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        return response()->json([
            'status' => HttpStatusCode::SUCCESS,
            'message' => 'User updated successfully',
            'data' => $userToUpdate,
        ]);
    }



    public function delete(Request $request)
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
        }  catch (\Exception $e) {
            return response()->json([
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'Unauthorized: Please login first'
            ]);
        }

        if (!$user->hasPermission('Delete-user')) {
            return response()->json([
                'status' => HttpStatusCode::FORBIDDEN,
                'message' => 'Unauthorized: You do not have permission to Delete users'
            ]);
        }
        $id = $request->id;

        if (!$id) {
            return response()->json([
                'status' => HttpStatusCode::BAD_REQUEST,
                'message' => 'User ID is required'
            ]);
        }

        $userToDelete = User::findOrFail($id);
        $userToDelete->delete();

        return response()->json([
            'status' => HttpStatusCode::SUCCESS,
            'message' => 'User deleted successfully'
        ]);
    }


}
