<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <label for="email">E-mail:</label><br>
        <input type="email" id="email" name="email" value="{{ old('email') }}" required><br><br>
        <label for="password">Senha:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        <label>
            <input type="checkbox" name="remember"> Lembrar-me
        </label><br><br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>