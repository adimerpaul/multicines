<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('authToken')->accessToken;
                return response(['user' => $user, 'token' => $token]);
            } else {
                return response(['message' => 'Contraseña incorrecta']);
            }
        } else {
            return response(['message' => 'Usuario no encontrado']);
        }
    }
    public function me(Request $request)
    {
        return response(['user' => $request->user()]);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response(['message' => 'Sesión cerrada']);
    }
    public function index(){return User::all();}
    public function show(User $user){return $user;}
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'nick' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'state' => 'required',
            'fechaLimite' => 'required',
        ]);
        $user = User::create($validated);
        return response(['user' => $user]);
    }
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required',
            'nick' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'state' => 'required',
            'fechaLimite' => 'required',
        ]);
        $user->update($validated);
        return response(['user' => $user]);
    }
    public function destroy(User $user)
    {
        $user->delete();
        return response(['message' => 'Usuario eliminado']);
    }
}
