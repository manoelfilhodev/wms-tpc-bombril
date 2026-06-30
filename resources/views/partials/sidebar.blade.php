<div class="leftside-menu">
    @php
        $operacaoAtiva = request()->routeIs(
            'demandas.dashboardOperacional',
            'demandas.operacional',
            'expedicao.apontamentos-operacionais.*',
            'expedicao.previsibilidade.*',
            'expedicao.saida-veiculos.*',
            'expedicao.timeline-dts.*',
            'stretch.*'
        );
        $importsAtivo = request()->routeIs(
            'demandas.import',
            'demandas.import.view',
            'expedicao.importacao-programacao.*',
            'expedicao.importacao-veiculos-presentes.*',
            'clientes-transit-time.import',
            'clientes-transit-time.import.*'
        );
        $relatoriosAtivo = request()->routeIs('demandas.relatorios');
        $administracaoAtiva = request()->routeIs(
            'usuarios.*',
            'separadores.*',
            'dispositivos.*',
            'clientes-transit-time.*',
            'admin.logs.*'
        );
    @endphp

    <!-- LOGO -->
    <a href="{{ route('dashboard') }}" class="logo text-center logo-light wms-sidebar-brand">
        <span class="logo-lg">
            <img src="{{ asset('images/logo-sem-nome.png') }}" alt="" height="80">
        </span>
        <span class="logo-sm">
            <img src="{{ asset('images/logo-sem-nome.png') }}" alt="" height="50">
        </span>
    </a>

    <div class="h-100" data-simplebar>
        <div class="leftside-menu-container">
            <ul class="side-nav">
                <li class="side-nav-title">Navegacao</li>

                <li class="side-nav-item {{ $operacaoAtiva ? 'menuitem-active' : '' }}">
                    <a data-bs-toggle="collapse" href="#sidebarOperacao" aria-expanded="{{ $operacaoAtiva ? 'true' : 'false' }}"
                        aria-controls="sidebarOperacao" class="side-nav-link {{ $operacaoAtiva ? '' : 'collapsed' }}">
                        <i class="mdi mdi-factory"></i>
                        <span> Operacao </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ $operacaoAtiva ? 'show' : '' }}" id="sidebarOperacao">
                        <ul class="side-nav-second-level">
                            <li class="{{ request()->routeIs('demandas.dashboardOperacional') ? 'active' : '' }}">
                                <a href="{{ route('demandas.dashboardOperacional') }}">
                                    <i class="uil-home-alt me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('demandas.operacional') ? 'active' : '' }}">
                                <a href="{{ route('demandas.operacional') }}">
                                    <i class="mdi mdi-format-list-bulleted-square me-1"></i> Separacao
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('expedicao.apontamentos-operacionais.*', 'expedicao.previsibilidade.*', 'expedicao.saida-veiculos.*') ? 'active' : '' }}">
                                <a href="{{ route('expedicao.apontamentos-operacionais.index') }}">
                                    <i class="mdi mdi-truck-fast-outline me-1"></i> Expedicao
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('expedicao.timeline-dts.*') ? 'active' : '' }}">
                                <a href="{{ route('expedicao.timeline-dts.index') }}">
                                    <i class="mdi mdi-timeline-clock-outline me-1"></i> Timeline
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('stretch.*') ? 'active' : '' }}">
                                <a href="{{ route('stretch.apontar') }}">
                                    <i class="mdi mdi-barcode-scan me-1"></i> Palete Stretch
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="side-nav-item {{ $importsAtivo ? 'menuitem-active' : '' }}">
                    <a data-bs-toggle="collapse" href="#sidebarImports" aria-expanded="{{ $importsAtivo ? 'true' : 'false' }}"
                        aria-controls="sidebarImports" class="side-nav-link {{ $importsAtivo ? '' : 'collapsed' }}">
                        <i class="mdi mdi-database-import-outline"></i>
                        <span> Imports </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ $importsAtivo ? 'show' : '' }}" id="sidebarImports">
                        <ul class="side-nav-second-level">
                            <li class="{{ request()->routeIs('demandas.import', 'demandas.import.view') ? 'active' : '' }}">
                                <a href="{{ route('demandas.import.view') }}">
                                    <i class="mdi mdi-file-table-outline me-1"></i> Explosao DTs
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('expedicao.importacao-programacao.*') ? 'active' : '' }}">
                                <a href="{{ route('expedicao.importacao-programacao.index') }}">
                                    <i class="mdi mdi-calendar-import me-1"></i> Programacao PROG
                                </a>
                            </li>
                            <li class="{{ request()->routeIs('expedicao.importacao-veiculos-presentes.*') ? 'active' : '' }}">
                                <a href="{{ route('expedicao.importacao-veiculos-presentes.index') }}">
                                    <i class="mdi mdi-truck-check-outline me-1"></i> Veiculos Presentes
                                </a>
                            </li>
                            @if (session('tipo') === 'admin')
                                <li class="{{ request()->routeIs('clientes-transit-time.import', 'clientes-transit-time.import.*') ? 'active' : '' }}">
                                    <a href="{{ route('clientes-transit-time.import.form') }}">
                                        <i class="mdi mdi-map-clock-outline me-1"></i> Transit Time
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </li>

                <li class="side-nav-item {{ $relatoriosAtivo ? 'menuitem-active' : '' }}">
                    <a data-bs-toggle="collapse" href="#sidebarRelatorios" aria-expanded="{{ $relatoriosAtivo ? 'true' : 'false' }}"
                        aria-controls="sidebarRelatorios" class="side-nav-link {{ $relatoriosAtivo ? '' : 'collapsed' }}">
                        <i class="mdi mdi-file-chart-outline"></i>
                        <span> Relatorios e Paineis </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ $relatoriosAtivo ? 'show' : '' }}" id="sidebarRelatorios">
                        <ul class="side-nav-second-level">
                            <li class="{{ request()->routeIs('demandas.relatorios') ? 'active' : '' }}">
                                <a href="{{ route('demandas.relatorios') }}">
                                    <i class="mdi mdi-file-chart-outline me-1"></i> Relatorios
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('painel.tv') }}" target="_blank" rel="noopener">
                                    <i class="mdi mdi-television-play me-1"></i> Painel TV
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                @if (session('tipo') === 'admin')
                    <li class="side-nav-title">Gestao</li>

                    <li class="side-nav-item {{ $administracaoAtiva ? 'menuitem-active' : '' }}">
                        <a data-bs-toggle="collapse" href="#sidebarAdministracao" aria-expanded="{{ $administracaoAtiva ? 'true' : 'false' }}"
                            aria-controls="sidebarAdministracao" class="side-nav-link {{ $administracaoAtiva ? '' : 'collapsed' }}">
                            <i class="mdi mdi-cog-outline"></i>
                            <span> Administracao </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ $administracaoAtiva ? 'show' : '' }}" id="sidebarAdministracao">
                            <ul class="side-nav-second-level">
                                <li class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                                    <a href="{{ route('usuarios.index') }}">
                                        <i class="mdi mdi-account-group-outline me-1"></i> Usuarios
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('separadores.*') ? 'active' : '' }}">
                                    <a href="{{ route('separadores.index') }}">
                                        <i class="mdi mdi-account-hard-hat me-1"></i> Separadores
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('dispositivos.*') ? 'active' : '' }}">
                                    <a href="{{ route('dispositivos.index') }}">
                                        <i class="mdi mdi-monitor-cellphone me-1"></i> Dispositivos
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('clientes-transit-time.*') ? 'active' : '' }}">
                                    <a href="{{ route('clientes-transit-time.index') }}">
                                        <i class="mdi mdi-map-clock-outline me-1"></i> Parametros Logisticos
                                    </a>
                                </li>
                                <li class="{{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                                    <a href="{{ route('admin.logs.index') }}">
                                        <i class="mdi mdi-shield-search me-1"></i> Auditoria
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
