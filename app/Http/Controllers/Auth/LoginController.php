<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Informe seu usuário ou e-mail.',
            'password.required' => 'Informe sua senha.',
        ]);

        $login = $request->input('login');
        $senha = $request->input('password');

        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (!$user) {
            return back()
                ->withInput()
                ->withErrors([
                    'login' => 'Usuário não encontrado.',
                ]);
        }

        if ((int) $user->status !== 1) {
            return back()
                ->withInput()
                ->withErrors([
                    'login' => 'Usuário bloqueado ou inativo.',
                ]);
        }

        if ($user->password && Hash::check($senha, $user->password)) {
            Auth::login($user);

            $request->session()->regenerate();

            return $this->redirecionarDepoisLogin($user);
        }

        if ($user->legacy_passwd && md5($senha) === $user->legacy_passwd) {
            $user->password = Hash::make($senha);
            $user->legacy_passwd = null;
            $user->save();

            Auth::login($user);

            $request->session()->regenerate();

            return $this->redirecionarDepoisLogin($user);
        }

        return back()
            ->withInput()
            ->withErrors([
                'password' => 'Senha incorreta.',
            ]);
    }

    private function redirecionarDepoisLogin($user)
    {
        if ((int) $user->nivel === 3) {
            return redirect()->route('cliente.painel');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}