<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, $request->filled('remember'))) {
             $request->session()->regenerate();
             $user = Auth::user();

             // Sincronizar el guard 'admin' de Filament si el usuario tiene acceso
             // Esto evita que al ir al admin después de loguearse en el POS le pida login de nuevo
             if ($user->canAccessPanel(filament()->getPanel('admin'))) {
                 Auth::guard('admin')->login($user, $request->filled('remember'));
             }

             // Set cookie for 30 days if remember is checked
             if ($request->filled('remember')) {
                 cookie()->queue(cookie('remember_email', $request->email, 43200));
             } else {
                 cookie()->queue(cookie()->forget('remember_email'));
             }

             // Redirigir a la ruta solicitada originalmente o al POS
             $intended = $request->session()->get('url.intended', route('principal.index'));
             
             // Si la ruta pretendida es parte del admin, pero el usuario entró por el POS, 
             // es mejor mandarlo al POS para evitar confusión, a menos que sea explícito.
             if (str_contains($intended, '/admin')) {
                 $intended = route('principal.index');
             }

             return redirect($intended);
         }

        return redirect()->back()
            ->withErrors(['email' => 'Las credenciales no coinciden con nuestros registros.'])
            ->withInput($request->except('password'));
    }

    public function logout(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
