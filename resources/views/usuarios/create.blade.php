@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    @include('partials.breadcrumb-auto')
    
    <!-- Header com ícone roxo -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="icon-wrapper me-3">
                <i class="mdi mdi-account-plus display-6 text-primary"></i>
            </div>
            <div>
                <h3 class="mb-1 fw-bold text-dark">Novo Usuário</h3>
                <p class="text-muted mb-0 small">Cadastre um novo usuário no sistema</p>
            </div>
        </div>
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
            <i class="mdi mdi-arrow-left me-1"></i> Voltar
        </a>
    </div>

    <!-- Alertas de Erro -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-start">
                <i class="mdi mdi-alert-circle-outline me-2 fs-5"></i>
                <div class="flex-grow-1">
                    <strong>Erro!</strong> Verifique os campos abaixo:
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <!-- Card do Formulário -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form action="{{ route('usuarios.store') }}" method="POST" id="formNovoUsuario">
                @csrf

                <div class="row g-3">
                    <!-- Nome -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Nome Completo <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-account-outline"></i>
                            </span>
                            <input type="text" 
                                   name="nome" 
                                   class="form-control @error('nome') is-invalid @enderror" 
                                   value="{{ old('nome') }}" 
                                   placeholder="Digite o nome completo"
                                   required>
                            @error('nome')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Login -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Login (E-mail) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-email-outline"></i>
                            </span>
                            <input type="text" 
                                   name="login" 
                                   class="form-control @error('login') is-invalid @enderror" 
                                   value="{{ old('login') }}" 
                                   placeholder="usuario@exemplo.com"
                                   required>
                            @error('login')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Senha -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Senha <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-lock-outline"></i>
                            </span>
                            <input type="password" 
                                   name="senha" 
                                   id="senha"
                                   class="form-control @error('senha') is-invalid @enderror" 
                                   placeholder="••••••••"
                                   autocomplete="new-password"
                                   required>
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="togglePassword('senha')">
                                <i class="mdi mdi-eye-outline"></i>
                            </button>
                            @error('senha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="text-muted">Mínimo 6 caracteres</small>
                    </div>

                    <!-- Unidade -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Unidade <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-office-building-outline"></i>
                            </span>
                            <input type="text" 
                                   name="unidade" 
                                   class="form-control @error('unidade') is-invalid @enderror" 
                                   value="{{ old('unidade') }}" 
                                   placeholder="Ex: Matriz, Filial 01"
                                   required>
                            @error('unidade')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Status <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-toggle-switch-outline"></i>
                            </span>
                            <select name="status" 
                                    class="form-select @error('status') is-invalid @enderror" 
                                    required>
                                <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inativo</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Código Nível -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Nível de Acesso <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-shield-account-outline"></i>
                            </span>
                            <select name="cod_nivel" 
                                    id="cod_nivel"
                                    class="form-select @error('cod_nivel') is-invalid @enderror" 
                                    required>
                                <option value="">Selecione...</option>
                                <option value="1" {{ old('cod_nivel') == '1' ? 'selected' : '' }}>Admin</option>
                                <option value="2" {{ old('cod_nivel') == '2' ? 'selected' : '' }}>Expedição</option>
                                <option value="3" {{ old('cod_nivel') == '3' ? 'selected' : '' }}>Separação</option>
                                <option value="4" {{ old('cod_nivel') == '4' ? 'selected' : '' }}>Recebimento</option>
                            </select>
                            @error('cod_nivel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Descrição Nível -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            Descrição do Nível <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="mdi mdi-text-box-outline"></i>
                            </span>
                            <input type="text" 
                                   name="desc_nivel" 
                                   id="desc_nivel"
                                   class="form-control @error('desc_nivel') is-invalid @enderror" 
                                   value="{{ old('desc_nivel') }}" 
                                   placeholder="Descrição do nível"
                                   required>
                            @error('desc_nivel')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Rodapé do Card -->
                <div class="card-footer bg-white border-top mt-4 px-0 pb-0">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-1"></i> Cadastrar
                        </button>
                        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-close me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .icon-wrapper {
        width: 60px; height: 60px;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(102,126,234,0.3);
    }
    .icon-wrapper i { color: #fff !important; }

    .input-group-text { 
        background-color: #f8f9fa;
        border-right: 0;
    }
    
    .input-group .form-control,
    .input-group .form-select {
        border-left: 0;
    }
    
    .input-group .form-control:focus,
    .input-group .form-select:focus {
        border-color: #ced4da;
        box-shadow: none;
    }
    
    .input-group:focus-within .input-group-text {
        border-color: #0d6efd;
    }
    
    .input-group:focus-within .form-control,
    .input-group:focus-within .form-select {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1);
    }

    .form-control:focus, .form-select:focus { 
        border-color: #0d6efd; 
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.1); 
    }
    
    .card { 
        border-radius: 0.5rem; 
    }

    .card-footer {
        background-color: transparent !important;
        border-top: 1px solid #e9ecef !important;
        padding-top: 1rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formNovoUsuario');
    const inputs = form.querySelectorAll('input[required], select[required]');
    
    // Auto-preencher descrição do nível
    const codNivel = document.getElementById('cod_nivel');
    const descNivel = document.getElementById('desc_nivel');
    
    codNivel.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            descNivel.value = selectedOption.text;
            descNivel.classList.remove('is-invalid');
            descNivel.classList.add('is-valid');
        }
    });
    
    // Validação em tempo real
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
            }
        });

        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && this.value.trim() === '') {
                this.classList.add('is-invalid');
            }
        });
    });

    // Validação no submit
    form.addEventListener('submit', function (e) {
        let valid = true;

        inputs.forEach(input => {
            if (input.value.trim() === '') {
                input.classList.add('is-invalid');
                valid = false;
            }
        });

        if (!valid) {
            e.preventDefault();
            // Scroll para o primeiro campo inválido
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
        }
    });
});

// Toggle visualização de senha
function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    const button = event.currentTarget;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('mdi-eye-outline');
        icon.classList.add('mdi-eye-off-outline');
    } else {
        input.type = 'password';
        icon.classList.remove('mdi-eye-off-outline');
        icon.classList.add('mdi-eye-outline');
    }
}
</script>

@endsection