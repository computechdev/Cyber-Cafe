@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Clientes</h1>

        <a href="{{ route('clientes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo Cliente
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
                        <th>Nome</th>
                        <th>Usuário</th>
                        <th>E-mail</th>
                        <th>Porcentagem</th>
                        <th>Validade</th>
                        <th>Status</th>
                        <th width="160">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->id }}</td>
                            <td>{{ $cliente->name }}</td>
                            <td>{{ $cliente->username }}</td>
                            <td>{{ $cliente->email }}</td>
                            <td>{{ $cliente->porcentagem }}</td>
                            <td>
                                {{ $cliente->validade ? \Carbon\Carbon::parse($cliente->validade)->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                @if ((int) $cliente->status === 1)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-danger">Inativo</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('clientes.toggle-status', $cliente->id) }}" method="POST"
                                    class="d-inline form-confirmacao" data-titulo="Alterar status?"
                                    data-texto="Deseja realmente alterar o status deste cliente?" data-botao="Sim, alterar">
                                    @csrf
                                    @method('PATCH')

                                    @if ((int) $cliente->status === 1)
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
                            <td colspan="8" class="text-center">
                                Nenhum cliente encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        <div class="card-footer">
            {{ $clientes->links() }}
        </div>
    </div>

@stop

@section('js')
    @include('partials.sweetalert')
@stop
