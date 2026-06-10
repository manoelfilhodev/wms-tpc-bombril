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
                    <a data-bs-toggle="collapse" href="#sidebarOperacao" aria-expanded="{{ request()->routeIs('demandas.dashboardOperacional', 'demandas.operacional', 'stretch.*', 'wms.separacao-inteligente.*') ? 'true' : 'false' }}" aria-controls="sidebarOperacao"
                        class="side-nav-link {{ request()->routeIs('demandas.dashboardOperacional', 'demandas.operacional', 'stretch.*', 'wms.separacao-inteligente.*') ? 'active' : '' }}">
                        <i class="uil-apps"></i>
                        <span> Operação </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('demandas.dashboardOperacional', 'demandas.operacional', 'stretch.*', 'wms.separacao-inteligente.*') ? 'show' : '' }}" id="sidebarOperacao">
                        <ul class="side-nav-second-level">
                            <li>
                                <a href="{{ route('demandas.dashboardOperacional') }}" class="{{ request()->routeIs('demandas.dashboardOperacional') ? 'active' : '' }}">Dashboard</a>
                            </li>
                            <li>
                                <a href="{{ route('demandas.operacional') }}" class="{{ request()->routeIs('demandas.operacional') ? 'active' : '' }}">Separação</a>
                            </li>
                            <li>
                                <a href="{{ route('stretch.apontar') }}" class="{{ request()->routeIs('stretch.*') ? 'active' : '' }}">Palete Stretch</a>
                            </li>
                            <li>
                                <a href="{{ route('wms.separacao-inteligente.index') }}" class="{{ request()->routeIs('wms.separacao-inteligente.*') ? 'active' : '' }}">Gerar Separação</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarExpedicao" aria-expanded="{{ request()->routeIs('expedicao.*') ? 'true' : 'false' }}" aria-controls="sidebarExpedicao"
                        class="side-nav-link {{ request()->routeIs('expedicao.*') ? 'active' : '' }}">
                        <i class="mdi mdi-truck-fast-outline"></i>
                        <span> Expedição </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('expedicao.*') ? 'show' : '' }}" id="sidebarExpedicao">
                        <ul class="side-nav-second-level">
                            <li>
                                <a href="{{ route('expedicao.apontamentos-operacionais.index') }}" class="{{ request()->routeIs('expedicao.apontamentos-operacionais.*') ? 'active' : '' }}">Apontamentos</a>
                            </li>
                            <li>
                                <a href="{{ route('expedicao.previsibilidade.index') }}" class="{{ request()->routeIs('expedicao.previsibilidade.*') ? 'active' : '' }}">Previsibilidade</a>
                            </li>
                            <li>
                                <a href="{{ route('expedicao.timeline-dts.index') }}" class="{{ request()->routeIs('expedicao.timeline-dts.*') ? 'active' : '' }}">Timeline</a>
                            </li>
                            <li>
                                <a href="{{ route('expedicao.saida-veiculos.index') }}" class="{{ request()->routeIs('expedicao.saida-veiculos.*') ? 'active' : '' }}">Saída de Veículos</a>
                            </li>
                            <li>
                                <a href="{{ route('expedicao.importacao-programacao.index') }}" class="{{ request()->routeIs('expedicao.importacao-programacao.*') ? 'active' : '' }}">Importar PROG</a>
                            </li>
                        </ul>
                    </div>
                </li>

                @php
                    $cadastrosWmsAtivo = request()->routeIs('wms.skus.*', 'wms.posicoes.*', 'wms.sku-posicoes.*', 'wms.importacoes.*');
                @endphp

                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarCadastrosWms" aria-expanded="{{ $cadastrosWmsAtivo ? 'true' : 'false' }}" aria-controls="sidebarCadastrosWms"
                        class="side-nav-link {{ $cadastrosWmsAtivo ? 'active' : '' }}">
                        <i class="mdi mdi-database-cog-outline"></i>
                        <span> Cadastros WMS </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ $cadastrosWmsAtivo ? 'show' : '' }}" id="sidebarCadastrosWms">
                        <ul class="side-nav-second-level">
                            <li>
                                <a href="{{ route('wms.skus.index') }}" class="{{ request()->routeIs('wms.skus.*') ? 'active' : '' }}">SKUs</a>
                            </li>
                            <li>
                                <a href="{{ route('wms.posicoes.index') }}" class="{{ request()->routeIs('wms.posicoes.*') ? 'active' : '' }}">Posições</a>
                            </li>
                            <li>
                                <a href="{{ route('wms.sku-posicoes.index') }}" class="{{ request()->routeIs('wms.sku-posicoes.*') ? 'active' : '' }}">SKU x Posições</a>
                            </li>
                            <li>
                                <a href="{{ route('wms.importacoes.index') }}" class="{{ request()->routeIs('wms.importacoes.*') ? 'active' : '' }}">Importações</a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="side-nav-item">
                    <a data-bs-toggle="collapse" href="#sidebarGestao" aria-expanded="{{ request()->routeIs('demandas.relatorios', 'painel.tv') ? 'true' : 'false' }}" aria-controls="sidebarGestao"
                        class="side-nav-link {{ request()->routeIs('demandas.relatorios', 'painel.tv') ? 'active' : '' }}">
                        <i class="mdi mdi-chart-box-outline"></i>
                        <span> Gestão </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('demandas.relatorios', 'painel.tv') ? 'show' : '' }}" id="sidebarGestao">
                        <ul class="side-nav-second-level">
                            <li>
                                <a href="{{ route('demandas.relatorios') }}" class="{{ request()->routeIs('demandas.relatorios') ? 'active' : '' }}">Relatórios</a>
                            </li>
                            <li>
                                <a href="{{ route('painel.tv') }}" target="_blank" rel="noopener">Painel TV</a>
                            </li>
                        </ul>
                    </div>
                </li>

                @if (session('tipo') === 'admin')
                    <li class="side-nav-item">
                        <a data-bs-toggle="collapse" href="#sidebarAdministracao" aria-expanded="{{ request()->routeIs('usuarios.*', 'dispositivos.*', 'admin.logs.*') ? 'true' : 'false' }}" aria-controls="sidebarAdministracao"
                            class="side-nav-link {{ request()->routeIs('usuarios.*', 'dispositivos.*', 'admin.logs.*') ? 'active' : '' }}">
                            <i class="mdi mdi-shield-account-outline"></i>
                            <span> Administração </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('usuarios.*', 'dispositivos.*', 'admin.logs.*') ? 'show' : '' }}" id="sidebarAdministracao">
                            <ul class="side-nav-second-level">
                                <li>
                                    <a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}">Usuários</a>
                                </li>
                                <li>
                                    <a href="{{ route('dispositivos.index') }}" class="{{ request()->routeIs('dispositivos.*') ? 'active' : '' }}">Dispositivos</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.logs.index') }}" class="{{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">Auditoria</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
