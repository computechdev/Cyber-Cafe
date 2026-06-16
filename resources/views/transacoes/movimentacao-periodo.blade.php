@extends('adminlte::page')

@section('title', 'Movimentações por Período')

@section('content_header')
    <h1>Movimentações por Período</h1>
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
    @endphp

    <div class="card">

        <div class="card-header">
            <strong>Movimentações por Período</strong>
        </div>

        <div class="card-body p-0">

            <div class="p-3 bg-light border-bottom">

                <form method="GET" action="{{ route('transacoes.movimentacao-periodo') }}" id="form-movimentacao">

                    <input type="hidden" name="pesquisar" value="1">

                    <div class="row align-items-center">

                        @if ($nivel === 1 || $nivel === 2)
                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            Cliente
                                        </span>
                                    </div>

                                    <select name="cliente" class="form-control">
                                        <option value="">Selecione</option>

                                        @foreach ($clientes as $cliente)
                                            <option
                                                value="{{ $cliente->id }}"
                                                {{ $clienteSelecionado == $cliente->id ? 'selected' : '' }}
                                            >
                                                {{ $cliente->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if ($nivel === 3)
                            <div class="col-md-3">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            Ponto
                                        </span>
                                    </div>

                                    <select name="ponto" class="form-control">
                                        <option value="">Selecione</option>

                                        @foreach ($pontos as $ponto)
                                            <option
                                                value="{{ $ponto->id }}"
                                                {{ $pontoSelecionado == $ponto->id ? 'selected' : '' }}
                                            >
                                                {{ $ponto->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        Data Movimentação
                                    </span>
                                </div>

                                <input
                                    type="date"
                                    name="data_inicial"
                                    class="form-control"
                                    
                                    placeholder="dd/mm/aaaa"
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
                                    
                                    placeholder="dd/mm/aaaa"
                                >
                            </div>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-info" id="btn-pesquisar">
                                Pesquisar
                            </button>
                        </div>

                    </div>

                </form>

            </div>

            <div class="p-4">

                @if ($pesquisou && $movimentacoes->count() > 0)

                    <table class="table table-borderless mb-0 movimentacao-resumo">
                        <thead>
                            <tr>
                                <th>Descrição da movimentação</th>
                                <th class="text-right">Entrada</th>
                                <th class="text-right">Saída</th>
                                <th class="text-right">Saldo</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($movimentacoes as $movimentacao)
                                <tr>
                                    <td>
                                        Tablet {{ $movimentacao->idprod }}
                                    </td>

                                    <td class="text-right">
                                        {{ $formatMoney($movimentacao->total_entrada) }}
                                    </td>

                                    <td class="text-right">
                                        {{ $formatMoney($movimentacao->total_saida) }}
                                    </td>

                                    <td class="text-right">
                                        {{ $formatMoney($movimentacao->saldo) }}
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="linha-separadora">
                                <td colspan="4"></td>
                            </tr>

                            <tr>
                                <td>
                                    <div class="barcode-legado">
                                        <span></span><span></span><span></span><span></span><span></span>
                                        <span></span><span></span><span></span><span></span><span></span>
                                        <span></span><span></span><span></span><span></span><span></span>
                                        <span></span><span></span><span></span><span></span><span></span>
                                    </div>
                                </td>

                                <td colspan="2" class="text-right">
                                    <strong>Total Entrada</strong>
                                </td>

                                <td class="text-right">
                                    {{ $formatMoney($totalEntrada) }}
                                </td>
                            </tr>

                            <tr>
                                <td></td>

                                <td colspan="2" class="text-right">
                                    <strong>Total Saída</strong>
                                </td>

                                <td class="text-right">
                                    {{ $formatMoney($totalSaida) }}
                                </td>
                            </tr>

                            <tr>
                                <td></td>

                                <td colspan="2" class="text-right">
                                    <strong>Saldo Total</strong>
                                </td>

                                <td class="text-right">
                                    {{ $formatMoney($saldoTotal) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                @elseif ($pesquisou)

                    <div class="alert alert-warning mb-0">
                        Nenhuma movimentação foi encontrada!
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
        .movimentacao-resumo th {
            font-weight: bold;
            color: #000;
            border-bottom: 1px solid #ddd;
        }

        .movimentacao-resumo td {
            border-top: 1px solid #ddd;
            color: #000;
        }

        .movimentacao-resumo .linha-separadora td {
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
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('form-movimentacao');
            const btn = document.getElementById('btn-pesquisar');

            form.addEventListener('submit', function () {
                btn.innerText = 'Loading...';
                btn.disabled = true;
            });
        });
    </script>
@stop