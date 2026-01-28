<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        p { margin: 0 0 12px 0; color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; text-transform: uppercase; font-size: 10px; letter-spacing: 0.04em; }
    </style>
</head>
<body>
    <h1>Usuarios</h1>
    <p>Generado: {{ $generatedAt->format('Y-m-d H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Email</th>
                <th>Rol del sistema</th>
                <th>Estado</th>
                <th>Equipos</th>
                <th>Proyectos</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td>{{ $user->full_name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role }}</td>
                    <td>{{ $user->estado }}</td>
                    <td>{{ $user->teams_count }}</td>
                    <td>{{ $user->projects_count }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
