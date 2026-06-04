@extends('layouts.admin')

@section('title', 'Главная')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">
        <i class="fas fa-home me-2"></i> Добро пожаловать в админ-панель
    </h1>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card text-center p-3">
            <i class="fas fa-users fa-2x text-primary mb-2"></i>
            <h5>Пользователи</h5>
            <p class="display-6">{{ \App\Models\User::count() }}</p>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm">Управление →</a>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-center p-3">
            <i class="fas fa-tags fa-2x text-success mb-2"></i>
            <h5>Роли</h5>
            <p class="display-6">{{ \App\Models\Role::count() }}</p>
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-success btn-sm">Управление →</a>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-center p-3">
            <i class="fas fa-exchange-alt fa-2x text-warning mb-2"></i>
            <h5>Операции</h5>
            <p class="display-6">{{ \App\Models\InventoryMovement::count() }}</p>
            <a href="{{ route('movements.index') }}" class="btn btn-outline-warning btn-sm">Перейти →</a>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-center p-3">
            <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
            <h5>Отчёты</h5>
            <p class="display-6">4</p>
            <a href="{{ route('reports.stock') }}" class="btn btn-outline-info btn-sm">Перейти →</a>
        </div>
    </div>
</div>
@endsection