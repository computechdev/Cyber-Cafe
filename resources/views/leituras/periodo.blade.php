@extends('adminlte::page')

@section('title', 'Faturas por Período')

@section('content_header')
    <h1>Faturas por Período</h1>
@stop

@section('content')

    @php
        $formatMoney = function ($valor) {
            $valor = (float) $valor;

            if (floor($valor) == $valor) {
                return number_format($valor, 0, ',', '.');
            }

            return number_format($valor, 2, ',', '.');
        };
    @endphp

    <div class="card">

        <div class="card-header">
            <strong>Faturas por Período</strong>
        </div>

        <div class="card-body p-0">

            <div class="p-3 bg-light border-bottom">

                <form method="GET" action="{{ route('leituras.periodo') }}" id="form-periodo-leituras">

                    <input type="hidden" name="pesquisar" value="1">

                    <div class="row align-items-center">

                        @if (in_array($nivel, [1, 2]))
                            <div class="col-md-4">
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            Cliente
                                        </span>
                                    </div>

                                    <select name="cliente" class="form-control">
                                        <option value="">Selecione...</option>

                                        @foreach ($clientes as $cliente)
                                            <option value="{{ $cliente->id }}"
                                                {{ old('cliente', $clienteSelecionado) == $cliente->id ? 'selected' : '' }}>
                                                {{ $cliente->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        @if ($nivel === 3)
                            <div class="col-md-4">
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            Ponto
                                        </span>
                                    </div>

                                    <select name="ponto" class="form-control">
                                        <option value="">Todos</option>

                                        @foreach ($pontos as $ponto)
                                            <option value="{{ $ponto->id }}"
                                                {{ old('ponto', $pontoSelecionado) == $ponto->id ? 'selected' : '' }}>
                                                {{ $ponto->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif

                        <div class="col-md-5">
                            <div class="input-group mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        Data Leitura
                                    </span>
                                </div>

                                <input
                                    type="date"
                                    name="data_inicial"
                                    class="form-control"
                                    value="{{ old('data_inicial', $dataInicial) }}"
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
                                    value="{{ old('data_final', $dataFinal) }}"
                                >
                            </div>
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-info btn-block mb-2" id="btn-pesquisar">
                                Pesquisar
                            </button>
                        </div>

                    </div>

                </form>

            </div>

            <div class="p-4">

                @if ($pesquisou && $leituras->count() > 0)

                    <table class="table table-borderless mb-0 leitura-resumo">
                        <thead>
                            <tr>
                                <th>Descrição do acerto</th>
                                <th>Status</th>
                                <th class="text-right">Entrada</th>
                                <th class="text-right">Saída</th>
                                <th class="text-right">Saldo</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($leituras as $leitura)
                                <tr>
                                    <td>
                                        Tablet {{ $leitura->idprod }}
                                        -
                                        Leitura de
                                        {{ $leitura->dataorder ? \Carbon\Carbon::parse($leitura->dataorder)->format('d/m/Y') : '-' }}

                                        @if (!empty($leitura->ponto_nome))
                                            <br>
                                            <small>Ponto: {{ $leitura->ponto_nome }}</small>
                                        @endif
                                    </td>

                                    <td>
                                        @if ((int) $leitura->status_leitura === 1)
                                            <span class="badge badge-warning">
                                                Aberta
                                            </span>
                                        @elseif ((int) $leitura->status_leitura === 2)
                                            <span class="badge badge-secondary">
                                                Fechada
                                            </span>
                                        @else
                                            <span class="badge badge-light">
                                                {{ $leitura->status_leitura_nome ?? 'Indefinida' }}
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        {{ $formatMoney($leitura->entrada_acerto ?? 0) }}
                                    </td>

                                    <td class="text-right">
                                        {{ $formatMoney($leitura->saida_acerto ?? 0) }}
                                    </td>

                                    <td class="text-right">
                                        {{ $formatMoney($leitura->saldo_acerto ?? $leitura->saldo_total) }}
                                    </td>
                                </tr>
                            @endforeach

                            <tr class="linha-separadora">
                                <td colspan="5"></td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <div class="barcode-legado">
                                        @for ($i = 1; $i <= 20; $i++)
                                            <span></span>
                                        @endfor
                                    </div>
                                </td>

                                <td class="text-right">
                                    <strong>Total Entrada</strong>
                                    <br>
                                    {{ $formatMoney($totalEntrada ?? 0) }}
                                </td>

                                <td class="text-right">
                                    <strong>Total Saída</strong>
                                    <br>
                                    {{ $formatMoney($totalSaida ?? 0) }}
                                </td>

                                <td class="text-right">
                                    <strong>Saldo Total</strong>
                                    <br>
                                    {{ $formatMoney($saldoTotal ?? 0) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                @elseif ($pesquisou)

                    <div class="alert alert-warning mb-0">
                        Nenhuma leitura foi encontrada para o período informado.
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
        .leitura-resumo th {
            font-weight: bold;
            color: #000;
            border-bottom: 1px solid #ddd;
        }

        .leitura-resumo td {
            border-top: 1px solid #ddd;
            color: #000;
        }

        .leitura-resumo .linha-separadora td {
            border-top: 4px solid #222;
            padding: 0;
            height: 0;
        }

        .barcode-legado {
            display: flex;
            align-items: flex-end;
            height: 45px;
            width: 70px;
            gap: 2px;
            margin-top: 10px;
        }

        .barcode-legado span {
            display: block;
            width: 2px;
            background: #00aeef;
            height: 40px;
        }

        .barcode-legado span:nth-child(2n) {
            height: 38px;
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

            .leitura-resumo {
                min-width: 720px;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('form-periodo-leituras');
            const btn = document.getElementById('btn-pesquisar');

            if (!form || !btn) {
                return;
            }

            form.addEventListener('submit', function () {
                btn.innerText = 'Loading...';
                btn.disabled = true;
            });
        });

        @if (session('swal_error'))
            Swal.fire({
                icon: 'error',
                title: 'Atenção',
                text: @json(session('swal_error')),
                confirmButtonText: 'OK'
            });
        @endif

        @if (session('swal_warning'))
            Swal.fire({
                icon: 'warning',
                title: 'Aviso',
                text: @json(session('swal_warning')),
                confirmButtonText: 'OK'
            });
        @endif

        @if (session('swal_success'))
            Swal.fire({
                icon: 'success',
                title: 'Sucesso',
                text: @json(session('swal_success')),
                confirmButtonText: 'OK'
            });
        @endif
    </script>
@stop