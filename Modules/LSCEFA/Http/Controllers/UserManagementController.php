<?php

namespace Modules\LSCEFA\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\SICA\Entities\Role;
use Modules\SICA\Entities\Person;
use Modules\SICA\Entities\EPS;
use Modules\SICA\Entities\PopulationGroup;
use Modules\SICA\Entities\PensionEntity;
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
            
            // Verificar si el usuario tiene el rol de administrador (sin usar helpers inexistentes)
            $hasRole = $user->roles()->where('slug', 'lscefa.admin')->exists();
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
        $this->ensureAdmin();
        
        $roles = Role::where('slug', 'like', 'lscefa.%')->get();
        return view('lscefa::user_management.create', compact('roles'));
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     * Crea primero la Persona con los datos del formulario y la asigna al nuevo Usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->ensureAdmin();
        
        $validated = $request->validate([
            // Datos de Persona
            'document_type' => 'required|string|in:Cédula de Ciudadanía,Tarjeta de Identidad,Cédula de Extranjería,Pasaporte,Documento Nacional de Identidad,Registro Civil,Número de Identificación Tributaria',
            'document_number' => 'required|string|max:50|unique:people,document_number',
            'first_name' => 'required|string|max:120',
            'first_last_name' => 'required|string|max:120',
            'second_last_name' => 'nullable|string|max:120',
            // Datos de Usuario
            'nickname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,id'
        ]);

        // Valores por defecto requeridos por la tabla people
        $population_group = PopulationGroup::firstOrCreate(['name' => 'NINGUNA']);
        $eps = EPS::firstOrCreate(['name' => 'NO REGISTRA']);
        $pension_entity = PensionEntity::firstOrCreate(['name' => 'NO REGISTRA']);

        // Crear Persona asociada
        $person = Person::create([
            'document_type' => $validated['document_type'],
            'document_number' => $validated['document_number'],
            'first_name' => $validated['first_name'],
            'first_last_name' => $validated['first_last_name'],
            'second_last_name' => $validated['second_last_name'] ?? null,
            'eps_id' => $eps->id,
            'population_group_id' => $population_group->id,
            'pension_entity_id' => $pension_entity->id,
        ]);

        // Crear Usuario asociado a la Persona
        $user = User::create([
            'person_id' => $person->id,
            'nickname' => $validated['nickname'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ]);

        // Asignar rol
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
        $this->ensureAdmin();
        
        $user = User::findOrFail($id);
        $roles = Role::where('slug', 'like', 'lscefa.%')->get();
        $userRole = $user->roles->first();
        $people = Person::orderBy('first_name')->orderBy('first_last_name')->get();
        
        return view('lscefa::user_management.edit', compact('user', 'roles', 'userRole', 'people'));
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
        $this->ensureAdmin();
        
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'nickname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,id'
        ]);

        $user->nickname = $validated['nickname'];
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
        $this->ensureAdmin();
        
        $user = User::findOrFail($id);
        
        // No permitir eliminar el propio usuario
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }
        
        $user->forceDelete();
        
        return redirect()->route('lscefa.admin.users.index')
            ->with('success', 'Usuario eliminado exitosamente.');
    }

    private function ensureAdmin(): void
    {
        $user = auth()->user();
        if (!$user || !$user->roles()->where('slug', 'lscefa.admin')->exists()) {
            abort(403, 'No tienes permisos para administrar usuarios.');
        }
    }
}
