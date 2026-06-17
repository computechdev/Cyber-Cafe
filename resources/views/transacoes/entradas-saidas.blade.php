@extends('adminlte::page')

@section('title', 'Entradas e Saídas')

@section('content_header')
    <h1>Entradas e Saídas</h1>
@stop

@section('content')

    @php
        $formatMoney = function ($valor) {
            $valor = (float) $valor;

            if (floor($valor) == $valor) {
                return 'R$ ' . number_format($valor, 0, ',', '.');
            }

            return 'R$ ' . number_format($valor, 2, ',', '.');
        };

        $tipoNome = function ($tipo) {
            if ((int) $tipo === 1) {
                return 'Entrada';
            }

            if ((int) $tipo === 2) {
                return 'Saída';
            }

            return 'Outros';
        };

        if ((string) $tipo === '1') {
            $tipoTotal = 'Entrada Total';
        } elseif ((string) $tipo === '2') {
            $tipoTotal = 'Saída Total';
        } else {
            $tipoTotal = 'Total';
        }
    @endphp

    <div class="card">

        <div class="card-header">
            <strong>Entradas e Saídas</strong>
        </div>

        <div class="card-body p-0">

            <div class="p-3 bg-light border-bottom">

                <form method="GET" action="{{ route('transacoes.entradas-saidas') }}" id="form-transacoes">

                    <input type="hidden" name="pesquisar" value="1">

                    <div class="row align-items-center">

                        <div class="col-md-3">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        Tablet
                                    </span>
                                </div>

                                <select name="idprod" class="form-control">
                                    <option value="">Todos</option>

                                    @foreach ($tablets as $tablet)
                                        <option
                                            value="{{ $tablet->idprod }}"
                                            {{ $idprod == $tablet->idprod ? 'selected' : '' }}
                                        >
                                            {{ $tablet->idprod }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        Data
                                    </span>
                                </div>

                                <input
                                    type="date"
                                    name="data_inicial"
                                    class="form-control"
                                    value="{{ $dataInicial }}"
                                >

                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        a
                                    </span>
                                </div>

                                <input
                                    type="date"
                                    name="data_final"
                                    class="form-control"
                                    value="{{ $dataFinal }}"
                                >
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        Tipo
                                    </span>
                                </div>

                                <select name="tipo" class="form-control">
                                    <option value="todos" {{ (string) $tipo === 'todos' ? 'selected' : '' }}>
                                        Todos
                                    </option>

                                    <option value="1" {{ (string) $tipo === '1' ? 'selected' : '' }}>
                                        Entrada
                                    </option>

                                    <option value="2" {{ (string) $tipo === '2' ? 'selected' : '' }}>
                                        Saída
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        Fatura
                                    </span>
                                </div>

                                <select name="status_fatura" class="form-control">
                                    <option value="todos" {{ ($statusFatura ?? 'todos') === 'todos' ? 'selected' : '' }}>
                                        Todas
                                    </option>

                                    <option value="pendente" {{ ($statusFatura ?? 'todos') === 'pendente' ? 'selected' : '' }}>
                                        Pendentes
                                    </option>

                                    <option value="fechada" {{ ($statusFatura ?? 'todos') === 'fechada' ? 'selected' : '' }}>
                                        Fechadas
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <button type="submit" class="btn btn-info btn-block mb-2" id="btn-pesquisar">
                                Pesquisar
                            </button>
                        </div>

                    </div>

                </form>

            </div>

            <div class="p-4">

                @if ($pesquisou && $transacoes->count() > 0)

                    <table class="table table-borderless mb-0 transacoes-resumo">
                        <thead>
                            <tr>
                                <th>Descrição da transação</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th class="text-right">Valor</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($transacoes as $transacao)
                                <tr>
                                    <td>
                                        Tablet {{ $transacao->idprod }}
                                    </td>

                                    <td>
                                        {{ $tipoNome($transacao->tipo) }}
                                    </td>

                                    <td>
                                        @if ($transacao->status_fatura === 'Fechada')
                                            <span class="badge badge-secondary">
                                                Fechada
                                            </span>
                                        @elseif ($transacao->status_fatura === 'Pendente')
                                            <span class="badge badge-warning">
                                                Pendente
                                            </span>
                                        @else
                                            <span class="badge badge-light">
                                                {{ $transacao->status_fatura }}
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $transacao->data_hora ? \Carbon\Carbon::parse($transacao->data_hora)->format('d/m/Y H:i:s') : '-' }}
                                    </td>

                                    <td class="text-right">
                                        {{ $formatMoney($transacao->valor) }}
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="linha-separadora">
                                <td colspan="5"></td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <div class="barcode-legado">
                                        <span></span><span></span><span></span><span></span><span></span>
                                        <span></span><span></span><span></span><span></span><span></span>
                                        <span></span><span></span><span></span><span></span><span></span>
                                        <span></span><span></span><span></span><span></span><span></span>
                                    </div>
                                </td>

                                <td colspan="2" class="text-right">
                                    <strong>{{ $tipoTotal }}</strong>
                                </td>

                                <td class="text-right">
                                    {{ $formatMoney($total) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                @elseif ($pesquisou)

                    <div class="alert alert-warning mb-0">
                        Nenhuma transação foi encontrada!
                    </div>

                @else

                    <div class="alert alert-info mb-0">
                        Selecione os filtros e clique em pesquisar.
                    </div>

                @endif

            </div>

        </div>

    </div>

@stop

@section('css')
    <style>
        .transacoes-resumo th {
            font-weight: bold;
            color: #000;
            border-bottom: 1px solid #ddd;
        }

        .transacoes-resumo td {
            border-top: 1px solid #ddd;
            color: #000;
        }

        .transacoes-resumo .linha-separadora td {
            border-top: 4px solid #222;
            padding: 0;
            height: 0;
        }

        .barcode-legado {
            display: flex;
            align-items: flex-end;
            height: 50px;
            width: 80px;
            gap: 2px;
            margin-top: 10px;
        }

        .barcode-legado span {
            display: block;
            width: 2px;
            background: #00aeef;
            height: 48px;
        }

        .barcode-legado span:nth-child(2n) {
            height: 45px;
            width: 1px;
        }

        .barcode-legado span:nth-child(3n) {
            width: 3px;
        }

        @media (max-width: 767.98px) {
            .input-group {
                margin-bottom: 8px;
            }

            .btn-block {
                width: 100%;
            }
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('form-transacoes');
            const btn = document.getElementById('btn-pesquisar');

            form.addEventListener('submit', function () {
                btn.innerText = 'Loading...';
                btn.disabled = true;
            });
        });
    </script>
@stop