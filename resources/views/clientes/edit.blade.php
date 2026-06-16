@extends('adminlte::page')

@section('title', 'Editar Cliente')

@section('content_header')
    <h1>Editar Cliente</h1>
@stop

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Ops!</strong> Corrija os campos abaixo.

            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('clientes.update', $cliente->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Editar Cliente</h3>
            </div>

            <div class="card-body">

                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            placeholder="Nome"
                            value="{{ old('name', $cliente->name) }}"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="far fa-envelope"></i>
                            </span>
                        </div>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="E-mail xxxx@xxxxxx.xxx"
                            value="{{ old('email', $cliente->email) }}"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                %
                            </span>
                        </div>

                        <input
                            type="number"
                            step="0.01"
                            name="porcentagem"
                            class="form-control"
                            placeholder="Porcentagem de Locação"
                            value="{{ old('porcentagem', $cliente->porcentagem) }}"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-globe-americas"></i>
                            </span>
                        </div>

                        <select name="id_pais" class="form-control" required>
                            <option value="">Selecione o País</option>

                            @foreach ($paises as $id => $pais)
                                <option value="{{ $id }}" {{ old('id_pais', $cliente->id_pais) == $id ? 'selected' : '' }}>
                                    {{ $pais }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>

                        <input
                            type="text"
                            name="username"
                            class="form-control"
                            placeholder="Login"
                            value="{{ old('username', $cliente->username) }}"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <small class="text-muted">
                        Deixe a senha em branco para manter a senha atual.
                    </small>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Nova Senha"
                        >
                    </div>
                </div>

                <div class="form-group mb-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>

                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Confirmar Nova Senha"
                        >
                    </div>
                </div>

            </div>

            <div class="card-footer text-right">
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-info">
                    Salvar
                </button>
            </div>
        </div>

    </form>

@stop

@section('js')
    @include('partials.sweetalert')
@stop