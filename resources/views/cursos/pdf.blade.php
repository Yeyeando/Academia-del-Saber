<!DOCTYPE html>
<html>
<head>
    <title>Catálogo</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #2E75B6; color: white; }
    </style>
</head>
<body>
    <h1>Catálogo EcoMarket</h1>
    <table>
        <thead><tr><th>Nombre</th><th>Precio</th></tr></thead>
        <tbody>
            @foreach($cursos as $p)
            <tr><td>{{ $p->nombre }}</td><td>{{ $p->precio }}€</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
