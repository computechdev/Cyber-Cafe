@extends('adminlte::page')

@section('title', 'Tablets')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Tablets</h1>
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

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

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

            <form method="GET" action="{{ route('tablets.index') }}" class="row align-items-center">

                <input type="hidden" name="status" value="{{ $status }}">

                <div class="col-md-5">
                    <input type="text" name="busca" class="form-control" value="{{ $busca }}"
                        placeholder="Buscar por tablet, cliente ou ponto...">
                </div>

                <div class="col-md-7 text-right">

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i>
                        Pesquisar
                    </button>

                    <a href="{{ route('tablets.index', ['status' => 'todos', 'busca' => $busca]) }}"
                        class="btn btn-sm {{ $status === 'todos' ? 'btn-primary' : 'btn-outline-primary' }}">
                        Todos
                    </a>

                    <a href="{{ route('tablets.index', ['status' => 'online', 'busca' => $busca]) }}"
                        class="btn btn-sm {{ $status === 'online' ? 'btn-success' : 'btn-outline-success' }}">
                        Online
                    </a>

                    <a href="{{ route('tablets.index', ['status' => 'offline', 'busca' => $busca]) }}"
                        class="btn btn-sm {{ $status === 'offline' ? 'btn-warning' : 'btn-outline-warning' }}">
                        Offline
                    </a>

                    <a href="{{ route('tablets.index', ['status' => 'desabilitados', 'busca' => $busca]) }}"
                        class="btn btn-sm {{ $status === 'desabilitados' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                        Desabilitados
                    </a>

                </div>

            </form>

        </div>

    </div>

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

                <div class="card tablet-card {{ $classeStatus }}" id="card-tablet-{{ $tablet->idprod }}">

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
                                <span class="badge badge-success" id="status-conexao-{{ $tablet->idprod }}">
                                    <i class="fas fa-wifi"></i>
                                    Online
                                </span>
                            @else
                                <span class="badge badge-danger" id="status-conexao-{{ $tablet->idprod }}">
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

                            <strong class="credito-realtime" id="credito-tablet-{{ $tablet->idprod }}"
                                data-idprod="{{ $tablet->idprod }}">
                                {{ $formatMoney($tablet->credito_atual) }}
                            </strong>
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
                            <strong id="ultimo-contato-{{ $tablet->idprod }}">
                                {{ $formatDate($tablet->ultimo_contato) }}
                            </strong>
                        </div>

                        <div class="tablet-actions mt-3">

                            <a href="{{ route('tablets.creditos', $tablet->id) }}" class="btn btn-sm btn-success">
                                <i class="fas fa-plus"></i>
                                Crédito
                            </a>

                            <button type="button" class="btn btn-sm btn-info btn-detalhes-tablet"
                                data-url="{{ route('tablets.detalhes-modal', $tablet->id) }}">
                                <i class="fas fa-eye"></i>
                                Detalhes
                            </button>

                            <a href="{{ route('tablets.edit', $tablet->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-edit"></i>
                                Editar
                            </a>

                            <form action="{{ route('tablets.toggle-ativo', $tablet->id) }}" method="POST"
                                class="d-inline form-confirmar-acao"
                                data-titulo="{{ $tablet->esta_desabilitado ? 'Habilitar tablet?' : 'Desabilitar tablet?' }}"
                                data-texto="Tablet {{ $tablet->idprod }}"
                                data-confirmar="{{ $tablet->esta_desabilitado ? 'Sim, habilitar' : 'Sim, desabilitar' }}"
                                data-icon="warning">
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

                            <form action="{{ route('tablets.retirada-creditos', $tablet->id) }}" method="POST"
                                class="d-inline form-confirmar-acao" data-titulo="Confirmar retirada?"
                                data-texto="Enviar retirada para o tablet {{ $tablet->idprod }}?"
                                data-confirmar="Sim, retirar" data-icon="warning">
                                @csrf

                                <button type="submit" class="btn btn-sm btn-dark">
                                    <i class="fas fa-money-bill"></i>
                                    Retirada
                                </button>
                            </form>

                            <form action="{{ route('tablets.destroy', $tablet->id) }}" method="POST"
                                class="d-inline form-confirmar-acao" data-titulo="Excluir tablet?"
                                data-texto="Essa ação pode remover ou desativar o tablet {{ $tablet->idprod }}, conforme seu nível de usuário."
                                data-confirmar="Sim, excluir" data-icon="error">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                    Excluir
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
    <div class="modal fade" id="modalDetalhesTablet" tabindex="-1" role="dialog"
        aria-labelledby="modalDetalhesTabletLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">

                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="modalDetalhesTabletLabel">
                        Detalhes do Tablet
                    </h5>

                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body" id="conteudoDetalhesTablet">
                    <div class="text-center p-4">
                        Carregando...
                    </div>
                </div>

            </div>
        </div>
    </div>
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
            min-height: 310px;
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

