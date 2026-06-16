@extends('adminlte::page')

@section('title', 'Editar Tablet')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Editar Tablet</h1>

        <a href="{{ route('tablets.index') }}" class="btn btn-secondary">
            Voltar
        </a>
    </div>
@stop

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Ops!</strong> Corrija os campos abaixo.

            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('tablets.update', $tablet->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Configurações do Tablet
                </h3>
            </div>

            <div class="card-body">

                <div class="row">

                    {{-- COLUNA 1 --}}
                    <div class="col-md-4">

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Tablet</span>
                                </div>

                                <input
                                    type="text"
                                    class="form-control"
                                    value="{{ $tablet->idprod }}"
                                    readonly
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Centavos p/ Bingo</span>
                                </div>

                                <select name="centavosbingo" id="centavosbingo" class="form-control">
                                    <option value="1" {{ old('centavosbingo', $tablet->centavosbingo) == 1 ? 'selected' : '' }}>1</option>
                                    <option value="10" {{ old('centavosbingo', $tablet->centavosbingo) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ old('centavosbingo', $tablet->centavosbingo) == 25 ? 'selected' : '' }}>25</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Apos. Max. Halloween</span>
                                </div>

                                <select name="apostamaxhalloween" id="apostamaxhalloween" class="form-control">
                                    <option value="10" {{ old('apostamaxhalloween', $tablet->apostamaxhalloween) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="20" {{ old('apostamaxhalloween', $tablet->apostamaxhalloween) == 20 ? 'selected' : '' }}>20</option>
                                    <option value="30" {{ old('apostamaxhalloween', $tablet->apostamaxhalloween) == 30 ? 'selected' : '' }}>30</option>
                                    <option value="40" {{ old('apostamaxhalloween', $tablet->apostamaxhalloween) == 40 ? 'selected' : '' }}>40</option>
                                    <option value="50" {{ old('apostamaxhalloween', $tablet->apostamaxhalloween) == 50 ? 'selected' : '' }}>50</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    {{-- COLUNA 2 --}}
                    <div class="col-md-4">

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Pendrive Nº</span>
                                </div>

                                <input
                                    type="number"
                                    min="1"
                                    max="100"
                                    name="pendrive_id"
                                    class="form-control"
                                    value="{{ old('pendrive_id', $tablet->pendrive_id) }}"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Pode Zerar</span>
                                </div>

                                <select name="zerar" id="zerar" class="form-control">
                                    <option value="0" {{ old('zerar', $tablet->zerar) == 0 ? 'selected' : '' }}>Não</option>
                                    <option value="1" {{ old('zerar', $tablet->zerar) == 1 ? 'selected' : '' }}>Sim</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Destravado</span>
                                </div>

                                <select name="destrava" id="destrava" class="form-control">
                                    <option value="0" {{ old('destrava', $tablet->destrava) == 0 ? 'selected' : '' }}>Não</option>
                                    <option value="1" {{ old('destrava', $tablet->destrava) == 1 ? 'selected' : '' }}>Acumulado 1</option>
                                    <option value="2" {{ old('destrava', $tablet->destrava) == 2 ? 'selected' : '' }}>Acumulado 2</option>
                                    <option value="3" {{ old('destrava', $tablet->destrava) == 3 ? 'selected' : '' }}>Acumulado 3</option>
                                    <option value="4" {{ old('destrava', $tablet->destrava) == 4 ? 'selected' : '' }}>Acumulado 4</option>
                                    <option value="5" {{ old('destrava', $tablet->destrava) == 5 ? 'selected' : '' }}>Acumulado 5</option>
                                    <option value="6" {{ old('destrava', $tablet->destrava) == 6 ? 'selected' : '' }}>Acumulado 6</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Matemática Slot</span>
                                </div>

                                <select name="matematica_slot" id="matematicaSlot" class="form-control">
                                    {{-- <option value="0" {{ old('matematica_slot', $tablet->matematica_slot) == 0 ? 'selected' : '' }}>Nova</option> --}}
                                    <option value="1" {{ old('matematica_slot', $tablet->matematica_slot) == 1 ? 'selected' : '' }}>Antiga</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group" id="grupoDificuldade">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Dificuldade</span>
                                </div>

                                <input
                                    type="number"
                                    min="35"
                                    max="95"
                                    name="dificuldade"
                                    id="dificuldadeTablet"
                                    class="form-control"
                                    value="{{ old('dificuldade', $tablet->dificuldade) }}"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group" id="grupoModo">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Modo</span>
                                </div>

                                <select name="modo_slot" id="modoTablet" class="form-control">
                                    <option value="1" {{ old('modo_slot', $tablet->modo_slot) == 1 ? 'selected' : '' }}>1</option>
                                    <option value="2" {{ old('modo_slot', $tablet->modo_slot) == 2 ? 'selected' : '' }}>2</option>
                                    <option value="3" {{ old('modo_slot', $tablet->modo_slot) == 3 ? 'selected' : '' }}>3</option>
                                    <option value="4" {{ old('modo_slot', $tablet->modo_slot) == 4 ? 'selected' : '' }}>4</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">ZERAR Leitura parc.</span>
                                </div>

                                <select name="zerarleituraparcial" id="zerarleituraparcial" class="form-control">
                                    <option value="1" {{ old('zerarleituraparcial', $tablet->zerarleituraparcial) == 1 ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('zerarleituraparcial', $tablet->zerarleituraparcial) == 0 ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    {{-- COLUNA 3 --}}
                    <div class="col-md-4">

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Ponto</span>
                                </div>

                                <select name="id_ponto" id="ponto" class="form-control">
                                    @if ($tablet->id_ponto)
                                        <option value="{{ $tablet->id_ponto }}" selected>
                                            {{ $tablet->ponto_nome ?? 'Ponto atual' }}
                                        </option>
                                    @endif

                                    @foreach ($pontos as $ponto)
                                        <option value="{{ $ponto->id }}" {{ old('id_ponto') == $ponto->id ? 'selected' : '' }}>
                                            {{ $ponto->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">% Acumulado</span>
                                </div>

                                <input
                                    type="number"
                                    step="any"
                                    min="1"
                                    max="3"
                                    name="porcent_acumu"
                                    class="form-control"
                                    value="{{ old('porcent_acumu', $tablet->porcent_acumu) }}"
                                    required
                                >
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Cred. por Teclado?</span>
                                </div>

                                <select name="creditoteclado" id="creditoteclado" class="form-control">
                                    <option value="0" {{ old('creditoteclado', $tablet->creditoteclado) == 0 ? 'selected' : '' }}>Não</option>
                                    <option value="1" {{ old('creditoteclado', $tablet->creditoteclado) == 1 ? 'selected' : '' }}>Sim</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">ZERAR Ofic. Backup</span>
                                </div>

                                <select name="zerarleituraoficialbackup" id="zerarleituraoficialbackup" class="form-control">
                                    <option value="1" {{ old('zerarleituraoficialbackup', $tablet->zerarleituraoficialbackup) == 1 ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('zerarleituraoficialbackup', $tablet->zerarleituraoficialbackup) == 0 ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Leit. Online ou Sincr.</span>
                                </div>

                                <select name="leituraonlinesincronizada" id="leituraonlinesincronizada" class="form-control">
                                    <option value="1" {{ old('leituraonlinesincronizada', $tablet->leituraonlinesincronizada) == 1 ? 'selected' : '' }}>Sim</option>
                                    <option value="0" {{ old('leituraonlinesincronizada', $tablet->leituraonlinesincronizada) == 0 ? 'selected' : '' }}>Não</option>
                                </select>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>

        {{-- ACUMULADOS --}}
        <div class="card">
            <div class="card-body bg-light">

                <div class="row">

                    {{-- Acumulado 1 (4 MAG) -> acum4 --}}
                    @include('tablets.partials.acumulado-card', [
                        'titulo' => 'Acumulado 1 (4 MAG)',
                        'prefixo' => 'acum4',
                        'tablet' => $tablet
                    ])

                    {{-- Acumulado 2 (4 ABO) -> acum1 --}}
                    @include('tablets.partials.acumulado-card', [
                        'titulo' => 'Acumulado 2 (4 ABO)',
                        'prefixo' => 'acum1',
                        'tablet' => $tablet
                    ])

                    {{-- Acumulado 3 (5 CAP) -> acum2 --}}
                    @include('tablets.partials.acumulado-card', [
                        'titulo' => 'Acumulado 3 (5 CAP)',
                        'prefixo' => 'acum2',
                        'tablet' => $tablet
                    ])

                    {{-- Acumulado 4 (5 ABO) -> acum3 --}}
                    @include('tablets.partials.acumulado-card', [
                        'titulo' => 'Acumulado 4 (5 ABO)',
                        'prefixo' => 'acum3',
                        'tablet' => $tablet
                    ])

                    {{-- Acumulado 5 (Jackpot) -> acum5 --}}
                    @include('tablets.partials.acumulado-card', [
                        'titulo' => 'Acumulado 5 (Jackpot)',
                        'prefixo' => 'acum5',
                        'tablet' => $tablet
                    ])

                    {{-- Acumulado 6 (Bingo) -> acum6 --}}
                    @include('tablets.partials.acumulado-card', [
                        'titulo' => 'Acumulado 6 (Bingo)',
                        'prefixo' => 'acum6',
                        'tablet' => $tablet
                    ])

                </div>

            </div>
        </div>

        <div class="mb-4 text-right">
            <a href="{{ route('tablets.index') }}" class="btn btn-secondary">
                Cancelar
            </a>

            <button type="submit" class="btn btn-primary">
                Salvar
            </button>
        </div>

    </form>

@stop

@section('js')
    @include('partials.sweetalert')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const matematicaSlot = document.getElementById('matematicaSlot');
            const grupoDificuldade = document.getElementById('grupoDificuldade');
            const grupoModo = document.getElementById('grupoModo');
            const dificuldadeTablet = document.getElementById('dificuldadeTablet');

            function atualizarMatematicaSlot() {
                const matematica = parseInt(matematicaSlot.value || 0);

                if (matematica === 1) {
                    grupoDificuldade.style.display = 'block';
                    grupoModo.style.display = 'none';
                } else {
                    grupoDificuldade.style.display = 'none';
                    grupoModo.style.display = 'block';
                }
            }

            if (dificuldadeTablet) {
                dificuldadeTablet.addEventListener('change', function () {
                    const max = parseInt(dificuldadeTablet.getAttribute('max'));
                    const min = parseInt(dificuldadeTablet.getAttribute('min'));
                    const valor = parseInt(dificuldadeTablet.value || 0);

                    if (valor > max) {
                        dificuldadeTablet.value = max;
                    }

                    if (valor < min) {
                        dificuldadeTablet.value = min;
                    }
                });
            }

            matematicaSlot.addEventListener('change', atualizarMatematicaSlot);

            atualizarMatematicaSlot();
        });
    </script>
@stop