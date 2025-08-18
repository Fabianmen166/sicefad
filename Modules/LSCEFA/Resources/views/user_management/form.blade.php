@if(isset($user))
    {{ Form::model($user, ['route' => ['lscefa.admin.users.update', $user->id], 'method' => 'PUT']) }}
@else
    {{ Form::open(['route' => 'lscefa.admin.users.store']) }}
@endif
@csrf

<div class="card-body">
    @if(isset($user))
        <div class="form-group">
            {{ Form::label('person_readonly', 'Persona asociada') }}
            <input type="text" class="form-control" value="{{ optional($user->person)->full_name }} ({{ optional($user->person)->document_number }})" readonly>
            {{-- Mantener person_id si fuera necesario en procesos posteriores --}}
            @if(isset($user->person_id))
                {{ Form::hidden('person_id', $user->person_id) }}
            @endif
        </div>
    @else
        <div class="form-group">
            {{ Form::label('person_id', 'Persona asociada') }}
            {{ Form::select('person_id', $people->pluck('full_name','id'), isset($user) ? $user->person_id : null, [
                'class' => 'form-control' . ($errors->has('person_id') ? ' is-invalid' : ''),
                'required' => 'required',
                'placeholder' => 'Seleccione una persona'
            ]) }}
            @error('person_id')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    @endif

    <div class="form-group">
        {{ Form::label('nickname', 'Nombre (nickname)') }}
        {{ Form::text('nickname', null, ['class' => 'form-control' . ($errors->has('nickname') ? ' is-invalid' : ''), 'required' => 'required']) }}
        @error('nickname')
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
        {{ Form::password('password', ['class' => 'form-control' . ($errors->has('password') ? ' is-invalid' : '')]) }}
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
        {{ Form::password('password_confirmation', ['class' => 'form-control']) }}
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
