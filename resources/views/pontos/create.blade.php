@extends('adminlte::page')

@section('title', 'Novo Ponto')

@section('content_header')
    <h1>Novo Ponto</h1>
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

    <form action="{{ route('pontos.store') }}" method="POST">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Novo Ponto</h3>
            </div>

            <div class="card-body">

                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-user-plus"></i>
                            </span>
                        </div>

                        <select name="id_apoio" class="form-control" required>
                            <option value="">Selecione um cliente</option>

                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('id_apoio') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->name }}
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

                        <input type="text" name="nome" class="form-control" placeholder="Nome"
                            value="{{ old('nome') }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>

                        <input type="password" name="passwd" maxlength="10" class="form-control"
                            placeholder="Senha para acesso ao Kiosk pela web" required>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                %
                            </span>
                        </div>

                        <input type="number" step="1" name="porcent_ponto" class="form-control"
                            placeholder="Porcentagem do Ponto" value="{{ old('porcent_ponto') }}" required>
                    </div>
                </div>

            </div>

            <div class="card-footer text-right">
                <a href="{{ route('pontos.index') }}" class="btn btn-secondary">
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
