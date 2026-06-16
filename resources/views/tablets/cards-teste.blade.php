@extends('adminlte::page')

@section('title', 'Tablets - Visual Teste')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Tablets - Visual Teste</h1>

        <div>
            <a href="{{ route('tablets.cards-teste', ['visual' => 'cards', 'status' => $status, 'busca' => $busca]) }}"
               class="btn btn-sm {{ $visual === 'cards' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-th-large"></i>
                Cards
            </a>

            <a href="{{ route('tablets.cards-teste', ['visual' => 'tabela', 'status' => $status, 'busca' => $busca]) }}"
               class="btn btn-sm {{ $visual === 'tabela' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-table"></i>
                Tabela
            </a>

            <a href="{{ route('tablets.index') }}" class="btn btn-secondary btn-sm">
                Tela original
            </a>
        </div>
    </div>
@stop

@section('content')

@php
    $formatMoney = function ($valor) {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    };

    $formatDate = function ($data) {
        if (!$data) {
            return '-';
        }

        return \Carbon\Carbon::parse($data)->format('d/m/Y H:i:s');
    };
@endphp

<div class="row mb-3">

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info">
                <i class="fas fa-tablet-alt"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Total</span>
                <span class="info-box-number">{{ $resumo['total'] }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success">
                <i class="fas fa-wifi"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Online</span>
                <span class="info-box-number">{{ $resumo['online'] }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning">
                <i class="fas fa-plug"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Offline</span>
                <span class="info-box-number">{{ $resumo['offline'] }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-secondary">
                <i class="fas fa-ban"></i>
            </span>

            <div class="info-box-content">
                <span class="info-box-text">Desabilitados</span>
                <span class="info-box-number">{{ $resumo['desabilitados'] }}</span>
            </div>
        </div>
    </div>

</div>

<div class="card mb-3">

    <div class="card-body">

        <form method="GET" action="{{ route('tablets.cards-teste') }}" class="row align-items-center">

            <input type="hidden" name="visual" value="{{ $visual }}">
            <input type="hidden" name="status" value="{{ $status }}">

            <div class="col-md-5">
                <input
                    type="text"
                    name="busca"
                    class="form-control"
                    value="{{ $busca }}"
                    placeholder="Buscar por tablet, cliente ou ponto..."
                >
            </div>

            <div class="col-md-7 text-right">

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-search"></i>
                    Pesquisar
                </button>

                <a href="{{ route('tablets.cards-teste', ['visual' => $visual, 'status' => 'todos', 'busca' => $busca]) }}"
                   class="btn btn-sm {{ $status === 'todos' ? 'btn-primary' : 'btn-outline-primary' }}">
                    Todos
                </a>

                <a href="{{ route('tablets.cards-teste', ['visual' => $visual, 'status' => 'online', 'busca' => $busca]) }}"
                   class="btn btn-sm {{ $status === 'online' ? 'btn-success' : 'btn-outline-success' }}">
                    Online
                </a>

                <a href="{{ route('tablets.cards-teste', ['visual' => $visual, 'status' => 'offline', 'busca' => $busca]) }}"
                   class="btn btn-sm {{ $status === 'offline' ? 'btn-warning' : 'btn-outline-warning' }}">
                    Offline
                </a>

                <a href="{{ route('tablets.cards-teste', ['visual' => $visual, 'status' => 'desabilitados', 'busca' => $busca]) }}"
                   class="btn btn-sm {{ $status === 'desabilitados' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                    Desabilitados
                </a>

            </div>

        </form>

    </div>

</div>

@if ($visual === 'cards')

    <div class="row">

        @forelse ($tablets as $tablet)

            @php
                $classeStatus = 'tablet-card-offline';

                if ($tablet->esta_desabilitado) {
                    $classeStatus = 'tablet-card-disabled';
                } elseif ($tablet->esta_online) {
                    $classeStatus = 'tablet-card-online';
                }
            @endphp

            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12 mb-3">

                <div class="card tablet-card {{ $classeStatus }}">

                    <div class="card-body">

                        <div class="tablet-card-header">

                            <div>
                                <h4 class="tablet-code mb-0">
                                    {{ $tablet->idprod }}
                                </h4>

                                <small class="text-muted">
                                    {{ $tablet->cliente ?? '-' }}
                                </small>
                            </div>

                            @if ($tablet->esta_online)
                                <span class="badge badge-success">
                                    <i class="fas fa-wifi"></i>
                                    Online
                                </span>
                            @else
                                <span class="badge badge-danger">
                                    <i class="fas fa-plug"></i>
                                    Offline
                                </span>
                            @endif

                        </div>

                        <hr>

                        <div class="tablet-info-line">
                            <span>Ponto</span>
                            <strong>{{ $tablet->ponto_nome ?? '-' }}</strong>
                        </div>

                        <div class="tablet-info-line">
                            <span>Crédito</span>
                            <strong>{{ $formatMoney($tablet->credito_atual) }}</strong>
                        </div>

                        <div class="tablet-info-line">
                            <span>Sistema</span>

                            @if ($tablet->esta_desabilitado)
                                <strong class="text-secondary">Desabilitado</strong>
                            @else
                                <strong class="text-success">Habilitado</strong>
                            @endif
                        </div>

                        <div class="tablet-info-line">
                            <span>Último contato</span>
                            <strong>{{ $formatDate($tablet->ultimo_contato) }}</strong>
                        </div>

                        <div class="tablet-actions mt-3">

                            <a href="{{ route('tablets.creditos', $tablet->id) }}"
                               class="btn btn-sm btn-success">
                                <i class="fas fa-plus"></i>
                                Crédito
                            </a>

                            <a href="{{ route('tablets.detalhes', $tablet->id) }}"
                               class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i>
                                Detalhes
                            </a>

                            <a href="{{ route('tablets.edit', $tablet->id) }}"
                               class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                                Editar
                            </a>

                            <form action="{{ route('tablets.toggle-ativo', $tablet->id) }}"
                                  method="POST"
                                  class="d-inline">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                        class="btn btn-sm {{ $tablet->esta_desabilitado ? 'btn-success' : 'btn-warning' }}">
                                    @if ($tablet->esta_desabilitado)
                                        <i class="fas fa-check"></i>
                                        Habilitar
                                    @else
                                        <i class="fas fa-ban"></i>
                                        Desabilitar
                                    @endif
                                </button>
                            </form>

                        </div>

                    </div>

                </div>

            </div>

        @empty

            <div class="col-12">
                <div class="alert alert-warning">
                    Nenhum tablet encontrado.
                </div>
            </div>

        @endforelse

    </div>

@endif

@if ($visual === 'tabela')

    <div class="card">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover table-striped tablet-table mb-0">

                    <thead>
                        <tr>
                            <th>Tablet</th>
                            <th>Cliente</th>
                            <th>Ponto</th>
                            <th class="text-center">Conexão</th>
                            <th class="text-center">Sistema</th>
                            <th class="text-right">Crédito</th>
                            <th>Último contato</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($tablets as $tablet)

                            <tr class="{{ $tablet->esta_desabilitado ? 'linha-desabilitada' : '' }}">

                                <td>
                                    <strong class="tablet-code">
                                        {{ $tablet->idprod }}
                                    </strong>
                                </td>

                                <td>
                                    {{ $tablet->cliente ?? '-' }}
                                </td>

                                <td>
                                    {{ $tablet->ponto_nome ?? '-' }}
                                </td>

                                <td class="text-center">
                                    @if ($tablet->esta_online)
                                        <span class="badge badge-success status-badge">
                                            <i class="fas fa-wifi"></i>
                                            Online
                                        </span>
                                    @else
                                        <span class="badge badge-danger status-badge">
                                            <i class="fas fa-plug"></i>
                                            Offline
                                        </span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    @if ($tablet->esta_desabilitado)
                                        <span class="badge badge-secondary status-badge">
                                            <i class="fas fa-ban"></i>
                                            Desabilitado
                                        </span>
                                    @else
                                        <span class="badge badge-primary status-badge">
                                            <i class="fas fa-check"></i>
                                            Habilitado
                                        </span>
                                    @endif
                                </td>

                                <td class="text-right">
                                    <strong>
                                        {{ $formatMoney($tablet->credito_atual) }}
                                    </strong>
                                </td>

                                <td>
                                    <small>
                                        {{ $formatDate($tablet->ultimo_contato) }}
                                    </small>
                                </td>

                                <td class="text-center">

                                    <div class="btn-group btn-group-sm">

                                        <a href="{{ route('tablets.creditos', $tablet->id) }}"
                                           class="btn btn-success"
                                           title="Adicionar crédito">
                                            <i class="fas fa-plus"></i>
                                        </a>

                                        <a href="{{ route('tablets.detalhes', $tablet->id) }}"
                                           class="btn btn-info"
                                           title="Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <a href="{{ route('tablets.edit', $tablet->id) }}"
                                           class="btn btn-primary"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                    </div>

                                    <form action="{{ route('tablets.toggle-ativo', $tablet->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="btn btn-sm {{ $tablet->esta_desabilitado ? 'btn-success' : 'btn-warning' }}"
                                                title="{{ $tablet->esta_desabilitado ? 'Habilitar' : 'Desabilitar' }}">
                                            @if ($tablet->esta_desabilitado)
                                                <i class="fas fa-check"></i>
                                            @else
                                                <i class="fas fa-ban"></i>
                                            @endif
                                        </button>
                                    </form>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-center p-4">
                                    Nenhum tablet encontrado.
                                </td>
                            </tr>

                        @endforelse
                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endif

@stop

@section('css')
<style>
    .info-box {
        min-height: 78px;
    }

    .info-box-icon {
        height: 78px;
        width: 78px;
        font-size: 30px;
    }

    .info-box-content {
        padding-top: 12px;
    }

    .tablet-card {
        border: 0;
        border-left: 6px solid #ccc;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.2s ease;
        min-height: 285px;
    }

    .tablet-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
    }

    .tablet-card-online {
        border-left-color: #28a745;
    }

    .tablet-card-offline {
        border-left-color: #dc3545;
    }

    .tablet-card-disabled {
        border-left-color: #6c757d;
        opacity: 0.85;
    }

    .tablet-card-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .tablet-code {
        font-weight: 700;
        letter-spacing: 1px;
    }

    .tablet-info-line {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 14px;
        padding: 5px 0;
        border-bottom: 1px dashed #e0e0e0;
    }

    .tablet-info-line span {
        color: #6c757d;
    }

    .tablet-info-line strong {
        text-align: right;
    }

    .tablet-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }

    .tablet-actions .btn,
    .tablet-actions form,
    .tablet-actions form button {
        width: 100%;
    }

    .tablet-table thead th {
        background: #f4f6f9;
        border-bottom: 2px solid #dee2e6;
        color: #343a40;
        font-size: 13px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .tablet-table td {
        vertical-align: middle;
        font-size: 14px;
        white-space: nowrap;
    }

    .status-badge {
        min-width: 105px;
        padding: 6px 8px;
        font-size: 12px;
    }

    .linha-desabilitada {
        opacity: 0.65;
    }

    .linha-desabilitada td {
        background: #f1f1f1 !important;
    }

    .btn-group-sm .btn,
    .btn-sm {
        margin-right: 2px;
    }

    @media (max-width: 768px) {
        .card-body .text-right {
            text-align: left !important;
            margin-top: 10px;
        }

        .card-body .btn {
            margin-top: 4px;
        }
    }

    @media (max-width: 576px) {
        .tablet-actions {
            grid-template-columns: 1fr;
        }
    }
</style>
@stop