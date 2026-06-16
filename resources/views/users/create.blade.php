@extends('adminlte::page')

@section('title', 'Novo Usuário')

@section('content_header')
    <h1>Novo Usuário</h1>
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

    <form action="{{ route('usuarios.store') }}" method="POST">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dados do usuário</h3>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nome</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name') }}"
                                required
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Usuário</label>
                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                value="{{ old('username') }}"
                                required
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>E-mail</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="{{ old('email') }}"
                            >
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Senha</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                required
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Confirmar senha</label>
                            <input
                                type="password"
                                name="password_confirmation"
                                class="form-control"
                                required
                            >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Nível</label>
                           <select name="nivel" id="nivel" class="form-control" required>
                                <option value="">Selecione</option>
                                <option value="1" {{ old('nivel') == 1 ? 'selected' : '' }}>Administrador</option>
                                <option value="2" {{ old('nivel') == 2 ? 'selected' : '' }}>Subadmin</option>
                                <option value="3" {{ old('nivel') == 3 ? 'selected' : '' }}>Cliente</option>
                                <option value="4" {{ old('nivel') == 4 ? 'selected' : '' }}>Sócio</option>
                                <option value="5" {{ old('nivel') == 5 ? 'selected' : '' }}>Funcionário</option>
                                <option value="6" {{ old('nivel') == 6 ? 'selected' : '' }}>Operador</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="card" id="card-dados-legados">
            <div class="card-header">
                <h3 class="card-title">Dados legados / administrativos</h3>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-3 campo-legado campo-apoio">
                        <div class="form-group">
                            <label>ID Apoio</label>
                            <input
                                type="number"
                                name="id_apoio"
                                class="form-control"
                                value="{{ old('id_apoio') }}"
                            >
                        </div>
                    </div>

                    <div class="col-md-3 campo-legado campo-porcentagem">
                        <div class="form-group">
                            <label>Porcentagem</label>
                            <input
                                type="number"
                                step="0.01"
                                name="porcentagem"
                                class="form-control"
                                value="{{ old('porcentagem') }}"
                            >
                        </div>
                    </div>

                   <div class="col-md-3 campo-legado campo-pais">
                        <div class="form-group">
                            <label>ID País</label>
                            <input
                                type="number"
                                name="id_pais"
                                class="form-control"
                                value="{{ old('id_pais') }}"
                            >
                        </div>
                    </div>

                    <div class="col-md-3 campo-legado campo-validade">
                        <div class="form-group">
                            <label>Validade</label>
                            <input
                                type="date"
                                name="validade"
                                class="form-control"
                                value="{{ old('validade') }}"
                            >
                        </div>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-3 campo-legado campo-afiliado">
                        <div class="form-group">
                            <label>Afiliado</label>
                            <input
                                type="number"
                                name="afiliado"
                                class="form-control"
                                value="{{ old('afiliado') }}"
                            >
                        </div>
                    </div>

                    <div class="col-md-3 campo-legado campo-fechar-faturas">
                        <div class="form-group">
                            <label>Fecha faturas por ponto?</label>
                            <select name="fechar_faturas_ponto" class="form-control">
                                <option value="0" {{ old('fechar_faturas_ponto', 0) == 0 ? 'selected' : '' }}>Não</option>
                                <option value="1" {{ old('fechar_faturas_ponto') == 1 ? 'selected' : '' }}>Sim</option>
                            </select>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <div class="mb-4">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Salvar
            </button>

            <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                Voltar
            </a>
        </div>

    </form>

@stop

@section('js')
    @include('partials.sweetalert')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectNivel = document.getElementById('nivel');
            const cardDadosLegados = document.getElementById('card-dados-legados');

            const camposLegados = document.querySelectorAll('.campo-legado');

            function esconderTodosCamposLegados() {
                camposLegados.forEach(function (campo) {
                    campo.style.display = 'none';
                });
            }

            function mostrarCampo(classe) {
                const campos = document.querySelectorAll(classe);

                campos.forEach(function (campo) {
                    campo.style.display = 'block';
                });
            }

            function atualizarCamposPorNivel() {
                const nivel = parseInt(selectNivel.value || 0);

                esconderTodosCamposLegados();

                if (!nivel || nivel === 1) {
                    cardDadosLegados.style.display = 'none';
                    return;
                }

                cardDadosLegados.style.display = 'block';

                if (nivel === 2) {
                    mostrarCampo('.campo-apoio');
                    mostrarCampo('.campo-pais');
                    mostrarCampo('.campo-validade');
                }

                if (nivel === 3) {
                    mostrarCampo('.campo-apoio');
                    mostrarCampo('.campo-porcentagem');
                    mostrarCampo('.campo-pais');
                    mostrarCampo('.campo-validade');
                    mostrarCampo('.campo-afiliado');
                    mostrarCampo('.campo-fechar-faturas');
                }

                if (nivel === 4) {
                    mostrarCampo('.campo-apoio');
                    mostrarCampo('.campo-porcentagem');
                    mostrarCampo('.campo-afiliado');
                }

                if (nivel === 5) {
                    mostrarCampo('.campo-apoio');
                    mostrarCampo('.campo-pais');
                }

                if (nivel === 6) {
                    mostrarCampo('.campo-apoio');
                    mostrarCampo('.campo-afiliado');
                }
            }

            selectNivel.addEventListener('change', atualizarCamposPorNivel);

            atualizarCamposPorNivel();
        });
    </script>
@stop