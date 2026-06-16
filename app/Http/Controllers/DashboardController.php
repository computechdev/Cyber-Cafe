<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalUsuarios = User::count();

        $totalPontos = DB::table('ponto')->count();

        $totalTablets = DB::table('tablet')->count();

        $totalLeituras = DB::table('leitura')->count();

        return view('dashboard.index', compact(
            'totalUsuarios',
            'totalPontos',
            'totalTablets',
            'totalLeituras'
        ));
    }
}