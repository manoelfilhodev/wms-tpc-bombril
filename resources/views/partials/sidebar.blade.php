<div class="leftside-menu">
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

                <li class="side-nav-item">
                    <a href="{{ route('demandas.dashboardOperacional') }}"
                        class="side-nav-link {{ request()->routeIs('demandas.dashboardOperacional') ? 'active' : '' }}">
                        <i class="uil-home-alt"></i>
                        <span> Dashboard </span>
                    </a>
                </li>

                <li class="side-nav-item">
                    <a href="{{ route('demandas.operacional') }}"
                        class="side-nav-link {{ request()->routeIs('demandas.operacional') ? 'active' : '' }}">
                        <i class="mdi mdi-format-list-bulleted-square"></i>
                        <span> Separação </span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('expedicao.previsibilidade.index') }}"
                        class="side-nav-link {{ request()->routeIs('expedicao.previsibilidade.*') ? 'active' : '' }}">

                        <i class="mdi mdi-truck-fast-outline"></i>

                        <span> Painel Expedição </span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('expedicao.importacao-programacao.index') }}"
                        class="side-nav-link {{ request()->routeIs('expedicao.importacao-programacao.*') ? 'active' : '' }}">
                        <i class="mdi mdi-file-upload-outline"></i>
                        <span> Importar PROG </span>
                    </a>
                </li>
                @if (session('tipo') === 'admin')
                    <li class="side-nav-item">
                        <a href="{{ route('expedicao.apontamentos-operacionais.index') }}"
                            class="side-nav-link {{ request()->routeIs('expedicao.apontamentos-operacionais.*') ? 'active' : '' }}">
                            <i class="mdi mdi-timer-edit-outline"></i>
                            <span> Apontar Expedição </span>
                        </a>
                    </li>
                @endif

                <li class="side-nav-item">
                    <a href="{{ route('demandas.relatorios') }}"
                        class="side-nav-link {{ request()->routeIs('demandas.relatorios') ? 'active' : '' }}">
                        <i class="mdi mdi-file-chart-outline"></i>
                        <span> Relatorios </span>
                    </a>
                </li>

                <li class="side-nav-item">
                    <a href="{{ route('stretch.apontar') }}"
                        class="side-nav-link {{ request()->routeIs('stretch.*') ? 'active' : '' }}">
                        <i class="mdi mdi-barcode-scan"></i>
                        <span> Palete Stretch </span>
                    </a>
                </li>

                <li class="side-nav-item">
                    <a href="{{ route('painel.tv') }}" target="_blank" rel="noopener" class="side-nav-link">
                        <i class="mdi mdi-television-play"></i>
                        <span> Painel TV </span>
                    </a>
                </li>

                @if (session('tipo') === 'admin')
                    <li class="side-nav-item">
                        <a href="{{ route('usuarios.index') }}"
                            class="side-nav-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                            <i class="mdi mdi-account-group-outline"></i>
                            <span> Usuarios </span>
                        </a>
                    </li>
                    <li class="side-nav-item">
                        <a href="{{ route('dispositivos.index') }}"
                            class="side-nav-link {{ request()->routeIs('dispositivos.*') ? 'active' : '' }}">
                            <i class="mdi mdi-monitor-cellphone"></i>
                            <span> Dispositivos </span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
