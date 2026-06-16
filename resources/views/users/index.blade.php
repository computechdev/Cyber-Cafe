@extends('adminlte::page')

@section('title', 'Usuários')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Usuários</h1>

        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Novo usuário
        </a>
    </div>
@stop

@section('content')

    <div class="card">
        <div class="card-header">
            <form method="GET" action="{{ route('usuarios.index') }}" class="row">

                <div class="col-md-4">
                    <label>Nível</label>
                    <select name="nivel" class="form-control">
                        <option value="">Todos</option>
                        <option value="1" {{ $nivel == 1 ? 'selected' : '' }}>Administrador</option>
                        <option value="2" {{ $nivel == 2 ? 'selected' : '' }}>Subadmin</option>
                        <option value="3" {{ $nivel == 3 ? 'selected' : '' }}>Cliente</option>
                        <option value="4" {{ $nivel == 4 ? 'selected' : '' }}>Sócio</option>
                        <option value="5" {{ $nivel == 5 ? 'selected' : '' }}>Funcionário</option>
                        <option value="6" {{ $nivel == 6 ? 'selected' : '' }}>Operador</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-success mr-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>

                    <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">
                        Limpar
                    </a>
                </div>

            </form>
        </div>

        <div class="card-body table-responsive p-0">

            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Usuário</th>
                        <th>E-mail</th>
                        <th>Nível</th>
                        <th>Status</th>
                        <th width="160">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->id }}</td>
                            <td>{{ $usuario->name }}</td>
                            <td>{{ $usuario->username }}</td>
                            <td>{{ $usuario->email }}</td>

                            <td>
                                @switch((int) $usuario->nivel)
                                    @case(1)
                                        <span class="badge badge-danger">Administrador</span>
                                    @break

                                    @case(2)
                                        <span class="badge badge-warning">Subadmin</span>
                                    @break

                                    @case(3)
                                        <span class="badge badge-primary">Cliente</span>
                                    @break

                                    @case(4)
                                        <span class="badge badge-info">Sócio</span>
                                    @break

                                    @case(5)
                                        <span class="badge badge-success">Funcionário</span>
                                    @break

                                    @case(6)
                                        <span class="badge badge-secondary">Operador</span>
                                    @break

                                    @default
                                        <span class="badge badge-dark">Não definido</span>
                                @endswitch
                            </td>

                            <td>
                                @if ((int) $usuario->status === 1)
                                    <span class="badge badge-success">Ativo</span>
                                @else
                                    <span class="badge badge-danger">Inativo</span>
                                @endif
                            </td>

                            <td>
                                <a href="#" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>

                                <a href="{{ route('usuarios.edit', $usuario->id) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ route('usuarios.toggle-status', $usuario->id) }}" method="POST"
                                    class="d-inline form-confirmacao" data-titulo="Alterar status?"
                                    data-texto="Deseja realmente alterar o status deste usuário?" data-botao="Sim, alterar">
                                    @csrf
                                    @method('PATCH')

                                    @if ((int) $usuario->status === 1)
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
                                <td colspan="7" class="text-center">
                                    Nenhum usuário encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>

            <div class="card-footer">
                {{ $usuarios->links() }}
            </div>
        </div>

    @stop
    @section('js')
        @include('partials.sweetalert')
    @stop
