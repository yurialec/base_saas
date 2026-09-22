@extends('layouts.app')

@section('content')
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <a href="{{ route('google.redirect', ['intent' => 'login']) }}" class="btn btn-outline-danger">
        Entrar com Google
    </a>
</div>

<form method="POST" action="{{ route('login') }}">
    @csrf
    <input
        type="email"
        name="email"
        value="{{ old('email') }}"
        required>
    <input
        type="password"
        name="password"
        required>
    <label>
        <input type="checkbox" name="remember">
        Lembrar-me
    </label>
    <button type="submit">
        Entrar
    </button>
</form>
<p class="mt-3">
    Não possui uma conta? <a href="{{ route('register') }}">Cadastre-se</a>
</p>
@endsection
