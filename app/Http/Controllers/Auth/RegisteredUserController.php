<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'tipo_identificacion' => ['required', 'string', 'in:CC,TI'],
            'numero_identificacion' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]+$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'telefono' => ['nullable', 'string', 'max:20'],
'carrera'  => ['required', 'string', 'max:100'],
'semestre' => ['required', 'integer', 'between:1,12'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'tipo_identificacion' => $request->tipo_identificacion,
            'numero_identificacion' => $request->numero_identificacion,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'telefono' => $request->telefono,
'carrera'  => $request->carrera,
'semestre' => $request->semestre,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
