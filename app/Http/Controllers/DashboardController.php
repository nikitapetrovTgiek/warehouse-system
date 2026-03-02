<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Главная панель после входа
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user(); // текущий пользователь
        // Передаём данные в шаблон
        return view('dashboard', compact('user'));
    }
}
