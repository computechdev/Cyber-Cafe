@extends('adminlte::page')

@section('title', 'Pontos')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Pontos</h1>

        <a href="{{ route('pontos.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo Ponto
        </a>
    </div>
@stop

@section('content')

    <div class="card">
        <div class="card-body table-responsive p-0">

            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Nome</th>
                        <th>Porcentagem</th>
                        <th>Status</th>
                        <th width="160">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($pontos as $ponto)
                        <tr>
                            <td>{{ $ponto->id }}</td>
                            <td>{{ $ponto->cliente_nome ?? '-' }}</td>
                            <td>{{ $ponto->nome }}</td>
                            <td>{{ $ponto->porcent_ponto }}</td>
                            <td>
                                @if ((int) $ponto->status === 1)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-danger">Bloqueado</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('pontos.edit', $ponto->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('pontos.toggle-status', $ponto->id) }}" method="POST"
                                    class="d-inline form-confirmacao" data-titulo="Alterar status?"
                                    data-texto="Deseja realmente alterar o status deste ponto?" data-botao="Sim, alterar">
                                    @csrf
                                    @method('PATCH')

                                    @if ((int) $ponto->status === 1)
                                        <button type="submit" class="btn btn-sm btn-danger" title="Bloquear">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-success" title="Ativar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                Nenhum ponto encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        <div class="card-footer">
            {{ $pontos->links() }}
        </div>
    </div>

@stop

@section('js')
    @include('partials.sweetalert')
@stop
