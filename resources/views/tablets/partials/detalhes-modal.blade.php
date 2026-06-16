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
    */

    $dataLeitura =
        $ultimaTransacao && $ultimaTransacao->data_hora
            ? \Carbon\Carbon::parse($ultimaTransacao->data_hora)->format('d/m/Y H:i')
            : '-';
@endphp

<div class="card mb-0">
    <div class="card-header">
        Informações sobre Total da Leitura
    </div>

    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-borderless text-center">
                <thead>
                    <tr>
                        <th class="text-muted">
                            <h4>Tipo</h4>
                        </th>
                        <th class="text-muted">
                            <h4>Data</h4>
                        </th>
                        <th class="text-success">
                            <h4>Entrada</h4>
                        </th>
                        <th class="text-danger">
                            <h4>Saída</h4>
                        </th>
                        <th class="text-info">
                            <h4>Balanço</h4>
                        </th>
                        <th class="text-info">
                            <h4>% Pgto</h4>
                        </th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="text-muted">
                            <h4><b>Oficial</b></h4>
                        </td>

                        <td class="text-muted">
                            <h4>{{ $dataLeitura }}</h4>
                        </td>

                        <td class="text-success">
                            <h4>$ {{ $formatMoney($entradaOficial) }}</h4>
                        </td>

                        <td class="text-danger">
                            <h4>$ {{ $formatMoney($saidaOficial) }}</h4>
                        </td>

                        <td class="text-info">
                            <h4>$ {{ $formatMoney($balancoOficial) }}</h4>
                        </td>

                        <td class="text-info">
                            <h4>{{ $formatPercent($pgtoOficial) }}</h4>
                        </td>
                    </tr>

                    <tr>
                        <td class="text-muted">
                            <h4><b>Parcial</b></h4>
                        </td>

                        <td class="text-muted">
                            <h4>{{ $dataLeitura }}</h4>
                        </td>

                        <td class="text-success">
                            <h4>$ {{ $formatMoney($entradaParcial) }}</h4>
                        </td>

                        <td class="text-danger">
                            <h4>$ {{ $formatMoney($saidaParcial) }}</h4>
                        </td>

                        <td class="text-info">
                            <h4>$ {{ $formatMoney($balancoParcial) }}</h4>
                        </td>

                        <td class="text-info">
                            <h4>{{ $formatPercent($pgtoParcial) }}</h4>
                        </td>
                    </tr>

                    <tr>
                        <td class="text-muted">
                            <h4><b>Rejogo</b></h4>
                        </td>

                        <td class="text-muted">
                            <h4>{{ $dataLeitura }}</h4>
                        </td>

                        <td class="text-success">
                            <h4>$ {{ $formatMoney($entradaRejogo) }}</h4>
                        </td>

                        <td class="text-danger">
                            <h4>$ {{ $formatMoney($saidaRejogo) }}</h4>
                        </td>

                        <td class="text-info">
                            <h4>$ {{ $formatMoney($balancoRejogo) }}</h4>
                        </td>

                        <td class="text-info">
                            <h4>{{ $formatPercent($pgtoRejogo) }}</h4>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="text-right mt-4">
            <form action="{{ route('tablets.zerar-leitura-virtual', $tablet->id) }}" method="POST"
                class="d-inline form-zerar-virtual">
                @csrf
                @method('PATCH')

                <button type="submit" class="btn btn-danger">
                    ZERAR LEITURA VIRTUAL
                </button>
            </form>
        </div>

    </div>
</div>
