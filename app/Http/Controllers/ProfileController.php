<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    public function show()
    {
        $user = Auth::user();

        // Crear perfil si no existe
        if (!$user->profile) {
            $user->profile()->create([
                'cargo' => null,
                'departamento' => null,
                'id_universitario' => null,
                'telefono' => null,
                'bio' => null,
            ]);
        }

        return view('profile.show', compact('user'));
    }

    /**
     * Show the form for editing the user's profile.
     */
    public function edit()
    {
        $user = Auth::user();

        // Crear perfil si no existe
        if (!$user->profile) {
            $user->profile()->create([
                'cargo' => null,
                'departamento' => null,
                'id_universitario' => null,
                'telefono' => null,
                'bio' => null,
            ]);
        }

        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Validar datos del usuario
        $validatedUser = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        // Validar datos del perfil
        $validatedProfile = $request->validate([
            'cargo' => ['nullable', 'string', 'max:255'],
            'departamento' => ['nullable', 'string', 'max:255'],
            'id_universitario' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        // Actualizar usuario
        $user->update($validatedUser);

        // Actualizar o crear perfil
        if ($user->profile) {
            $user->profile->update($validatedProfile);
        } else {
            $user->profile()->create($validatedProfile);
        }

        return redirect()->route('profile.show')->with('success', 'Perfil actualizado exitosamente');
    }
}
