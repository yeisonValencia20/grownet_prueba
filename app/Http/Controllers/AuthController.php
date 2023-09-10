<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request) {
        // validar los datos del formulario
        $rules = [
            'name' => 'required|string|max:15|regex:/^[A-Za-z\s]+$/',
            'password' => 'required'
        ];

        // Define mensajes de error personalizados
        $customMessages = [
            'name.*' => 'El nombre es obligatorio y debe ser una cadena de texto con un máximo de 15 caracteres y solo letras y espacios.',
            'password.*' => 'La contraseña es obligatoria'
        ];

        // Valida los datos del formulario
        $validator = Validator::make($request->all(), $rules, $customMessages);

        // Si la validación falla, devuelve una respuesta JSON con los errores
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Código de respuesta 422 para errores de validación
        }

        $data = json_decode($request->getContent());
        $user = User::where('name', $data->name)->first();

        if ($user) {
            if (Hash::check($data->password, $user->password)) {
                $token = $user->createToken('token del usuario' . $user->id);

                $response = ['msg' => 'Se ha ingresado correctamente', 'token' => $token->plainTextToken];
                $status = 200;
            }
            else {
                $response = ['msg' => 'Credenciales incorrectas'];
                $status = 400;
            }
        }
        else {
            $response = ['msg' => 'Usuario no encontrado'];
            $status = 404;
        }

        return response()->json($response, $status);
    }

    public function logout(Request $request) {

        if (Auth::check()) {
            // Revoca todos los tokens del usuario autenticado
            $request->user()->tokens()->delete();
            
            return response()->json(['message' => 'Sesión cerrada']);
        } else {
            return response()->json(['message' => 'No hay sesión activa'], 401);
        }
    }
}
