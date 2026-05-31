<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo mensaje de contacto</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#111827;">
    <h2>Nuevo mensaje de contacto</h2>
    <p><strong>Asunto:</strong> {{ $thread->subject }}</p>
    <p><strong>Usuario:</strong> {{ $user->nombre }} {{ $user->apellido }} ({{ $user->username }})</p>
    <p><strong>Email de respuesta:</strong> {{ $message->from_email }}</p>
    <hr>
    <p style="white-space:pre-line;">{{ $message->body }}</p>
</body>
</html>
