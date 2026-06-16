@extends('adminlte::page')

@section('title', 'Contas a Receber')

@section('content_header')
    <h1>Contas a Receber</h1>
@stop

@section('content')

    @php
        $formatMoney = function ($valor) {
            return 'R$ ' . number_format((float) $valor, 2, ',', '.');
        };
    @endphp

    <div class="card">
        <div class="card-header">
            <strong>Filtros</strong>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('contas-receber.index') }}">
                <div class="row">
                    <div class="col-md-5">
                        <label>Cliente</label>

                        <select name="cliente_id" class="form-control">
                            <option value="">Todos</option>

                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}"
                                    {{ (string) $clienteId === (string) $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->name }} {{ $cliente->username ? '(' . $cliente->username . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Status</label>

                        <select name="status" class="form-control">
                            <option value="pendente" {{ $status === 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="pago" {{ $status === 'pago' ? 'selected' : '' }}>Pago</option>
                            <option value="todos" {{ $status === 'todos' ? 'selected' : '' }}>Todos</option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Pesquisar
                        </button>

                        <a href="{{ route('contas-receber.fechar.index') }}" class="btn btn-success ml-2">
                            <i class="fas fa-lock"></i>
                            Fechar Fatura Aberta
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-dark d-flex justify-content-between align-items-center">
            <strong>Faturas</strong>

            <span class="badge badge-light">
                {{ $contas->total() }} encontrada(s)
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Nº</th>
                            <th>Cliente</th>
                            <th>Processamento</th>
                            <th>Vencimento</th>
                            <th class="text-right">Valor</th>
                            <th>Status</th>
                            <th>Pagamento</th>
                            <th width="180">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($contas as $conta)
                            <tr>
                                <td>{{ $conta->id_cobranca }}</td>

                                <td>
                                    {{ $conta->cliente_nome }}
                                    @if ($conta->cliente_username)
                                        <br>
                                        <small>{{ $conta->cliente_username }}</small>
                                    @endif
                                </td>

                                <td>
                                    {{ $conta->data_processamento ? \Carbon\Carbon::parse($conta->data_processamento)->format('d/m/Y H:i') : '-' }}
                                </td>

                                <td>
                                    {{ $conta->data_vencimento ? \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') : '-' }}
                                </td>

                                <td class="text-right">
                                    {{ $formatMoney($conta->valor_total) }}
                                </td>

                                <td>
                                    @if ((int) $conta->pago === 1)
                                        <span class="badge badge-success">Pago</span>
                                    @else
                                        <span class="badge badge-warning">Pendente</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $conta->data_pagamento ? \Carbon\Carbon::parse($conta->data_pagamento)->format('d/m/Y') : '-' }}
                                </td>

                                <td>
                                    @if ((int) $conta->pago === 0)
                                        <form method="POST"
                                            action="{{ route('contas-receber.marcar-pago', $conta->id_cobranca) }}"
                                            class="d-inline form-confirmar-acao" data-titulo="Marcar como pago?"
                                            data-texto="Confirmar baixa da fatura nº {{ $conta->id_cobranca }}?"
                                            data-confirmar="Sim, marcar pago" data-icon="warning">
                                            @csrf

                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-money-bill-wave"></i>
                                                Baixar Pagamento
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="fas fa-check-circle"></i>
                                            Pago
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center p-4">
                                    Nenhuma cobrança encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($contas->hasPages())
            <div class="card-footer">
                {{ $contas->links() }}
            </div>
        @endif
    </div>

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
    </script>
@stop
