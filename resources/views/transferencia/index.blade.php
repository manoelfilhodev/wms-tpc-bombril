@extends($layout)

@section('content')
<div class="container-fluid">
    <h4 class="mb-4">Central de Transferências</h4>

    <div class="row">
        <!-- Programar nova transferência -->
        <div class="col-md-4 mb-3">
            <a href="{{ route('transferencia.programar') }}" 
               class="card text-center text-decoration-none text-dark shadow-sm h-100">
                <div class="card-body">
                    <i class="mdi mdi-playlist-plus mdi-48px text-success"></i>
                    <h5 class="mt-2">Programar Transferência</h5>
                </div>
            </a>
        </div>

        <!-- Apontamentos -->
        <div class="col-md-4 mb-3">
            <a href="{{ route('transferencia.apontar') }}" 
               class="card text-center text-decoration-none text-dark shadow-sm h-100">
                <div class="card-body">
                    <i class="mdi mdi-format-list-bulleted mdi-48px text-info"></i>
                    <h5 class="mt-2">Apontamentos</h5>
                </div>
            </a>
        </div>

        <!-- Pendências -->
        <div class="col-md-4 mb-3">
            <a href="{{ route('transferencia.pendencias') }}" 
               class="card text-center text-decoration-none text-dark shadow-sm h-100">
                <div class="card-body">
                    <i class="mdi mdi-alert-circle mdi-48px text-danger"></i>
                    <h5 class="mt-2">Pendências de Apontamento</h5>
                </div>
            </a>
        </div>

        <!-- Editar Programação (se houver hoje) -->
        @if ($transferencias->contains(function ($trf) {
            return \Carbon\Carbon::parse($trf->data_transferencia)->isToday();
        }))
            <div class="col-md-4 mb-3">
                <a href="{{ route('transferencia.editProgramacao') }}"
                   class="card text-center text-decoration-none text-dark shadow-sm h-100">
                    <div class="card-body">
                        <i class="mdi mdi-pencil-box-outline mdi-48px text-warning"></i>
                        <h5 class="mt-2">Editar Programação</h5>
                    </div>
                </a>
            </div>
        @endif

        <!-- Relatório (apenas admin) -->
        @auth
            @if(Auth::user()->tipo === 'admin')
                <div class="col-md-4 mb-3">
                    <a href="{{ route('transferencia.relatorio') }}" 
                       class="card text-center text-decoration-none text-dark shadow-sm h-100">
                        <div class="card-body">
                            <i class="mdi mdi-chart-bar mdi-48px text-primary"></i>
                            <h5 class="mt-2">Relatório de Transferências</h5>
                        </div>
                    </a>
                </div>
            @endif
        @endauth

        <!-- Etiquetas -->
        <div class="col-md-4 mb-3">
            <a href="{{ route('transferencia.etiquetas.index') }}" 
               class="card text-center text-decoration-none text-dark shadow-sm h-100">
                <div class="card-body">
                    <i class="mdi mdi-tag-multiple mdi-48px text-success"></i>
                    <h5 class="mt-2">Etiquetas</h5>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
