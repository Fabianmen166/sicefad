<?php

namespace Modules\LSCEFA\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\SICA\Entities\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    /**
     * Muestra un listado de usuarios.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $user = Auth::user();
            \Log::info('=== INICIO DEPURACIÓN ===');
            \Log::info('Usuario ID: ' . $user->id);
            \Log::info('Email: ' . $user->email);
            \Log::info('Roles: ' . json_encode($user->roles->pluck('slug')->toArray()));
            
            // Verificar si el usuario tiene el rol de administrador
            $hasRole = $user->hasRole('lscefa.admin');
            \Log::info('¿Tiene rol lscefa.admin?: ' . ($hasRole ? 'Sí' : 'No'));
            
            if (!$hasRole) {
                \Log::error('Usuario no tiene el rol requerido para acceder a esta ruta');
                abort(403, 'No tienes permiso para acceder a esta sección. Rol requerido: lscefa.admin');
            }
            
            $users = User::whereHas('roles', function($query) {
                $query->where('slug', 'like', 'lscefa.%');
            })->with('roles')->get();

            return view('lscefa::user_management.index', compact('users'));
            
        } catch (\Exception $e) {
            \Log::error('Error en UserManagementController@index: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            abort(500, 'Error interno del servidor. Por favor revise los logs para más detalles.');
        }
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('lscefa.admin.users.create');
        
        $roles = Role::where('slug', 'like', 'lscefa.%')->get();
        return view('lscefa::user_management.create', compact('roles'));
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('lscefa.admin.users.create');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,id'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        $role = Role::findOrFail($validated['role']);
        $user->roles()->sync([$role->id]);

        return redirect()->route('lscefa.admin.users.index')
            ->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->authorize('lscefa.admin.users.edit');
        
        $user = User::findOrFail($id);
        $roles = Role::where('slug', 'like', 'lscefa.%')->get();
        $userRole = $user->roles->first();
        
        return view('lscefa::user_management.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Actualiza el usuario especificado en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('lscefa.admin.users.edit');
        
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,id'
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        
        $user->save();

        // Actualizar rol
        $newRole = Role::findOrFail($validated['role']);
        $user->roles()->sync([$newRole->id]);

        return redirect()->route('lscefa.admin.users.index')
            ->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina el usuario especificado de la base de datos.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('lscefa.admin.users.destroy');
        
        $user = User::findOrFail($id);
        
        // No permitir eliminar el propio usuario
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }
        
        $user->delete();
        
        return redirect()->route('lscefa.admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }
}
