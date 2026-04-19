<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Novo Tenant</title>
</head>
<body>
    <h1>Cadastrar Novo Tenant</h1>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register') }}" method="POST">
        @csrf
        <h2>Dados do Tenant</h2>
        <label for="name">Nome do Tenant (Empresa):</label><br>
        <input type="text" id="name" name="tenant_name" required><br><br>

        <h2>Dados do Domínio</h2>
        <label for="domain">Domínio (ex: empresa.seusaaS.com):</label><br>
        <input type="text" id="domain" name="domain" required><br><br>

        <h2>Dados do Usuário Principal</h2>
        <label for="user_name">Nome do Usuário:</label><br>
        <input type="text" id="user_name" name="user_name" required><br><br>

        <label for="email">E-mail do Usuário:</label><br>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Senha:</label><br>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Cadastrar</button>
    </form>
</body>
</html>