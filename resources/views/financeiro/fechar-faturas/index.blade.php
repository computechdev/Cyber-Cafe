@extends('adminlte::page')

@section('title', 'Fechar Faturas')

@section('content_header')
    <h1>Fechar Faturas</h1>
@stop

@section('css')
    <style>
        .summary-clean {
            border-radius: 14px;
            padding: 14px;
            background: #fff;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
            margin-bottom: 12px;
            min-height: 92px;
        }

        .summary-clean-label {
            color: #6c757d;
            font-size: 13px;
            margin-bottom: 4px;
        }

        .summary-clean-value {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .section-title-clean {
            font-size: 17px;
            font-weight: 700;
            margin: 18px 0 12px;
        }

        .desktop-acerto-card {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .04);
        }

        .desktop-acerto-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .desktop-acerto-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }

        .desktop-acerto-subtitle {
            margin: 3px 0 0;
            font-size: 13px;
            color: #6c757d;
        }

        .desktop-acerto-grid {
            display: grid;
            grid-template-columns: repeat(8, minmax(110px, 1fr));
            gap: 8px;
        }

        .desktop-acerto-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 10px;
            min-height: 72px;
        }

        .desktop-acerto-label {
            color: #6c757d;
            font-size: 12px;
            margin-bottom: 4px;
            white-space: nowrap;
        }

        .desktop-acerto-value {
            font-size: 15px;
            font-weight: 700;
            margin: 0;
            white-space: nowrap;
        }

        .desktop-acerto-percent {
            display: block;
            font-size: 11px;
            color: #6c757d;
            font-weight: 400;
            margin-top: 2px;
        }

        .mobile-acerto-card {
            border-radius: 14px;
            border: 1px solid #e9ecef;
            background: #fff;
            padding: 14px;
            margin-bottom: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .04);
        }

        .mobile-acerto-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 10px;
        }

        .mobile-acerto-title {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
        }

        .mobile-acerto-subtitle {
            font-size: 12px;
            color: #6c757d;
            margin: 2px 0 0;
        }

        .mobile-acerto-line {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f1f1f1;
            font-size: 14px;
        }

        .mobile-acerto-line:last-child {
            border-bottom: none;
        }

        .mobile-acerto-line span:first-child {
            color: #6c757d;
        }

        .mobile-acerto-result {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 10px;
            margin-top: 8px;
            font-weight: 700;
        }

        .btn-mobile-full {
            white-space: nowrap;
        }

        .tabela-tecnica-title {
            font-size: 16px;
            font-weight: 700;
            margin: 20px 0 10px;
        }

        @media (max-width: 1199.98px) {
            .desktop-acerto-grid {
                grid-template-columns: repeat(4, minmax(110px, 1fr));
            }
        }

        @media (max-width: 767.98px) {
            .content-header h1 {
                font-size: 22px;
            }

            .btn-mobile-full {
                width: 100%;
                margin-top: 8px;
            }

            .summary-clean {
                min-height: auto;
            }

            .summary-clean-value {
                font-size: 20px;
            }

            .mobile-acerto-header {
                display: block;
            }

            .mobile-acerto-header .badge {
                margin-top: 8px;
            }
        }
    </style>
@stop

