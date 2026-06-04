@extends('layouts.guest')

@section('title', 'Вход в систему')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent text-center pt-4 pb-0 border-0">
                <h3 class="fw-bold">
                    <i class="fas fa-arrow-right-to-bracket me-2"></i> Вход в систему
                </h3>
                <p class="text-muted">Складская система</p>
            </div>
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Пароль</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Запомнить меня</label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> Войти
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <a href="{{ route('register') }}" class="text-decoration-none">Нет аккаунта? Зарегистрироваться</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection