<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">

    <title>Fatura {{ $conta->id_cobranca }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        .container {
            padding: 24px;
        }

        .header {
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
        }

        .header p {
            margin: 4px 0 0 0;
            font-size: 12px;
        }

        .box {
            border: 1px solid #ccc;
            padding: 12px;
            margin-bottom: 14px;
        }

        .box-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f0f0f0;
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
            font-size: 11px;
        }

        td {
            border: 1px solid #ccc;
            padding: 6px;
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .summary-table td {
            border: none;
            padding: 4px 6px;
        }

        .summary-value {
            font-weight: bold;
            font-size: 14px;
        }

        .total-box {
            margin-top: 16px;
            border: 2px solid #222;
            padding: 12px;
        }

        .total-row {
            width: 100%;
        }

        .total-row td {
            border: none;
            font-size: 13px;
            padding: 5px;
        }

        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #555;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }

        .assinatura {
            margin-top: 45px;
            width: 260px;
            border-top: 1px solid #222;
            text-align: center;
            padding-top: 6px;
        }
    </style>
</head>

<body>

    @php
        $formatMoney = function ($valor) {
            return 'R$ ' . number_format((float) $valor, 2, ',', '.');
        };

        $formatPercent = function ($valor) {
            return number_format((float) $valor, 2, ',', '.') . '%';
        };

        $dataProcessamento = $conta->data_processamento
            ? \Carbon\Carbon::parse($conta->data_processamento)->format('d/m/Y H:i')
            : '-';

        $dataVencimento = $conta->data_vencimento
            ? \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y')
            : '-';

        $dataPagamento = $conta->data_pagamento
            ? \Carbon\Carbon::parse($conta->data_pagamento)->format('d/m/Y')
            : '-';

        $status = (int) $conta->pago === 1 ? 'Pago' : 'Pendente';
    @endphp

    <div class="container">

        <div class="header">
            <h1>Fatura Nº {{ $conta->id_cobranca }}</h1>
            <p>Relatório de cobrança - CyberCafe</p>
            <p>Gerado em {{ $geradoEm->format('d/m/Y H:i:s') }}</p>
        </div>

        <div class="box">
            <div class="box-title">Dados da Fatura</div>

            <table class="summary-table">
                <tr>
                    <td><strong>Nº da Fatura:</strong></td>
                    <td>{{ $conta->id_cobranca }}</td>

                    <td><strong>Status:</strong></td>
                    <td>{{ $status }}</td>
                </tr>

                <tr>
                    <td><strong>Processamento:</strong></td>
                    <td>{{ $dataProcessamento }}</td>

                    <td><strong>Vencimento:</strong></td>
                    <td>{{ $dataVencimento }}</td>
                </tr>

                <tr>
                    <td><strong>Pagamento:</strong></td>
                    <td>{{ $dataPagamento }}</td>

                    <td><strong>Tipo:</strong></td>
                    <td>{{ $conta->tipo_cobranca ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="box">
            <div class="box-title">Cliente e Administração</div>

            <table class="summary-table">
                <tr>
                    <td><strong>Cliente:</strong></td>
                    <td>
                        {{ $conta->cliente_nome }}

                        @if (!empty($conta->cliente_username))
                            ({{ $conta->cliente_username }})
                        @endif
                    </td>

                    <td><strong>Comissão Cliente:</strong></td>
                    <td>{{ $formatPercent($porcentagemCliente) }}</td>
                </tr>

                <tr>
                    <td><strong>Admin:</strong></td>
                    <td>
                        {{ $conta->admin_nome ?? '-' }}

                        @if (!empty($conta->admin_username))
                            ({{ $conta->admin_username }})
                        @endif
                    </td>

                    <td><strong>Comissão Admin:</strong></td>
                    <td>{{ $formatPercent($porcentagemAdmin) }}</td>
                </tr>
            </table>
        </div>

        <div class="box">
            <div class="box-title">Resumo Financeiro</div>

            <table>
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th class="text-right">Percentual</th>
                        <th class="text-right">Valor</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Valor Total do Acerto</td>
                        <td class="text-right">100,00%</td>
                        <td class="text-right summary-value">{{ $formatMoney($valorTotalAcerto) }}</td>
                    </tr>

                    <tr>
                        <td>Valor do Admin</td>
                        <td class="text-right">{{ $formatPercent($porcentagemAdmin) }}</td>
                        <td class="text-right summary-value">{{ $formatMoney($valorAdmin) }}</td>
                    </tr>

                    <tr>
                        <td>Valor do Cliente</td>
                        <td class="text-right">{{ $formatPercent($porcentagemCliente) }}</td>
                        <td class="text-right summary-value">{{ $formatMoney($valorCliente) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="box">
            <div class="box-title">Leituras / Métricas da Fatura</div>

            <table>
                <thead>
                    <tr>
                        <th>Tablet</th>
                        <th>Ponto</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th class="text-right">Entrada</th>
                        <th class="text-right">Saída</th>
                        <th class="text-right">Saldo</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($metricas as $metrica)
                        <tr>
                            <td>{{ $metrica->idprod }}</td>

                            <td>{{ $metrica->ponto_nome ?? '-' }}</td>

                            <td>
                                {{ $metrica->dataorder ? \Carbon\Carbon::parse($metrica->dataorder)->format('d/m/Y H:i') : '-' }}
                            </td>

                            <td>{{ $metrica->status_nome }}</td>

                            <td class="text-right">
                                {{ $formatMoney($metrica->entrada_acerto ?? 0) }}
                            </td>

                            <td class="text-right">
                                {{ $formatMoney($metrica->saida_acerto ?? 0) }}
                            </td>

                            <td class="text-right">
                                {{ $formatMoney($metrica->saldo_acerto ?? 0) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">
                                Nenhuma leitura vinculada foi encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if ($metricas->count() > 0)
                    <tfoot>
                        <tr>
                            <th colspan="4">Totais</th>
                            <th class="text-right">
                                {{ $formatMoney($metricas->sum('entrada_acerto')) }}
                            </th>
                            <th class="text-right">
                                {{ $formatMoney($metricas->sum('saida_acerto')) }}
                            </th>
                            <th class="text-right">
                                {{ $formatMoney($metricas->sum('saldo_acerto')) }}
                            </th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>

        <div class="total-box">
            <table class="total-row">
                <tr>
                    <td><strong>Total da Fatura</strong></td>
                    <td class="text-right"><strong>{{ $formatMoney($valorTotalAcerto) }}</strong></td>
                </tr>

                <tr>
                    <td><strong>Admin</strong></td>
                    <td class="text-right"><strong>{{ $formatMoney($valorAdmin) }}</strong></td>
                </tr>

                <tr>
                    <td><strong>Cliente</strong></td>
                    <td class="text-right"><strong>{{ $formatMoney($valorCliente) }}</strong></td>
                </tr>
            </table>
        </div>

        <div class="assinatura">
            Assinatura / Conferência
        </div>

        <div class="footer">
            Documento gerado automaticamente pelo sistema CyberCafe.
        </div>

    </div>

</body>

</html>