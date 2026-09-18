<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="login-url" content="{{ route('login') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrativo</title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>

<body>
    <div id="app"></div>
    <script>
        window.App = {
            user: @json(session('user')),
            tenant: @json(session('user.tenant')),
            permissions: @json(session('user.role.permissions'))
        };
    </script>
    <script src="{{ mix('js/app.js') }}" defer></script>
</body>

</html>