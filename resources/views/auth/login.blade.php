@extends('layouts.app')

@section('content')
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
@endsection