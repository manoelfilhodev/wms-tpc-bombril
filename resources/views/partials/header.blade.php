<div class="navbar-custom">
    <button class="button-menu-mobile open-left" aria-label="Abrir menu lateral">
        <i class="mdi mdi-menu"></i>
    </button>

    <ul class="list-unstyled topbar-menu float-end mb-0">
        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <i class="dripicons-bell noti-icon"></i>
                <span class="noti-icon-badge"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg">
                <div class="dropdown-item noti-title">
                    <h5 class="m-0">
                        <span class="float-end">
                            <a href="javascript:void(0);" class="text-dark"><small>Limpar tudo</small></a>
                        </span>
                        Notificacoes
                    </h5>
                </div>

                <a href="javascript:void(0);" class="dropdown-item text-center text-primary notify-item notify-all">
                    Ver todas notificacoes
                </a>
            </div>
        </li>

        <li class="notification-list">
            <a class="nav-link end-bar-toggle" href="javascript:void(0);" aria-label="Configuracoes">
                <i class="dripicons-gear noti-icon"></i>
            </a>
        </li>

        <li class="notification-list">
            <a class="nav-link" href="{{ route('convites.index') }}" data-bs-container="body" data-bs-placement="bottom" data-bs-toggle="tooltip" title="Gerar link de cadastro">
                <i class="dripicons-user-group noti-icon"></i>
            </a>
        </li>

        <li class="notification-list">
            <a class="nav-link" href="javascript:void(0);" id="toggle-dark-mode" data-bs-container="body" data-bs-placement="bottom" data-bs-toggle="tooltip" title="Alternar tema">
                <i class="dripicons-brightness-max noti-icon"></i>
            </a>
        </li>

        <li class="notification-list">
            <a class="nav-link" href="{{ route('logout') }}" data-bs-container="body" data-bs-placement="bottom" data-bs-toggle="tooltip" title="Sair do sistema">
                <i class="dripicons-power noti-icon text-danger"></i>
            </a>
        </li>

        <li class="dropdown notification-list wms-user-zone">
            <a class="nav-link dropdown-toggle nav-user arrow-none me-0 wms-user-trigger" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                <span class="account-user-avatar wms-user-avatar">
                    <img src="https://vmxi.com.br/img/login.png" alt="Imagem do usuario" class="rounded-circle">
                </span>
                <span class="wms-user-meta">
                    <span class="account-user-name">{{ session('nome') }}</span>
                    <span class="account-position">{{ session('tipo') }}</span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                <div class="dropdown-header noti-title">
                    <h6 class="text-overflow m-0">Bem-vindo</h6>
                </div>

                <a href="javascript:void(0);" class="dropdown-item notify-item">
                    <i class="mdi mdi-account-circle me-1"></i>
                    <span>Meus dados</span>
                </a>

                <a href="javascript:void(0);" class="dropdown-item notify-item">
                    <i class="mdi mdi-account-edit me-1"></i>
                    <span>Configuracoes</span>
                </a>

                <a href="javascript:void(0);" class="dropdown-item notify-item">
                    <i class="mdi mdi-lifebuoy me-1"></i>
                    <span>Suporte</span>
                </a>

                <a href="{{ route('logout') }}" class="dropdown-item notify-item">
                    <i class="mdi mdi-logout me-1"></i>
                    <span>Sair</span>
                </a>
            </div>
        </li>
    </ul>
</div>