@section('content')

    @php
        $formatMoney = function ($valor) {
            return 'R$ ' . number_format((float) $valor, 2, ',', '.');
        };

        $formatPercent = function ($valor) {
            return number_format((float) $valor, 2, ',', '.') . '%';
        };
    @endphp

    <div class="card">
        <div class="card-header">
            <strong>Selecionar cliente</strong>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('contas-receber.fechar.index') }}">
                <div class="row">
                    <div class="col-md-8">
                        <label>Cliente</label>

                        <select name="cliente_id" class="form-control">
                            <option value="">Selecione...</option>

                            @foreach ($clientes as $clienteItem)
                                <option value="{{ $clienteItem->id }}"
                                    {{ (string) $clienteId === (string) $clienteItem->id ? 'selected' : '' }}>
                                    {{ $clienteItem->name }}
                                    {{ $clienteItem->username ? '(' . $clienteItem->username . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-mobile-full">
                            <i class="fas fa-search"></i>
                            Buscar Leituras Abertas
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($cliente)
        <div class="card">
            <div class="card-header bg-dark">
                <strong>Acerto aberto de {{ $cliente->name }}</strong>
            </div>

            <div class="card-body">

                @if (isset($cobrancasAbertas) && $cobrancasAbertas->isNotEmpty())
                    <div class="alert alert-info">
                        <strong>Cobranças abertas:</strong>

                        @foreach ($cobrancasAbertas as $cobrancaAberta)
                            <span class="badge badge-primary">
                                #{{ $cobrancaAberta->id_cobranca }}
                            </span>
                        @endforeach
                    </div>
                @endif

                @if ($metricas->isEmpty())
                    <div class="alert alert-warning mb-0">
                        Nenhuma leitura aberta encontrada para este cliente.
                    </div>
                @else

                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-6">
                            <div class="summary-clean">
                                <div class="summary-clean-label">Entrada do Acerto</div>
                                <p class="summary-clean-value text-info">
                                    {{ $formatMoney($totais['entrada'] ?? 0) }}
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-6">
                            <div class="summary-clean">
                                <div class="summary-clean-label">Saída do Acerto</div>
                                <p class="summary-clean-value text-warning">
                                    {{ $formatMoney($totais['saida'] ?? 0) }}
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-6">
                            <div class="summary-clean">
                                <div class="summary-clean-label">Saldo do Acerto</div>
                                <p class="summary-clean-value text-dark">
                                    {{ $formatMoney($totais['saldo'] ?? 0) }}
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-6">
                            <div class="summary-clean">
                                <div class="summary-clean-label">Créditos</div>
                                <p class="summary-clean-value {{ ($totais['creditos'] ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $totais['creditos'] ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3 col-md-6 col-6">
                            <div class="summary-clean">
                                <div class="summary-clean-label">Resumo Admin</div>
                                <p class="summary-clean-value text-success">
                                    {{ $formatMoney($totais['valor_admin'] ?? 0) }}
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-6">
                            <div class="summary-clean">
                                <div class="summary-clean-label">Cliente</div>
                                <p class="summary-clean-value text-primary">
                                    {{ $formatMoney($totais['valor_cliente'] ?? 0) }}
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-6">
                            <div class="summary-clean">
                                <div class="summary-clean-label">Ponto</div>
                                <p class="summary-clean-value text-danger">
                                    {{ $formatMoney($totais['valor_ponto'] ?? 0) }}
                                </p>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-6 col-6">
                            <div class="summary-clean">
                                <div class="summary-clean-label">Distribuído</div>
                                <p class="summary-clean-value text-muted">
                                    {{ $formatMoney($totais['valor_distribuido'] ?? 0) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if (($totais['creditos'] ?? 0) > 0)
                        <div class="alert alert-danger">
                            <strong>Atenção:</strong>
                            existem créditos em aberto. Esta fatura não poderá ser fechada enquanto os créditos não forem zerados.
                        </div>
                    @endif

                    @if (($totais['porcentagem_invalida'] ?? false) === true)
                        <div class="alert alert-danger">
                            <strong>Atenção:</strong>
                            a soma das porcentagens passou de 100%.
                            Corrija o cadastro antes de fechar.
                        </div>
                    @endif

                    <div class="section-title-clean">
                        Leituras do acerto
                    </div>

                    {{-- DESKTOP - CARD HORIZONTAL --}}
                    <div class="d-none d-md-block">
                        @foreach ($metricas as $metrica)
                            <div class="desktop-acerto-card">
                                <div class="desktop-acerto-header">
                                    <div>
                                        <p class="desktop-acerto-title">
                                            Tablet {{ $metrica->idprod }}
                                        </p>

                                        <p class="desktop-acerto-subtitle">
                                            Ponto: {{ $metrica->ponto_nome ?? 'Sem ponto' }}
                                            ·
                                            {{ $metrica->dataorder ? \Carbon\Carbon::parse($metrica->dataorder)->format('d/m/Y H:i') : '-' }}
                                            ·
                                            Métrica #{{ $metrica->id }}
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        @if (($metrica->creditos_numero ?? 0) > 0)
                                            <span class="badge badge-danger">
                                                Créditos: {{ $metrica->creditos }}
                                            </span>
                                        @else
                                            <span class="badge badge-success">
                                                Sem créditos
                                            </span>
                                        @endif

                                        @if (($metrica->porcentagem_distribuida ?? 0) > 100)
                                            <span class="badge badge-danger ml-1">
                                                % inválida
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="desktop-acerto-grid">
                                    <div class="desktop-acerto-item">
                                        <div class="desktop-acerto-label">Entrada do Acerto</div>
                                        <p class="desktop-acerto-value text-info">
                                            {{ $formatMoney($metrica->entrada_acerto ?? 0) }}
                                        </p>
                                    </div>

                                    <div class="desktop-acerto-item">
                                        <div class="desktop-acerto-label">Saída do Acerto</div>
                                        <p class="desktop-acerto-value text-warning">
                                            {{ $formatMoney($metrica->saida_acerto ?? 0) }}
                                        </p>
                                    </div>

                                    <div class="desktop-acerto-item">
                                        <div class="desktop-acerto-label">Saldo do Acerto</div>
                                        <p class="desktop-acerto-value">
                                            {{ $formatMoney($metrica->saldo_acerto ?? 0) }}
                                        </p>
                                    </div>

                                    <div class="desktop-acerto-item">
                                        <div class="desktop-acerto-label">Resumo Admin</div>
                                        <p class="desktop-acerto-value text-success">
                                            {{ $formatMoney($metrica->valor_admin ?? 0) }}
                                            <span class="desktop-acerto-percent">
                                                {{ $formatPercent($metrica->porcentagem_admin ?? 0) }}
                                            </span>
                                        </p>
                                    </div>

                                    <div class="desktop-acerto-item">
                                        <div class="desktop-acerto-label">Cliente</div>
                                        <p class="desktop-acerto-value text-primary">
                                            {{ $formatMoney($metrica->valor_cliente ?? 0) }}
                                            <span class="desktop-acerto-percent">
                                                {{ $formatPercent($metrica->porcentagem_cliente ?? 0) }}
                                            </span>
                                        </p>
                                    </div>

                                    <div class="desktop-acerto-item">
                                        <div class="desktop-acerto-label">Ponto</div>
                                        <p class="desktop-acerto-value text-danger">
                                            {{ $formatMoney($metrica->valor_ponto ?? 0) }}
                                            <span class="desktop-acerto-percent">
                                                {{ $formatPercent($metrica->porcentagem_ponto ?? 0) }}
                                            </span>
                                        </p>
                                    </div>

                                    <div class="desktop-acerto-item">
                                        <div class="desktop-acerto-label">Distribuído</div>
                                        <p class="desktop-acerto-value">
                                            {{ $formatMoney($metrica->valor_distribuido ?? 0) }}
                                            <span class="desktop-acerto-percent">
                                                {{ $formatPercent($metrica->porcentagem_distribuida ?? 0) }}
                                            </span>
                                        </p>
                                    </div>

                                    <div class="desktop-acerto-item">
                                        <div class="desktop-acerto-label">Créditos</div>
                                        <p class="desktop-acerto-value {{ ($metrica->creditos_numero ?? 0) > 0 ? 'text-danger' : 'text-success' }}">
                                            {{ ($metrica->creditos_numero ?? 0) > 0 ? $metrica->creditos : '0' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-secondary"
                                        type="button"
                                        data-toggle="collapse"
                                        data-target="#detalhes-metrica-desktop-{{ $metrica->id }}"
                                        aria-expanded="false"
                                        aria-controls="detalhes-metrica-desktop-{{ $metrica->id }}">
                                        <i class="fas fa-list"></i>
                                        Detalhes técnicos
                                    </button>
                                </div>

                                <div class="collapse mt-3" id="detalhes-metrica-desktop-{{ $metrica->id }}">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-sm mb-0">
                                            <tbody>
                                                <tr>
                                                    <th style="width: 220px;">ID da métrica</th>
                                                    <td>{{ $metrica->id }}</td>
                                                </tr>

                                                <tr>
                                                    <th>ID da cobrança</th>
                                                    <td>{{ $metrica->id_cobranca }}</td>
                                                </tr>

                                                <tr>
                                                    <th>Entrada atual</th>
                                                    <td>{{ $formatMoney($metrica->entrada_atual ?? 0) }}</td>
                                                </tr>

                                                <tr>
                                                    <th>Entrada anterior</th>
                                                    <td>{{ $formatMoney($metrica->entrada_anterior_numero ?? 0) }}</td>
                                                </tr>

                                                <tr>
                                                    <th>Entrada do acerto</th>
                                                    <td>{{ $formatMoney($metrica->entrada_acerto ?? 0) }}</td>
                                                </tr>

                                                <tr>
                                                    <th>Saída atual</th>
                                                    <td>{{ $formatMoney($metrica->saida_atual ?? 0) }}</td>
                                                </tr>

                                                <tr>
                                                    <th>Saída anterior</th>
                                                    <td>{{ $formatMoney($metrica->saida_anterior_numero ?? 0) }}</td>
                                                </tr>

                                                <tr>
                                                    <th>Saída do acerto</th>
                                                    <td>{{ $formatMoney($metrica->saida_acerto ?? 0) }}</td>
                                                </tr>

                                                <tr>
                                                    <th>Saldo do acerto</th>
                                                    <td>{{ $formatMoney($metrica->saldo_acerto ?? 0) }}</td>
                                                </tr>

                                                <tr>
                                                    <th>% Admin</th>
                                                    <td>{{ $formatPercent($metrica->porcentagem_admin ?? 0) }}</td>
                                                </tr>

                                                <tr>
                                                    <th>% Cliente</th>
                                                    <td>{{ $formatPercent($metrica->porcentagem_cliente ?? 0) }}</td>
                                                </tr>

                                                <tr>
                                                    <th>% Ponto</th>
                                                    <td>{{ $formatPercent($metrica->porcentagem_ponto ?? 0) }}</td>
                                                </tr>

                                                <tr>
                                                    <th>% Distribuído</th>
                                                    <td>{{ $formatPercent($metrica->porcentagem_distribuida ?? 0) }}</td>
                                                </tr>

                                                <tr>
                                                    <th>Valor distribuído</th>
                                                    <td>{{ $formatMoney($metrica->valor_distribuido ?? 0) }}</td>
                                                </tr>

                                                <tr>
                                                    <th>Sobra técnica</th>
                                                    <td>{{ $formatMoney($metrica->valor_sobra ?? 0) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- MOBILE - FORMATO VERTICAL --}}
                    <div class="d-md-none">
                        @foreach ($metricas as $metrica)
                            <div class="mobile-acerto-card">
                                <div class="mobile-acerto-header">
                                    <div>
                                        <p class="mobile-acerto-title">
                                            Tablet {{ $metrica->idprod }}
                                        </p>

                                        <p class="mobile-acerto-subtitle">
                                            {{ $metrica->ponto_nome ?? 'Sem ponto' }}
                                            ·
                                            {{ $metrica->dataorder ? \Carbon\Carbon::parse($metrica->dataorder)->format('d/m/Y H:i') : '-' }}
                                        </p>
                                    </div>

                                    <div>
                                        @if (($metrica->creditos_numero ?? 0) > 0)
                                            <span class="badge badge-danger">
                                                Créditos: {{ $metrica->creditos }}
                                            </span>
                                        @else
                                            <span class="badge badge-success">
                                                Sem créditos
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="mobile-acerto-line">
                                    <span>Entrada do Acerto</span>
                                    <strong>{{ $formatMoney($metrica->entrada_acerto ?? 0) }}</strong>
                                </div>

                                <div class="mobile-acerto-line">
                                    <span>Saída do Acerto</span>
                                    <strong>{{ $formatMoney($metrica->saida_acerto ?? 0) }}</strong>
                                </div>

                                <div class="mobile-acerto-line">
                                    <span>Saldo do Acerto</span>
                                    <strong>{{ $formatMoney($metrica->saldo_acerto ?? 0) }}</strong>
                                </div>

                                <div class="mobile-acerto-line">
                                    <span>Cliente {{ $formatPercent($metrica->porcentagem_cliente ?? 0) }}</span>
                                    <strong class="text-primary">{{ $formatMoney($metrica->valor_cliente ?? 0) }}</strong>
                                </div>

                                <div class="mobile-acerto-line">
                                    <span>Ponto {{ $formatPercent($metrica->porcentagem_ponto ?? 0) }}</span>
                                    <strong class="text-danger">{{ $formatMoney($metrica->valor_ponto ?? 0) }}</strong>
                                </div>

                                <div class="mobile-acerto-line">
                                    <span>Distribuído {{ $formatPercent($metrica->porcentagem_distribuida ?? 0) }}</span>
                                    <strong>{{ $formatMoney($metrica->valor_distribuido ?? 0) }}</strong>
                                </div>

                                <div class="mobile-acerto-result">
                                    <div class="d-flex justify-content-between">
                                        <span>Resumo Admin {{ $formatPercent($metrica->porcentagem_admin ?? 0) }}</span>
                                        <span class="text-success">{{ $formatMoney($metrica->valor_admin ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- TABELA TÉCNICA GERAL - ABERTA NO DESKTOP E OCULTA NO CELULAR --}}
                    <div class="mt-4 d-none d-md-block">
                        <div class="tabela-tecnica-title">
                            Tabela técnica geral
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tablet</th>
                                        <th>Ponto</th>
                                        <th>Data</th>

                                        <th class="text-right">Entrada Atual</th>
                                        <th class="text-right">Entrada Anterior</th>
                                        <th class="text-right">Entrada Acerto</th>

                                        <th class="text-right">Saída Atual</th>
                                        <th class="text-right">Saída Anterior</th>
                                        <th class="text-right">Saída Acerto</th>

                                        <th class="text-right">Saldo Acerto</th>

                                        <th class="text-right">% Admin</th>
                                        <th class="text-right">Resumo Admin</th>

                                        <th class="text-right">% Cliente</th>
                                        <th class="text-right">Valor Cliente</th>

                                        <th class="text-right">% Ponto</th>
                                        <th class="text-right">Valor Ponto</th>

                                        <th class="text-right">% Distribuído</th>
                                        <th class="text-right">Distribuído</th>

                                        <th class="text-right">% Sobra Técnica</th>
                                        <th class="text-right">Sobra Técnica</th>

                                        <th class="text-right">Créditos</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($metricas as $metrica)
                                        <tr>
                                            <td>{{ $metrica->id }}</td>
                                            <td>{{ $metrica->idprod }}</td>
                                            <td>{{ $metrica->ponto_nome ?? '-' }}</td>
                                            <td>
                                                {{ $metrica->dataorder ? \Carbon\Carbon::parse($metrica->dataorder)->format('d/m/Y H:i') : '-' }}
                                            </td>

                                            <td class="text-right">{{ $formatMoney($metrica->entrada_atual ?? 0) }}</td>
                                            <td class="text-right">{{ $formatMoney($metrica->entrada_anterior_numero ?? 0) }}</td>
                                            <td class="text-right">{{ $formatMoney($metrica->entrada_acerto ?? 0) }}</td>

                                            <td class="text-right">{{ $formatMoney($metrica->saida_atual ?? 0) }}</td>
                                            <td class="text-right">{{ $formatMoney($metrica->saida_anterior_numero ?? 0) }}</td>
                                            <td class="text-right">{{ $formatMoney($metrica->saida_acerto ?? 0) }}</td>

                                            <td class="text-right">{{ $formatMoney($metrica->saldo_acerto ?? 0) }}</td>

                                            <td class="text-right">{{ $formatPercent($metrica->porcentagem_admin ?? 0) }}</td>
                                            <td class="text-right">{{ $formatMoney($metrica->valor_admin ?? 0) }}</td>

                                            <td class="text-right">{{ $formatPercent($metrica->porcentagem_cliente ?? 0) }}</td>
                                            <td class="text-right">{{ $formatMoney($metrica->valor_cliente ?? 0) }}</td>

                                            <td class="text-right">{{ $formatPercent($metrica->porcentagem_ponto ?? 0) }}</td>
                                            <td class="text-right">{{ $formatMoney($metrica->valor_ponto ?? 0) }}</td>

                                            <td class="text-right">
                                                @if (($metrica->porcentagem_distribuida ?? 0) > 100)
                                                    <span class="badge badge-danger">
                                                        {{ $formatPercent($metrica->porcentagem_distribuida ?? 0) }}
                                                    </span>
                                                @else
                                                    {{ $formatPercent($metrica->porcentagem_distribuida ?? 0) }}
                                                @endif
                                            </td>

                                            <td class="text-right">{{ $formatMoney($metrica->valor_distribuido ?? 0) }}</td>
                                            <td class="text-right">{{ $formatPercent($metrica->porcentagem_sobra ?? 0) }}</td>
                                            <td class="text-right">{{ $formatMoney($metrica->valor_sobra ?? 0) }}</td>

                                            <td class="text-right">
                                                @if (($metrica->creditos_numero ?? 0) > 0)
                                                    <span class="badge badge-danger">
                                                        {{ $metrica->creditos }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-success">
                                                        0
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="4">Totais</td>

                                        <td class="text-right">-</td>
                                        <td class="text-right">-</td>
                                        <td class="text-right">{{ $formatMoney($totais['entrada'] ?? 0) }}</td>

                                        <td class="text-right">-</td>
                                        <td class="text-right">-</td>
                                        <td class="text-right">{{ $formatMoney($totais['saida'] ?? 0) }}</td>

                                        <td class="text-right">{{ $formatMoney($totais['saldo'] ?? 0) }}</td>

                                        <td class="text-right">-</td>
                                        <td class="text-right">{{ $formatMoney($totais['valor_admin'] ?? 0) }}</td>

                                        <td class="text-right">-</td>
                                        <td class="text-right">{{ $formatMoney($totais['valor_cliente'] ?? 0) }}</td>

                                        <td class="text-right">-</td>
                                        <td class="text-right">{{ $formatMoney($totais['valor_ponto'] ?? 0) }}</td>

                                        <td class="text-right">-</td>
                                        <td class="text-right">{{ $formatMoney($totais['valor_distribuido'] ?? 0) }}</td>

                                        <td class="text-right">-</td>
                                        <td class="text-right">{{ $formatMoney($totais['valor_sobra'] ?? 0) }}</td>

                                        <td class="text-right">{{ $totais['creditos'] ?? 0 }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <form method="POST"
                        action="{{ route('contas-receber.fechar.store') }}"
                        class="form-confirmar-acao mt-3"
                        data-titulo="Fechar fatura?"
                        data-texto="Será fechada a fatura aberta de {{ $cliente->name }}. Saldo do acerto: {{ $formatMoney($totais['saldo'] ?? 0) }}. Resumo admin: {{ $formatMoney($totais['valor_admin'] ?? 0) }}."
                        data-confirmar="Sim, fechar fatura"
                        data-icon="warning">
                        @csrf

                        <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">

                        <button type="submit"
                            class="btn btn-success btn-mobile-full"
                            {{ (($totais['creditos'] ?? 0) > 0 || ($totais['porcentagem_invalida'] ?? false)) ? 'disabled' : '' }}>
                            <i class="fas fa-lock"></i>
                            Fechar Fatura
                        </button>

                        @if (($totais['creditos'] ?? 0) > 0)
                            <small class="text-danger ml-md-2 d-block d-md-inline mt-2 mt-md-0">
                                Não é possível fechar com créditos em aberto.
                            </small>
                        @endif

                        @if (($totais['porcentagem_invalida'] ?? false) === true)
                            <small class="text-danger ml-md-2 d-block d-md-inline mt-2 mt-md-0">
                                Corrija as porcentagens antes de fechar.
                            </small>
                        @endif
                    </form>

                @endif

            </div>
        </div>
    @endif

@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('submit', function(event) {
            const form = event.target;

            if (!form.classList.contains('form-confirmar-acao')) {
                return;
            }

            event.preventDefault();

            Swal.fire({
                title: form.dataset.titulo || 'Confirmar?',
                text: form.dataset.texto || 'Deseja continuar?',
                icon: form.dataset.icon || 'warning',
                showCancelButton: true,
                confirmButtonText: form.dataset.confirmar || 'Sim',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    form.submit();
                }
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

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Atenção',
                html: @json(implode('<br>', $errors->all())),
                confirmButtonText: 'OK'
            });
        @endif
    </script>
@stop