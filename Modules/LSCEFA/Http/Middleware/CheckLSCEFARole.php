<?php

namespace Modules\LSCEFA\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckLSCEFARole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRoles = $user->roles()->pluck('slug')->toArray();

        // Si el usuario tiene el rol de admin, permitir acceso a todo
        if (in_array('lscefa.admin', $userRoles)) {
            return $next($request);
        }

        // Verificar si el usuario tiene alguno de los roles requeridos
        foreach ($roles as $role) {
            if (in_array($role, $userRoles)) {
                return $next($request);
            }
        }

        // Si no tiene permisos, redirigir con mensaje de error
        return redirect()->route('cefa.lscefa.index')
            ->with('error', 'No tienes los permisos necesarios para acceder a esta sección. Por favor, contacta al administrador si crees que esto es un error.');
    }
} 