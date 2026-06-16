@extends('adminlte::page')

@section('title', 'Enviar Créditos')

@section('content_header')
    <h1>Enviar Créditos</h1>
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

    <form action="{{ route('tablets.creditos.store', $tablet->id) }}" method="POST" id="form-creditos">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Créditos do Tablet</h3>
            </div>

            <div class="card-body">

                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-tablet-alt"></i>
                            </span>
                        </div>

                        <input
                            type="text"
                            class="form-control"
                            value="{{ $tablet->idprod }} / {{ $tablet->cliente_nome ?? $tablet->cliente }} / {{ $tablet->ponto_nome ?? '-' }}"
                            readonly
                        >
                    </div>
                </div>

                <input type="hidden" name="valor" id="valor_credito" value="{{ old('valor') }}">

                <div class="bg-light p-3 rounded">

                    <div class="row mb-3">
                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-dark btn-block btn-credito" data-valor="20">20</button>
                        </div>

                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-dark btn-block btn-credito" data-valor="50">50</button>
                        </div>

                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-dark btn-block btn-credito" data-valor="100">100</button>
                        </div>

                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-dark btn-block btn-credito" data-valor="2">2</button>
                        </div>

                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-dark btn-block btn-credito" data-valor="5">5</button>
                        </div>

                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-dark btn-block btn-credito" data-valor="10">10</button>
                        </div>

                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-dark btn-block btn-credito" data-valor="1">1</button>
                        </div>

                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-secondary btn-block" id="btn-limpar">
                                Limpar
                            </button>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label>Valor selecionado</label>
                        <input
                            type="text"
                            id="valor_visual"
                            class="form-control"
                            value="{{ old('valor') }}"
                            readonly
                        >
                    </div>

                </div>

            </div>

            <div class="card-footer text-right">
                <a href="{{ route('tablets.index') }}" class="btn btn-secondary">
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputValor = document.getElementById('valor_credito');
            const inputVisual = document.getElementById('valor_visual');
            const botoesCredito = document.querySelectorAll('.btn-credito');
            const btnLimpar = document.getElementById('btn-limpar');
            const form = document.getElementById('form-creditos');

            botoesCredito.forEach(function (botao) {
                botao.addEventListener('click', function () {
                    const valor = botao.dataset.valor;

                    inputValor.value = valor;
                    inputVisual.value = valor;
                });
            });

            btnLimpar.addEventListener('click', function () {
                inputValor.value = '';
                inputVisual.value = '';
            });

            form.addEventListener('submit', function (event) {
                if (!inputValor.value) {
                    event.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Selecione um valor',
                        text: 'Escolha um valor de crédito antes de salvar.',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    </script>
@stop