<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Etiqueta Transferência</title>
    <style>
        body { margin: 0; padding: 0; text-align: center; }
        img { max-width: 100%; height: auto; }
    </style>
</head>
<body onload="window.print()">
    <img src="{{ asset('storage/etiquetas_png/transferencias/' . $etiqueta->transferencia_id . '/transferencia_' . $etiqueta->id . '.png') }}" alt="Etiqueta">
</body>
</html>
