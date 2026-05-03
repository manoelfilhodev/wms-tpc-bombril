<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Preview de Etiquetas</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 20px; }
        img { max-width: 100%; margin: 10px 0; }
        @media print {
            button { display: none; }
        }
    </style>
</head>
<body>
    <h3>Preview das Etiquetas</h3>
    <button onclick="window.print()">Imprimir</button>
    <div>
        @foreach($imagens as $img)
            <img src="{{ $img }}" alt="Etiqueta">
        @endforeach
    </div>
</body>
</html>
