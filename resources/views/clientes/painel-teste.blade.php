<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Painel Cliente Teste</title>

    <link rel="stylesheet" href="{{ asset('css/cliente-painel-teste.css') }}">
</head>

<body>

    @php
        $formatMoney = function ($valor) {
            return number_format((float) $valor, 2, ',', '.');
        };

        $formatPercent = function ($valor) {
            return number_format((float) $valor, 2, ',', '.') . '%';
        };

        $abaAtiva = $aba ?? 'contabilidade';
    @endphp

    <div class="cliente-layout">

        <form method="GET" action="{{ route('cliente.painel-teste') }}" class="top-bar">

            <input type="hidden" name="aba" value="{{ $abaAtiva }}">

            <div class="date-field">
                <input type="date" id="data_inicial" name="data_inicial" value="{{ $dataInicial }}"
                    class="date-input">

                <button type="button" class="date-calendar-button" data-target="data_inicial">
                    📅
                </button>
            </div>

            <div class="date-field">
                <input type="date" id="data_final" name="data_final" value="{{ $dataFinal }}" class="date-input">

                <button type="button" class="date-calendar-button" data-target="data_final">
                    📅
                </button>
            </div>

            {{-- <div class="periodo-field">
                <button type="button" class="today-input periodo-button" id="btnPeriodoRapido">
                    Semana atual
                </button>

                <div class="periodo-menu" id="periodoMenu">
                    <button type="button" data-periodo="hoje">Hoje</button>
                    <button type="button" data-periodo="ontem">Ontem</button>
                    <button type="button" data-periodo="semana_atual">Semana atual</button>
                    <button type="button" data-periodo="semana_anterior">Semana anterior</button>
                    <button type="button" data-periodo="mes_atual">Mês atual</button>
                    <button type="button" data-periodo="mes_anterior">Mês anterior</button>
                </div>
            </div> --}}

            <button type="submit" class="top-button">
                Filtrar
            </button>

        </form>

        <div class="tabs">

            <a href="{{ route('cliente.painel-teste', ['aba' => 'contabilidade', 'data_inicial' => $dataInicial, 'data_final' => $dataFinal]) }}"
                class="tab-item {{ $abaAtiva === 'contabilidade' ? 'active' : '' }}">
                <span class="tab-icon">👤</span>
                Contabilidade
            </a>

            <a href="{{ route('cliente.painel-teste', ['aba' => 'movimentos', 'data_inicial' => $dataInicial, 'data_final' => $dataFinal]) }}"
                class="tab-item {{ $abaAtiva === 'movimentos' ? 'active' : '' }}">
                <span class="tab-icon">📊</span>
                Movimentos
            </a>

            <a href="{{ route('cliente.painel-teste', [
                'aba' => 'itens',
                'data_inicial' => $dataInicial,
                'data_final' => $dataFinal,
                'itens_page_size' => request('itens_page_size', 10),
            ]) }}"
                class="tab-item {{ $abaAtiva === 'itens' ? 'active' : '' }}">
                <span class="tab-icon">🧾</span>
                Itens
                <a href="{{ route('cliente.painel-teste', ['aba' => 'configuracao', 'data_inicial' => $dataInicial, 'data_final' => $dataFinal]) }}"
                    class="tab-item {{ $abaAtiva === 'configuracao' ? 'active' : '' }}">
                    <span class="tab-icon">⚙️</span>
                    Configuração
                </a>

        </div>

        <main class="content-area">

            @if ($abaAtiva === 'contabilidade')
                <section class="client-panel-section">

                    <table class="client-table">
                        <thead>
                            <tr>
                                <th>Usuário</th>
                                <th class="text-right">Entradas</th>
                                <th class="text-right">Saídas</th>
                                <th class="text-right">Diferença</th>
                                <th class="text-right">Comissão</th>
                                <th>Bill Audit</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td>{{ $resumo['usuario'] }}</td>
                                <td class="text-right">{{ $formatMoney($resumo['entradas']) }}</td>
                                <td class="text-right">{{ $formatMoney($resumo['saidas']) }}</td>
                                <td class="text-right">{{ $formatMoney($resumo['diferenca']) }}</td>
                                <td class="text-right">
                                    {{ $formatMoney($resumo['comissao_cliente'] ?? 0) }}
                                    <br>
                                    <small>{{ $formatPercent($resumo['porcentagem_cliente'] ?? 0) }}</small>
                                </td>
                                <td>-</td>
                            </tr>

                            <tr class="total-row">
                                <td></td>
                                <td class="text-right">{{ $formatMoney($resumo['entradas']) }}</td>
                                <td class="text-right">{{ $formatMoney($resumo['saidas']) }}</td>
                                <td class="text-right">{{ $formatMoney($resumo['diferenca']) }}</td>
                                <td class="text-right">
                                    {{ $formatMoney($resumo['comissao_cliente'] ?? 0) }}
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="sticky-note">
                        <strong>Conta corrente</strong>

                        <p>
                            {{ \Carbon\Carbon::parse($dataInicial)->format('d/m/Y') }}
                            {{ \Carbon\Carbon::parse($dataFinal)->format('d/m/Y') }}
                        </p>

                        <p class="note-value">{{ $formatMoney($resumo['conta_entrada']) }}</p>
                        <p class="note-value negative">- {{ $formatMoney($resumo['conta_saida']) }}</p>

                        <hr>

                        <p class="note-total">{{ $formatMoney($resumo['conta_saldo']) }}</p>
                    </div>

                    <div class="faturas-pendentes-box">
                        <h3>Faturas pendentes</h3>

                        @if ($faturasPendentes->isEmpty())
                            <p class="faturas-empty">
                                Nenhuma fatura pendente.
                            </p>
                        @else
                            <table class="client-table">
                                <thead>
                                    <tr>
                                        <th>Nº</th>
                                        <th>Fechamento</th>
                                        <th>Vencimento</th>
                                        <th class="text-right">Valor Total</th>
                                        <th class="text-right">Comissão</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($faturasPendentes as $fatura)
                                        <tr>
                                            <td>{{ $fatura->id_cobranca }}</td>

                                            <td>
                                                {{ $fatura->data_processamento ? \Carbon\Carbon::parse($fatura->data_processamento)->format('d/m/Y') : '-' }}
                                            </td>

                                            <td>
                                                {{ $fatura->data_vencimento ? \Carbon\Carbon::parse($fatura->data_vencimento)->format('d/m/Y') : '-' }}
                                            </td>

                                            <td class="text-right">
                                                {{ $formatMoney($fatura->valor_total_acerto ?? $fatura->valor_total) }}
                                            </td>

                                            <td class="text-right">
                                                {{ $formatMoney($fatura->comissao_cliente ?? 0) }}
                                                <br>
                                                <small>{{ $formatPercent($fatura->porcentagem_cliente ?? 0) }}</small>
                                            </td>

                                            <td>
                                                Pendente
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </section>
            @endif

            @if ($abaAtiva === 'movimentos')

                <section class="client-panel-section">

                    <div class="sub-tabs">
                        <a href="{{ route('cliente.painel-teste', [
                            'aba' => 'movimentos',
                            'subaba' => 'resumo',
                            'data_inicial' => $dataInicial,
                            'data_final' => $dataFinal,
                        ]) }}"
                            class="sub-tab {{ $subAbaMovimentos === 'resumo' ? 'active' : '' }}">
                            Resumo
                        </a>

                        <a href="{{ route('cliente.painel-teste', [
                            'aba' => 'movimentos',
                            'subaba' => 'detalhe',
                            'data_inicial' => $dataInicial,
                            'data_final' => $dataFinal,
                        ]) }}"
                            class="sub-tab {{ $subAbaMovimentos === 'detalhe' ? 'active' : '' }}">
                            Detalhe
                        </a>
                    </div>

                    @if ($subAbaMovimentos === 'resumo')

                        <table class="client-table movimento-resumo-table">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th class="text-right">Entradas</th>
                                    <th class="text-right">Saídas</th>
                                    <th class="text-right">Diferença</th>
                                    <th class="text-right">Comissão</th>
                                    <th class="text-right">%</th>
                                    <th>Data</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($movimentos as $movimento)
                                    <tr>
                                        <td>{{ $usuario->username ?? $usuario->name }}</td>

                                        <td class="text-right">
                                            {{ number_format((float) $movimento->entradas, 2, ',', '.') }}
                                        </td>

                                        <td class="text-right">
                                            {{ number_format((float) $movimento->saidas, 2, ',', '.') }}
                                        </td>

                                        <td class="text-right">
                                            {{ number_format((float) $movimento->diferenca, 2, ',', '.') }}
                                        </td>

                                        <td class="text-right">
                                            {{ number_format((float) ($movimento->comissao_cliente ?? 0), 2, ',', '.') }}
                                            <br>
                                            <small>
                                                {{ number_format((float) ($movimento->porcentagem_cliente ?? 0), 2, ',', '.') }}%
                                            </small>
                                        </td>

                                        <td class="text-right">
                                            {{ number_format((float) $movimento->porcentagem, 0, ',', '.') }}%
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($movimento->data_movimento)->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="empty-row">
                                            Nenhum movimento encontrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="client-pagination">

                            @if ($movimentos->onFirstPage())
                                <button disabled>⏮</button>
                                <button disabled>◀</button>
                            @else
                                <a href="{{ $movimentos->url(1) }}">⏮</a>
                                <a href="{{ $movimentos->previousPageUrl() }}">◀</a>
                            @endif

                            @for ($pagina = 1; $pagina <= $movimentos->lastPage(); $pagina++)
                                <a href="{{ $movimentos->url($pagina) }}"
                                    class="{{ $movimentos->currentPage() == $pagina ? 'active' : '' }}">
                                    {{ $pagina }}
                                </a>
                            @endfor

                            @if ($movimentos->hasMorePages())
                                <a href="{{ $movimentos->nextPageUrl() }}">▶</a>
                                <a href="{{ $movimentos->url($movimentos->lastPage()) }}">⏭</a>
                            @else
                                <button disabled>▶</button>
                                <button disabled>⏭</button>
                            @endif

                            <form method="GET" action="{{ route('cliente.painel-teste') }}" class="page-size-form"
                                style="display:inline-flex; align-items:center; gap:6px; margin-left:12px;">
                                <input type="hidden" name="aba" value="movimentos">
                                <input type="hidden" name="subaba" value="resumo">
                                <input type="hidden" name="data_inicial" value="{{ $dataInicial }}">
                                <input type="hidden" name="data_final" value="{{ $dataFinal }}">

                                <span class="page-size-label">Page size:</span>

                                <select name="resumo_page_size" onchange="this.form.submit()">
                                    <option value="10"
                                        {{ (int) request('resumo_page_size', 10) === 10 ? 'selected' : '' }}>
                                        10
                                    </option>

                                    <option value="25"
                                        {{ (int) request('resumo_page_size', 10) === 25 ? 'selected' : '' }}>
                                        25
                                    </option>

                                    <option value="50"
                                        {{ (int) request('resumo_page_size', 10) === 50 ? 'selected' : '' }}>
                                        50
                                    </option>

                                    <option value="100"
                                        {{ (int) request('resumo_page_size', 10) === 100 ? 'selected' : '' }}>
                                        100
                                    </option>
                                </select>
                            </form>

                            <span class="items-count">
                                {{ $movimentos->total() }}
                                itens in
                                {{ $movimentos->lastPage() }}
                                pages
                            </span>
                        </div>

                    @endif

                    @if ($subAbaMovimentos === 'detalhe')

                        <table class="client-table movimento-detalhe-table">
                            <thead>
                                <tr>
                                    <th>Session Id</th>
                                    <th>Data</th>
                                    <th>ID</th>
                                    <th class="text-right">Entradas</th>
                                    <th class="text-right">Saídas</th>
                                    <th class="text-right">Tipo</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($movimentosDetalhe as $detalhe)
                                    <tr>
                                        <td></td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($detalhe->data_hora)->format('d/m/y H:i:s') }}
                                        </td>

                                        <td>
                                            {{ $detalhe->id }}
                                            {{ $usuario->username ?? $usuario->name }}
                                        </td>

                                        <td class="text-right">
                                            @if ($detalhe->entrada > 0)
                                                $ {{ number_format($detalhe->entrada, 2, ',', '.') }}
                                            @endif
                                        </td>

                                        <td class="text-right">
                                            @if ($detalhe->saida > 0)
                                                $ {{ number_format($detalhe->saida, 2, ',', '.') }}
                                            @endif
                                        </td>

                                        <td class="text-right">
                                            {{ $detalhe->tipo_nome }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="empty-row">
                                            Nenhum detalhe encontrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        <div class="client-pagination">

                            @if ($movimentosDetalhe->onFirstPage())
                                <button disabled>⏮</button>
                                <button disabled>◀</button>
                            @else
                                <a href="{{ $movimentosDetalhe->url(1) }}">⏮</a>
                                <a href="{{ $movimentosDetalhe->previousPageUrl() }}">◀</a>
                            @endif

                            @for ($pagina = 1; $pagina <= $movimentosDetalhe->lastPage(); $pagina++)
                                <a href="{{ $movimentosDetalhe->url($pagina) }}"
                                    class="{{ $movimentosDetalhe->currentPage() == $pagina ? 'active' : '' }}">
                                    {{ $pagina }}
                                </a>
                            @endfor

                            @if ($movimentosDetalhe->hasMorePages())
                                <a href="{{ $movimentosDetalhe->nextPageUrl() }}">▶</a>
                                <a href="{{ $movimentosDetalhe->url($movimentosDetalhe->lastPage()) }}">⏭</a>
                            @else
                                <button disabled>▶</button>
                                <button disabled>⏭</button>
                            @endif

                            <form method="GET" action="{{ route('cliente.painel-teste') }}" class="page-size-form"
                                style="display:inline-flex; align-items:center; gap:6px; margin-left:12px;">
                                <input type="hidden" name="aba" value="movimentos">
                                <input type="hidden" name="subaba" value="detalhe">
                                <input type="hidden" name="data_inicial" value="{{ $dataInicial }}">
                                <input type="hidden" name="data_final" value="{{ $dataFinal }}">

                                <span class="page-size-label">Page size:</span>

                                <select name="detalhe_page_size" onchange="this.form.submit()">
                                    <option value="10"
                                        {{ (int) request('detalhe_page_size', 10) === 10 ? 'selected' : '' }}>
                                        10
                                    </option>

                                    <option value="25"
                                        {{ (int) request('detalhe_page_size', 10) === 25 ? 'selected' : '' }}>
                                        25
                                    </option>

                                    <option value="50"
                                        {{ (int) request('detalhe_page_size', 10) === 50 ? 'selected' : '' }}>
                                        50
                                    </option>

                                    <option value="100"
                                        {{ (int) request('detalhe_page_size', 10) === 100 ? 'selected' : '' }}>
                                        100
                                    </option>
                                </select>
                            </form>

                            <span class="items-count">
                                {{ $movimentosDetalhe->total() }}
                                itens in
                                {{ $movimentosDetalhe->lastPage() }}
                                pages
                            </span>

                        </div>

                    @endif

                </section>

            @endif

            @if ($abaAtiva === 'itens')

                <section class="client-panel-section">

                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>Jogo ID</th>
                                <th>Fecha</th>
                                <th>Imagem</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($itens as $item)
                                <tr>
                                    <td>
                                        {{ $item->jogo_id }} &lt; {{ $usuario->username ?? $usuario->name }}<br>

                                        <span class="credito-jogador-realtime"
                                            id="credito-jogador-{{ $item->idprod }}"
                                            data-idprod="{{ $item->idprod }}">
                                            {{ $item->credito_texto }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        {{ $item->fecha }}<br>
                                        ⓘ
                                    </td>

                                    <td>
                                        <div class="fake-game-image">
                                            EM BREVE
                                            {{-- <span>{{ $item->idprod }}</span> --}}
                                        </div>
                                    </td>

                                    <td class="text-right">
                                        {{ $item->total_1 }}<br>
                                        ({{ $item->total_2 }})
                                        <br>
                                        {{ $item->total_3 }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="empty-row">
                                        Nenhum tablet encontrado para este cliente.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="client-pagination">

                        @if ($itens->onFirstPage())
                            <button disabled>⏮</button>
                            <button disabled>◀</button>
                        @else
                            <a href="{{ $itens->url(1) }}">⏮</a>
                            <a href="{{ $itens->previousPageUrl() }}">◀</a>
                        @endif

                        @for ($pagina = 1; $pagina <= $itens->lastPage(); $pagina++)
                            <a href="{{ $itens->url($pagina) }}"
                                class="{{ $itens->currentPage() == $pagina ? 'active' : '' }}">
                                {{ $pagina }}
                            </a>
                        @endfor

                        @if ($itens->hasMorePages())
                            <a href="{{ $itens->nextPageUrl() }}">▶</a>
                            <a href="{{ $itens->url($itens->lastPage()) }}">⏭</a>
                        @else
                            <button disabled>▶</button>
                            <button disabled>⏭</button>
                        @endif

                        <form method="GET" action="{{ route('cliente.painel-teste') }}" class="page-size-form"
                            style="display:inline-flex; align-items:center; gap:6px; margin-left:12px;">
                            <input type="hidden" name="aba" value="itens">
                            <input type="hidden" name="data_inicial" value="{{ $dataInicial }}">
                            <input type="hidden" name="data_final" value="{{ $dataFinal }}">

                            <span class="page-size-label">Page size:</span>

                            <select name="itens_page_size" onchange="this.form.submit()">
                                <option value="10"
                                    {{ (int) request('itens_page_size', 10) === 10 ? 'selected' : '' }}>
                                    10
                                </option>

                                <option value="25"
                                    {{ (int) request('itens_page_size', 10) === 25 ? 'selected' : '' }}>
                                    25
                                </option>

                                <option value="50"
                                    {{ (int) request('itens_page_size', 10) === 50 ? 'selected' : '' }}>
                                    50
                                </option>

                                <option value="100"
                                    {{ (int) request('itens_page_size', 10) === 100 ? 'selected' : '' }}>
                                    100
                                </option>
                            </select>
                        </form>

                        <span class="items-count">
                            Item {{ $itens->firstItem() ?? 0 }}
                            to {{ $itens->lastItem() ?? 0 }}
                            of {{ $itens->total() }}
                        </span>

                        <span class="items-count">
                            {{ $itens->total() }}
                            itens in
                            {{ $itens->lastPage() }}
                            pages
                        </span>

                    </div>
                </section>

            @endif

            @if ($abaAtiva === 'configuracao')
                <section class="client-panel-section configuracao-panel">

                    <div class="config-header">
                        Administração senhas
                    </div>

                    <div class="config-content">

                        <form method="POST" action="{{ route('cliente.painel.alterar-senha') }}"
                            class="password-admin-form">
                            @csrf

                            <input type="hidden" name="data_inicial" value="{{ $dataInicial }}">
                            <input type="hidden" name="data_final" value="{{ $dataFinal }}">

                            <div class="password-row">
                                <label>Usuario</label>

                                <input type="text" value="{{ $usuario->username ?? $usuario->name }}" readonly
                                    class="password-input readonly">
                            </div>

                            <div class="password-row">
                                <label>Senha atual</label>

                                <input type="password" name="senha_atual" class="password-input"
                                    autocomplete="current-password">
                            </div>

                            <div class="password-row">
                                <label>Nova senha</label>

                                <input type="password" name="nova_senha" class="password-input"
                                    autocomplete="new-password">
                            </div>

                            <div class="password-row">
                                <label>Reescrever nova senha</label>

                                <div class="password-save-group">
                                    <input type="password" name="nova_senha_confirmacao" class="password-input"
                                        autocomplete="new-password">

                                    <button type="submit" class="password-save-button">
                                        Salvar
                                    </button>
                                </div>
                            </div>

                        </form>

                    </div>

                </section>
            @endif
        </main>

        <footer class="bottom-bar">
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="footer-button">
                    Logout
                </button>
            </form>

            <a href="{{ route('cliente.painel-teste', [
                'aba' => 'movimentos',
                'subaba' => 'detalhe',
                'data_inicial' => $dataInicial,
                'data_final' => $dataFinal,
                'detalhe_page_size' => request('detalhe_page_size', 10),
            ]) }}"
                class="footer-button refresh-button">
                Refresh 🔄
            </a>
        </footer>

    </div>

    <script>
        document.addEventListener('click', function(event) {
            const botao = event.target.closest('.date-calendar-button');

            if (!botao) {
                return;
            }

            const targetId = botao.dataset.target;
            const input = document.getElementById(targetId);

            if (!input) {
                return;
            }

            if (typeof input.showPicker === 'function') {
                input.showPicker();
            } else {
                input.focus();
                input.click();
            }
        });
    </script>

    <script>
        function atualizarCreditosJogadores() {
            document.querySelectorAll('.credito-jogador-realtime').forEach(function(elemento) {
                const idprod = elemento.dataset.idprod;

                if (!idprod) {
                    return;
                }

                const url = "{{ route('cliente.credito-jogador-realtime') }}" +
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

                        elemento.innerHTML = data.creditos_texto;
                    })
                    .catch(function(erro) {
                        console.log('Erro ao atualizar crédito do jogador ' + idprod, erro);
                    });
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            atualizarCreditosJogadores();

            setInterval(function() {
                atualizarCreditosJogadores();
            }, 2000);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        @if (session('swal_error'))
            Swal.fire({
                icon: 'error',
                title: 'Atenção',
                text: @json(session('swal_error')),
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
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonText: 'OK'
            });
        @endif
    </script>
</body>

</html>