@section('js')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function atualizarCreditosTablets() {
            document.querySelectorAll('.credito-realtime').forEach(function(elemento) {
                const idprod = elemento.dataset.idprod;

                if (!idprod) {
                    return;
                }

                const url = "{{ route('tablets.info') }}" +
                    "?idprod=" + encodeURIComponent(idprod) +
                    "&_=" + new Date().getTime();

                fetch(url, {
                        method: 'GET',
                        cache: 'no-store',
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (!data.success) {
                            return;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Atualiza crédito
                        |--------------------------------------------------------------------------
                        */
                        elemento.innerHTML = data.creditos_formatado;

                        /*
                        |--------------------------------------------------------------------------
                        | Atualiza último contato
                        |--------------------------------------------------------------------------
                        */
                        const ultimoContato = document.getElementById('ultimo-contato-' + idprod);

                        if (ultimoContato) {
                            ultimoContato.innerHTML = data.ultimo_contato;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Atualiza badge online/offline
                        |--------------------------------------------------------------------------
                        */
                        const statusBadge = document.getElementById('status-conexao-' + idprod);

                        if (statusBadge) {
                            if (data.online) {
                                statusBadge.className = 'badge badge-success';
                                statusBadge.innerHTML = '<i class="fas fa-wifi"></i> Online';
                            } else {
                                statusBadge.className = 'badge badge-danger';
                                statusBadge.innerHTML = '<i class="fas fa-plug"></i> Offline';
                            }
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Atualiza borda do card
                        |--------------------------------------------------------------------------
                        */
                        const card = document.getElementById('card-tablet-' + idprod);

                        if (card) {
                            card.classList.remove('tablet-card-online', 'tablet-card-offline');

                            if (data.online) {
                                card.classList.add('tablet-card-online');
                            } else {
                                card.classList.add('tablet-card-offline');
                            }
                        }
                    })
                    .catch(function(erro) {
                        console.log('Erro ao atualizar tablet ' + idprod, erro);
                    });
            });
        }

        document.addEventListener('click', function(event) {
            const botao = event.target.closest('.btn-detalhes-tablet');

            if (!botao) {
                return;
            }

            const url = botao.dataset.url;
            const conteudo = document.getElementById('conteudoDetalhesTablet');

            conteudo.innerHTML = `
            <div class="text-center p-4">
                <i class="fas fa-spinner fa-spin"></i>
                Carregando detalhes...
            </div>
        `;

            $('#modalDetalhesTablet').modal('show');

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success && data.html) {
                        conteudo.innerHTML = data.html;
                        return;
                    }

                    conteudo.innerHTML = `
                    <div class="alert alert-warning mb-0">
                        Não foi possível carregar os detalhes do tablet.
                    </div>
                `;
                })
                .catch(function() {
                    conteudo.innerHTML = `
                    <div class="alert alert-danger mb-0">
                        Erro ao carregar detalhes do tablet.
                    </div>
                `;
                });
        });

        document.addEventListener('submit', function(event) {
            const form = event.target;

            if (!form.classList.contains('form-confirmar-acao')) {
                return;
            }

            event.preventDefault();

            const titulo = form.dataset.titulo || 'Tem certeza?';
            const texto = form.dataset.texto || 'Essa ação será executada.';
            const confirmButtonText = form.dataset.confirmar || 'Sim, confirmar';
            const icon = form.dataset.icon || 'warning';

            Swal.fire({
                title: titulo,
                text: texto,
                icon: icon,
                showCancelButton: true,
                confirmButtonText: confirmButtonText,
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            atualizarCreditosTablets();

            setInterval(function() {
                atualizarCreditosTablets();
            }, 5000);
        });
    </script>
@stop
