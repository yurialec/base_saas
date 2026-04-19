<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div id="app">
        OEEEEE {{Auth::user()}}

        <nav style="padding: 10px; background: #f0f0f0; display: flex; justify-content: space-between;">
        <div>
            <strong>Bem-vindo, {{ Auth::user()->name ?? 'Usuário' }}</strong>
        </div>
        
        <div>
            <!-- Formulário de Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background: red; color: white; border: none; padding: 5px 10px; cursor: pointer;">
                    Sair
                </button>
            </form>
        </div>
    </nav>
    </div>
</body>
</html>