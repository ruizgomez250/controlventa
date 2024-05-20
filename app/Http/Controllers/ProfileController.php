<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if ($request->has('name')) {
            $user->name = $request->input('name');
            $user->save();
            // Lógica para actualizar el perfil del usuario
            return redirect()->route('profile.show')->with('success', 'Perfil actualizado exitosamente');
        } else {
            $request->validate([
                'contrasena_actual' => 'required',
                'nueva_contrasena' => 'required|min:8', // Agrega cualquier otra regla de validación según tus necesidades
                'repetir_contrasena' => 'required|same:nueva_contrasena',
            ]);
            if (strlen($request->input('contrasena_actual')) >= 8 && Hash::check($request->input('contrasena_actual'), $user->password)) {
                // La contraseña actual es correcta y cumple con la longitud mínima
                $user->password = Hash::make($request->input('nueva_contrasena'));
                $user->save();
                return redirect()->route('profile.edit')->with('success', 'Contraseña actualizada exitosamente');
            } elseif (strlen($request->input('contrasena_actual')) < 8) {
                // La contraseña actual no cumple con la longitud mínima
                return redirect()->back()->with('error', 'La contraseña actual debe tener al menos 8 caracteres');
            } else {
                // La contraseña actual no coincide, muestra un mensaje de error
                return redirect()->back()->with('error', 'La contraseña actual es incorrecta');
            }
        }
    }
}
