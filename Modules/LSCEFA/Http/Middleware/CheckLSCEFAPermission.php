<?php

namespace Modules\LSCEFA\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckLSCEFAPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Si el usuario tiene el rol de admin, permitir acceso a todo
        if ($user->roles()->where('slug', 'lscefa.admin')->exists()) {
            return $next($request);
        }

        // Verificar si el usuario tiene el permiso específico
        if ($user->havePermission($permission)) {
            return $next($request);
        }

        // Si no tiene permisos, redirigir con mensaje de error
        return redirect()->route('cefa.lscefa.index')
            ->with('error', 'No tienes los permisos necesarios para acceder a esta sección. Por favor, contacta al administrador si crees que esto es un error.');
    }
} 