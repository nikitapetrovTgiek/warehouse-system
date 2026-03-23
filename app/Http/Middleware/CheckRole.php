<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request):  $next
     * @param  \Closure  $next
     * @param  string  $roles  // роли которые проверяем (admin, manager, worker)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $roles): Response
    {
        // проверка авторизации
        if (!Auth::check()) {
            return redirect('/login')->with('error', 'Сначала войдите в систему');
        }

        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return $next($request);
        }
        $roles = array_map('trim', (array) $roles);

        // иерархия ролей
        $hierarchy = [
            'worker' => ['admin', 'manager', 'worker'],
            'manager' => ['manager', 'admin'],
            'admin' => ['admin'], 
        ];

        // все допустимые роли для текущего маршрута
        $allowedRoles = [];
        foreach ($roles as $role) {
            if (isset($hierarchy[$role])) {
                $allowedRoles = array_merge($allowedRoles, $hierarchy[$role]);
            } else {
                $allowedRoles[] = $role;
            }
        }
        $allowedRoles = array_unique($allowedRoles);

        // проверка есть ли хоть одна из нужных ролей
        foreach ($allowedRoles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }
        // Если ничего не подошло
        abort(403, 'У вас нет прав для доступа к этой странице');
        }
}
