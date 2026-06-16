<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserLevel
{
    public function handle(Request $request, Closure $next, ...$levels)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->temNivel($levels)) {
            // abort(403, 'Você não tem permissão para acessar esta página.');
            return redirect()->route('cliente.painel-teste');

        }

        return $next($request);
    }
}