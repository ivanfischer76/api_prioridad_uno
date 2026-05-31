<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Respuesta a tu consulta</title>
</head>
<body style="font-family:Arial,Helvetica,sans-serif;line-height:1.5;color:#111827;">
    <h2>Respuesta a tu consulta</h2>
    <p><strong>Asunto:</strong> {{ $thread->subject }}</p>
    <hr>
    <p style="white-space:pre-line;">{{ $message->body }}</p>
</body>
</html>
