<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página no encontrada - Academia del saber</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f0f0f0;
        }
        h1 { color: #333; font-size: 72px; margin: 0; }
        p { color: #666; font-size: 20px; }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>404</h1>
    <p>¡Ups! La página que buscas no existe.</p>
    <p>Puede que el curso haya sido eliminado o la URL sea incorrecta.</p>
    <a href="{{ url('/cursos') }}">Volver al inicio</a>
</body>
</html>