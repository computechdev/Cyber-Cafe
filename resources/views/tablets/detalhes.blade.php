@extends('adminlte::page')

@section('title', 'Detalhamento do Tablet')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Detalhamento do Tablet</h1>

        <a href="{{ route('tablets.index') }}" class="btn btn-secondary">
            Voltar
        </a>
    </div>
@stop
@section('content')
    

    @php
        $formatMoney = function ($valor) {
            $valor = (float) $valor;

            if (floor($valor) == $valor) {
                return number_format($valor, 0, '.', '');
            }

            return number_format($valor, 2, '.', '');
        };

        $formatPercent = function ($valor) {
            return number_format((float) $valor, 2, '.', '') . '%';
        };

        /*
    |--------------------------------------------------------------------------
    | OFICIAL
    |--------------------------------------------------------------------------
    */

        $entradaOficial = $leitura ? (float) $leitura->entrada : 0;
        $saidaOficial = $leitura ? (float) $leitura->saida : 0;
        $balancoOficial = $entradaOficial - $saidaOficial;

        $pgtoOficial = $entradaOficial > 0 ? ($saidaOficial / $entradaOficial) * 100 : 0;

        /*
    |--------------------------------------------------------------------------
    | PARCIAL
    |--------------------------------------------------------------------------
    */

        $entradaParcial = $leitura ? (float) $leitura->entrada_virtual : 0;
        $saidaParcial = $leitura ? (float) $leitura->saida_virtual : 0;
        $balancoParcial = $entradaParcial - $saidaParcial;

        $pgtoParcial = $entradaParcial > 0 ? ($saidaParcial / $entradaParcial) * 100 : 0;

        /*
    |--------------------------------------------------------------------------
    | REJOGO
    |--------------------------------------------------------------------------
    | No legado:
    |
    | Entrada = apostado / 100
    | Saída = premiado / 100
    | Balanço = (apostado - premiado) / 100
    | % Pgto = premiado / apostado * 100
    */

        $apostadoBruto = $leitura ? (float) $leitura->apostado : 0;
        $premiadoBruto = $leitura ? (float) $leitura->premiado : 0;

        $entradaRejogo = $apostadoBruto / 100;
        $saidaRejogo = $premiadoBruto / 100;
        $balancoRejogo = ($apostadoBruto - $premiadoBruto) / 100;

        $pgtoRejogo = $apostadoBruto > 0 ? ($premiadoBruto / $apostadoBruto) * 100 : 0;

        /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    | O legado usa max(data_hora) da tabela transacoes.
    */

        $dataLeitura =
            $ultimaTransacao && $ultimaTransacao->data_hora
                ? \Carbon\Carbon::parse($ultimaTransacao->data_hora)->format('d/m/Y H:i')
                : '-';
    @endphp
    <div class="card">
        <div class="card-header">
            <strong>
                {{ $tablet->idprod }} /
                {{ $tablet->cliente_nome ?? $tablet->cliente }} /
                {{ $tablet->ponto_nome ?? '-' }}
            </strong>
        </div>

        <div class="card-body">

            <div class="card">
                <div class="card-header">
                    Informações sobre Total da Leitura
                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-borderless table-hover text-center">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Data</th>
                                    <th class="text-success">Entrada</th>
                                    <th class="text-danger">Saída</th>
                                    <th class="text-info">Balanço</th>
                                    <th class="text-info">% Pgto</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td class="text-muted text-center">
                                        <h4><b>Oficial</b></h4>
                                    </td>

                                    <td class="text-muted text-center">
                                        <h4>{{ $dataLeitura }}</h4>
                                    </td>

                                    <td class="text-success text-center">
                                        <h4>$ {{ $formatMoney($entradaOficial) }}</h4>
                                    </td>

                                    <td class="text-danger text-center">
                                        <h4>$ {{ $formatMoney($saidaOficial) }}</h4>
                                    </td>

                                    <td class="text-info text-center">
                                        <h4>$ {{ $formatMoney($balancoOficial) }}</h4>
                                    </td>

                                    <td class="text-info text-center">
                                        <h4>{{ $formatPercent($pgtoOficial) }}</h4>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="text-muted text-center">
                                        <h4><b>Parcial</b></h4>
                                    </td>

                                    <td class="text-muted text-center">
                                        <h4>{{ $dataLeitura }}</h4>
                                    </td>

                                    <td class="text-success text-center">
                                        <h4>$ {{ $formatMoney($entradaParcial) }}</h4>
                                    </td>

                                    <td class="text-danger text-center">
                                        <h4>$ {{ $formatMoney($saidaParcial) }}</h4>
                                    </td>

                                    <td class="text-info text-center">
                                        <h4>$ {{ $formatMoney($balancoParcial) }}</h4>
                                    </td>

                                    <td class="text-info text-center">
                                        <h4>{{ $formatPercent($pgtoParcial) }}</h4>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted text-center">
                                        <h4><b>Rejogo</b></h4>
                                    </td>

                                    <td class="text-muted text-center">
                                        <h4>{{ $dataLeitura }}</h4>
                                    </td>

                                    <td class="text-success text-center">
                                        <h4>$ {{ $formatMoney($entradaRejogo) }}</h4>
                                    </td>

                                    <td class="text-danger text-center">
                                        <h4>$ {{ $formatMoney($saidaRejogo) }}</h4>
                                    </td>

                                    <td class="text-info text-center">
                                        <h4>$ {{ $formatMoney($balancoRejogo) }}</h4>
                                    </td>

                                    <td class="text-info text-center">
                                        <h4>{{ $formatPercent($pgtoRejogo) }}</h4>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-right mt-4">
                        <button type="button" class="btn btn-danger" disabled>
                            ZERAR LEITURA VIRTUAL
                        </button>
                    </div>

                </div>
            </div>

        </div>

        <div class="card-footer text-right">
            <a href="{{ route('tablets.index') }}" class="btn btn-secondary">
                Cancelar
            </a>

            <button type="button" class="btn btn-info" disabled>
                Salvar
            </button>
        </div>
    </div>

@stop

@section('js')
    @include('partials.sweetalert')
@stop
