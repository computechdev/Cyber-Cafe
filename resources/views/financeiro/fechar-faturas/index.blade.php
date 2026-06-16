@extends('adminlte::page')

@section('title', 'Fechar Faturas')

@section('content_header')
    <h1>Fechar Faturas</h1>
@stop

@section('content')

    @php
        $formatMoney = function ($valor) {
            return 'R$ ' . number_format((float) $valor, 2, ',', '.');
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
                        <button type="submit" class="btn btn-primary">
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
                <strong>Leituras abertas de {{ $cliente->name }}</strong>
            </div>

            <div class="card-body">

                @if (isset($cobrancasAbertas) && $cobrancasAbertas->isNotEmpty())
                    <div class="alert alert-info">
                        <strong>Cobranças abertas encontradas:</strong>

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
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h4>{{ $formatMoney($totais['entrada']) }}</h4>
                                    <p>Entrada</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h4>{{ $formatMoney($totais['saida']) }}</h4>
                                    <p>Saída</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h4>{{ $formatMoney($totais['saldo']) }}</h4>
                                    <p>Saldo para fatura</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tablet</th>
                                    <th>Ponto</th>
                                    <th>Data</th>
                                    <th class="text-right">Entrada</th>
                                    <th class="text-right">Saída</th>
                                    <th class="text-right">Saldo</th>
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
                                        <td class="text-right">{{ $formatMoney($metrica->entrada) }}</td>
                                        <td class="text-right">{{ $formatMoney($metrica->saida) }}</td>
                                        <td class="text-right">{{ $formatMoney($metrica->saldo_total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <form method="POST" action="{{ route('contas-receber.fechar.store') }}" class="form-confirmar-acao"
                        data-titulo="Fechar fatura?"
                        data-texto="Será gerada uma cobrança para {{ $cliente->name }} no valor de {{ $formatMoney($totais['saldo']) }}."
                        data-confirmar="Sim, fechar fatura" data-icon="warning">
                        @csrf

                        <input type="hidden" name="cliente_id" value="{{ $cliente->id }}">

                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-lock"></i>
                            Fechar Fatura
                        </button>
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
