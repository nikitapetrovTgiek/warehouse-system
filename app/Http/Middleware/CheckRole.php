<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request):  $next
     * @param  \Closure  $next
     * @param  string  $role  // роль которую проверяем (admin, manager, worker)
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
         // Проверка авторизован ли пользователь
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Сначала войдите в систему');
        }
        // Текущий пользователь
        $user = Auth::user();
        // Есть ли у пользователя нужная роль
        // Используем метод hasRole()
        if (!$user->hasRole($role)) {
            // Если роль не совпадает ошибка
            abort(403, 'У вас нет прав для доступа к этой странице');
        }
        // Если всё хорошо пропускаем запрос дальше
        return $next($request);
    }
}
