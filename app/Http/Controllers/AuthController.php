<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use stdClass;

class AuthController extends Controller
{
    public function login(Request $request) {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->first();
        $status = "error";
        $message = "";
        $data = new stdClass();
        $status_code = 401;
        if($user){
            if (Hash::check($request->password, $user->password)) {
                $user->generateToken();
                $status = 'success';
                $message = 'Login Successful';
                $data = $user->toArray();
                $status_code = 200;
            } else {
                $message = "Login failed, wrong password";
            }
        } else {
            $message = "Login failed, email not found";
        }
        return response()->json([
            'status' => $status,
            'message' => $message,
            'status code' => $status_code,
            'data' => $data,
        ]);
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
        $status = "error";
        $message = "";
        $data = new stdClass();
        $status_code = 400;
        if ($validator->fails()) {
            $errors = $validator->errors();
            $message = $errors;
        } else {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'roles' => json_encode(['CUSTOMER']),
            ]);
            if ($user) {
                $user->generateToken();
                $status = "success";
                $message = "register successfully";
                $data = $user->toArray();
                $status_code = 200;
            } else {
                $message = 'register failed';
            }
        }
        return response()->json([
            'status' => $status,
            'status code' => $status_code,
            'message' => $message,
            'data' => $data
        ]);
    }

    public function logout(Request $request) {
    /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user) {
            $user->api_token = null;
            $user->save();
        } return response()->json([
            'status' => 'success',
            'status code' => 200,
            'message' => 'logout berhasil',
            'data' => null
        ]);
    }
}
