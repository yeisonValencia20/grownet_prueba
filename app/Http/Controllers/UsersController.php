<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();

        return response()->json(['usuarios' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return response()->json(['msg' => 'Esta ruta no ha sido implementada, comuniquese con el administrador']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         // validar los datos del formulario
         $rules = [
            'name' => 'required|string|max:15|regex:/^[A-Za-z\s]+$/',
            'cedula' => 'required|integer|max:9999999999',
            'password' => 'required|max:6',
            'state' => 'boolean',
        ];

        // Define mensajes de error personalizados
        $customMessages = [
            'name.*' => 'El nombre es obligatorio y debe ser una cadena de texto con un máximo de 15 caracteres y solo letras y espacios.',
            'cedula.*' => 'La cédula es obligatoria y debe ser un número entero con un máximo de 10 dígitos.',
            'password.*' => 'La contraseña es obligatoria y debe tener un máximo de 6 caracteres.',
            'state.*' => 'El estado debe ser un valor booleano.',
        ];

        // Valida los datos del formulario
        $validator = Validator::make($request->all(), $rules, $customMessages);

        // Si la validación falla, devuelve una respuesta JSON con los errores
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Código de respuesta 422 para errores de validación
        }

        // validar que la cedula no esta registrada
        $cedula = $request->input('cedula');
        $existe = User::where('cedula', $cedula)->exists();
        if ($existe) {
            return response()->json(['msg' => 'La cedula ya esta registrada en la base de datos'], 422);
        }
        
        // crear el nuevo usuario
        $user = new User();
        $user->name = $request->input('name');
        $user->cedula = $cedula;
        $password = $request->input('password');
        $user->password = Hash::make($password);
        $user->state = $request->input('state', true);

        //guardar usuario en la base de datos
        $user->save();

        return response()->json(['msg' => 'El usuario se ha creado con exito', 'data' => $user]);
    }

    /**
     * Display the specified resource.
     */
    public function show($cedula)
    {
        $user = User::where('cedula', $cedula)->first();

        if (!$user) {
            return response()->json(['msg' => 'No se ha encontrado usuario con la cedula ' . $cedula]);
        }

        return response()->json(['msg' => 'Se ha encontrado el usuario', 'data' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $users)
    {
        return response()->json(['msg' => 'Esta ruta no ha sido implementada, comuniquese con el administrador']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $cedula)
    {
        // validar los datos del formulario
        $rules = [
            'name' => 'string|max:15|regex:/^[A-Za-z\s]+$/',
            'cedula' => 'integer|max:9999999999',
            'password' => 'max:6',
            'state' => 'boolean',
        ];

        // Define mensajes de error personalizados
        $customMessages = [
            'name.*' => 'El nombre debe ser una cadena de texto con un máximo de 15 caracteres y solo letras y espacios.',
            'cedula.*' => 'La cédula debe ser un número entero con un máximo de 10 dígitos.',
            'password.*' => 'La contraseña debe tener un máximo de 6 caracteres.',
            'state.*' => 'El estado debe ser un valor booleano.',
        ];

        // Valida los datos del formulario
        $validator = Validator::make($request->all(), $rules, $customMessages);

        // Si la validación falla, devuelve una respuesta JSON con los errores
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Código de respuesta 422 para errores de validación
        }

        $user = User::where('cedula', $cedula)->first();

        if (!$user) {
            return response()->json(['msg' => 'No se ha encontrado usuario con la cedula ' . $cedula], 404);
        }

        if ($request->has('name')) {
            $user->name = $request->input('name');
        }
    
        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
        }
    
        if ($request->has('state')) {
            $user->state = $request->input('state');
        }

        $user->save();

        return response()->json(['msg' => 'Se ha actualizado el usuario', 'data' => $user]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($cedula)
    {
        $user = User::where('cedula', $cedula)->first();

        if (!$user) {
            return response()->json(['msg' => 'No se ha encontrado usuario con la cedula ' . $cedula], 404);
        }

        $user->delete();

        return response()->json(['msg' => 'Se ha eliminado con exito el usuario']);
    }
}
