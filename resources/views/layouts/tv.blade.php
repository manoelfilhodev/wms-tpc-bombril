<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel TV</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="{{ asset('assets/css/wms-ui.css') }}" rel="stylesheet" type="text/css" />

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f4f6f8;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        }
    </style>
</head>
<body>
    @yield('content')

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen().catch(() => {});
            }
        });
    </script>

    @yield('scripts')
</body>
</html>