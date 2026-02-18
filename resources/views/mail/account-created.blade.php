<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cuenta creada Mac Del Norte</title>
</head>
<body>
    <p>Hola {{ $name }},</p>
    <p>Tu cuenta ha sido creada correctamente en {{ config('app.name') }}.</p>
    <ul>
        <li><strong>Correo:</strong> {{ $email }}</li>
        <li><strong>Contraseña:</strong> {{ $password }}</li>
    </ul>
    <p>Puedes iniciar sesión aquí: <a href="{{ url('/') }}">{{ url('/') }}</a></p>
    <p>Saludos,<br>{{ config('app.name') }}</p>
</body>
</html>
