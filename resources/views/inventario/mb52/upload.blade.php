@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Importar Relatório MB52</h3>
    
    


    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

  <div class="row">
        <form action="{{ route('mb52.importar') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-end">
            @csrf
            
             <div class="col-12 mb-4">
                
                <label for="arquivo" class="form-label">Arquivo MB52 (.xlsx)</label>
                <input type="file" name="arquivo" class="form-control" required>
            </div>
             <div class="col-3 mb-4">
            <button class="btn btn-primary mt-4">Importar</button>
             </div>
        </form>
       
       
            <form action="{{ route('mb52.excluir') }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir os dados da MB52 de hoje?')" class="d-inline">
            @csrf
             <div class="col-3 mb-4">
            <button class="btn btn-danger mt-4">Excluir MB52 de hoje</button>
             </div>
        </form>
   </div>
</div>
@endsection
