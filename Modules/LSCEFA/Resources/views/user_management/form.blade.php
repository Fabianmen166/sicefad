@if(isset($user))
    {{ Form::model($user, ['route' => ['lscefa.admin.users.update', $user->id], 'method' => 'PUT']) }}
@else
    {{ Form::open(['route' => 'lscefa.admin.users.store']) }}
@endif

<div class="card-body">
    <div class="form-group">
        {{ Form::label('name', 'Nombre Completo') }}
        {{ Form::text('name', null, ['class' => 'form-control' . ($errors->has('name') ? ' is-invalid' : ''), 'required' => 'required']) }}
        @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        {{ Form::label('email', 'Correo Electrónico') }}
        {{ Form::email('email', null, ['class' => 'form-control' . ($errors->has('email') ? ' is-invalid' : ''), 'required' => 'required']) }}
        @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>

    <div class="form-group">
        {{ Form::label('password', 'Contraseña') }}
        {{ Form::password('password', ['class' => 'form-control' . ($errors->has('password') ? ' is-invalid' : ''), isset($user) ? null : 'required']) }}
        @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
        @if(isset($user))
            <small class="form-text text-muted">Dejar en blanco para mantener la contraseña actual</small>
        @endif
    </div>

    <div class="form-group">
        {{ Form::label('password_confirmation', 'Confirmar Contraseña') }}
        {{ Form::password('password_confirmation', ['class' => 'form-control', isset($user) ? null : 'required']) }}
    </div>

    <div class="form-group">
        {{ Form::label('role', 'Rol') }}
        {{ Form::select('role', $roles->pluck('name', 'id'), $userRole->id ?? null, ['class' => 'form-control' . ($errors->has('role') ? ' is-invalid' : ''), 'required' => 'required', 'placeholder' => 'Seleccione un rol']) }}
        @error('role')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>

<div class="card-footer">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> {{ isset($user) ? 'Actualizar' : 'Guardar' }}
    </button>
    <a href="{{ route('lscefa.admin.users.index') }}" class="btn btn-default">
        <i class="fas fa-arrow-left"></i> Volver
    </a>
</div>

{{ Form::close() }}
