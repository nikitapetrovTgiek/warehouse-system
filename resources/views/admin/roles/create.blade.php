@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-tag"></i> Создать роль</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            <div class="mb-3">
                <label>Название </label>
                <input type="text" name="name" class="form-control" required>
                <small class="text-muted">Только латиница: admin, manager, worker</small>
            </div>
            <div class="mb-3">
                <label>Описание</label>
                <input type="text" name="description" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Сохранить
            </button>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </form>
    </div>
</div>
@endsection 
