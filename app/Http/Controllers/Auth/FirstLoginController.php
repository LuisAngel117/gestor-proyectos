<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class FirstLoginController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();

        if (!$user->profile) {
            $user->profile()->create([
                'cargo' => null,
                'departamento' => null,
                'id_universitario' => null,
                'telefono' => null,
                'bio' => null,
            ]);
        }

        $telefonoLocal = $this->formatTelefonoLocal(optional($user->profile)->telefono);

        return view('profile.first-login', [
            'user' => $user,
            'telefonoLocal' => $telefonoLocal,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validatedUser = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ]);

        $validatedProfile = $request->validate([
            'cargo' => ['required', 'string', 'max:255'],
            'departamento' => ['required', 'string', 'max:255'],
            'id_universitario' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'regex:/^0\\d{9}$/'],
            'bio' => ['nullable', 'string', 'max:1000'],
        ], [
            'telefono.regex' => 'El telefono debe tener 10 digitos y empezar con 0.',
        ]);

        $validatedPassword = $request->validate([
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->update(array_merge($validatedUser, [
            'password' => Hash::make($validatedPassword['password']),
            'must_change_password' => false,
            'profile_completed_at' => now(),
        ]));

        $validatedProfile['telefono'] = $this->normalizeTelefono($validatedProfile['telefono']);

        if ($user->profile) {
            $user->profile->update($validatedProfile);
        } else {
            $user->profile()->create($validatedProfile);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Perfil completado. Bienvenido/a.');
    }

    private function normalizeTelefono(?string $telefono): ?string
    {
        if ($telefono === null) {
            return null;
        }

        $digits = preg_replace('/\\D+/', '', $telefono);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            $digits = '593' . substr($digits, 1);
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '593')) {
            return '+' . $digits;
        }

        return '+' . $digits;
    }

    private function formatTelefonoLocal(?string $telefono): ?string
    {
        if (!$telefono) {
            return null;
        }

        $digits = preg_replace('/\\D+/', '', $telefono);

        if (str_starts_with($digits, '593') && strlen($digits) === 12) {
            return '0' . substr($digits, 3);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            return $digits;
        }

        return $telefono;
    }
}
