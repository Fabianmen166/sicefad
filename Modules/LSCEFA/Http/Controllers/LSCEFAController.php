<?php

namespace Modules\LSCEFA\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class LSCEFAController extends Controller
{
    public function __construct()
    {
        // Eliminado el middleware 'auth' para permitir acceso público a index
    }

    /**
     * Página principal del módulo
     * @return Renderable
     */
    public function index()
    {
        if (auth()->check()) {
            $user = auth()->user();
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
        }
        // Si no está autenticado o no tiene roles, muestra la vista por defecto
        return view('lscefa::index');
    }

    public function admin()
    {
        $user = Auth::user();
        $role = $user->roles()->where('slug', 'lscefa.admin')->first();
        
        if (!$role) {
            return redirect()->route('cefa.lscefa.index')->with('error', 'No tienes permiso de administrador.');
        }

        return view('lscefa::admin.dashboard', compact('user'));
    }

    public function config()
    {
        $user = Auth::user();
        $role = $user->roles()->where('slug', 'lscefa.admin')->first();
        
        if (!$role) {
            return redirect()->route('cefa.lscefa.index')->with('error', 'No tienes permiso de administrador.');
        }

        return view('lscefa::admin.config', compact('user'));
    }

    public function intern()
    {
        $user = Auth::user();
        $role = $user->roles()->where('slug', 'lscefa.intern')->first();
        
        if (!$role) {
            return redirect()->route('cefa.lscefa.index')->with('error', 'No tienes permiso de pasante.');
        }

        return view('lscefa::intern.dashboard', compact('user'));
    }

    public function tasks()
    {
        $user = Auth::user();
        $role = $user->roles()->where('slug', 'lscefa.intern')->first();
        
        if (!$role) {
            return redirect()->route('cefa.lscefa.index')->with('error', 'No tienes permiso de pasante.');
        }

        return view('lscefa::intern.tasks', compact('user'));
    }

    public function technical()
    {
        $user = Auth::user();
        $role = $user->roles()->where('slug', 'lscefa.technical')->first();
        
        if (!$role) {
            return redirect()->route('cefa.lscefa.index')->with('error', 'No tienes permiso de personal técnico.');
        }

        return view('lscefa::technical.dashboard', compact('user'));
    }

    public function samples()
    {
        $user = Auth::user();
        $role = $user->roles()->where('slug', 'lscefa.technical')->first();
        
        if (!$role) {
            return redirect()->route('cefa.lscefa.index')->with('error', 'No tienes permiso de personal técnico.');
        }

        return view('lscefa::technical.samples', compact('user'));
    }

    /**
     * Muestra el dashboard de gestión de calidad
     */
    public function qualityDashboard()
    {
        $user = Auth::user();
        $role = $user->roles()->whereIn('slug', ['lscefa.admin', 'lscefa.quality'])->first();
        
        if (!$role) {
            return redirect()->route('cefa.lscefa.index')->with('error', 'No tienes permiso de gestión de calidad.');
        }

        return view('lscefa::quality.dashboard', compact('user'));
    }

    /**
     * Muestra la gestión de estándares de calidad
     */
    public function qualityStandards()
    {
        $user = Auth::user();
        $role = $user->roles()->whereIn('slug', ['lscefa.admin', 'lscefa.quality'])->first();
        
        if (!$role) {
            return redirect()->route('cefa.lscefa.index')->with('error', 'No tienes permiso de gestión de calidad.');
        }

        return view('lscefa::quality.standards', compact('user'));
    }

    /**
     * Muestra la gestión de auditorías de calidad
     */
    public function qualityAudits()
    {
        $user = Auth::user();
        $role = $user->roles()->whereIn('slug', ['lscefa.admin', 'lscefa.quality'])->first();
        
        if (!$role) {
            return redirect()->route('cefa.lscefa.index')->with('error', 'No tienes permiso de gestión de calidad.');
        }

        return view('lscefa::quality.audits', compact('user'));
    }

    public function welcome()
    {
        $role = Auth::user()->role ?? 'invitado'; // Asegúrate de tener la columna "role" en tu tabla de usuarios
        return view('lscefa::welcome', compact('role'));
    }

    public function PanelPas()
    {
        return view('lscefa::intern.panelpas');
    }

    /**
     * Mostrar formulario de creación
     * @return Renderable
     */
    public function create()
    {
        return view('lscefa::create');
    }

    /**
     * Almacenar nuevo recurso
     * @param Request $request
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Mostrar recurso específico
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('lscefa::show');
    }

    /**
     * Mostrar formulario de edición
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('lscefa::edit');
    }

    /**
     * Actualizar recurso
     * @param Request $request
     * @param int $id
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Eliminar recurso
     * @param int $id
     */
    public function destroy($id)
    {
        //
    }
}
