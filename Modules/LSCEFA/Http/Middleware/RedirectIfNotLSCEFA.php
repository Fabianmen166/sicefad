<?php

namespace Modules\LSCEFA\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotLSCEFA
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $roles = $user->roles()->pluck('slug')->toArray();

        if (in_array('lscefa.admin', $roles)) {
            return redirect()->route('lscefa.admin.welcome');
        }
        if (in_array('lscefa.quality', $roles)) {
            return redirect()->route('lscefa.quality.dashboard');
        }
        if (in_array('lscefa.intern', $roles)) {
            return redirect()->route('lscefa.intern.panelpas');
        }
        if (in_array('lscefa.technical', $roles)) {
            return redirect()->route('lscefa.technical.panel');
        }

        return $next($request);
    }
} 