# Demo Seeders (M-37)

## Objetivo
Dataset reproducible para QA local:
- 1 equipo demo
- 3 proyectos
- 10 usuarios
- 3 sprints por proyecto (1 activo, 1 cerrado, 1 planificacion)
- 20 tareas por proyecto (mezcla de estados)
- backlog items para planificacion
- checklist items y dependencias de tareas
- asignaciones, time entries, comentarios y notificaciones minimas
- adjuntos locales de prueba (PDF pequeno)

## Ejecutar
Recomendado:
```
php artisan migrate:fresh --seed
```

En PowerShell (si quieres forzar DEMO_SEED):
```
$env:DEMO_SEED="true"
php artisan migrate:fresh --seed
```

Alternativa:
```
php artisan db:seed --class=Database\\Seeders\\Demo\\DemoSeeder
```

## Credenciales
Password para todos: `password`

- admin@gestor.test (superadmin)
- carlos@gestor.test (admin de proyectos)
- maria@gestor.test
- juan@gestor.test
- ana@gestor.test
- pedro@gestor.test
- laura@gestor.test
- diego@gestor.test
- sofia@gestor.test (observer)
- miguel@gestor.test (observer)

## Distribucion de datos
- Proyectos: `Proyecto Demo A/B/C`
- Sprints por proyecto:
  - Sprint Cerrado (status = cerrado)
  - Sprint Activo (status = activo)
  - Sprint Planificacion (status = planificacion)
- Tareas: 20 por proyecto
  - 12 en sprint activo
  - 5 en sprint cerrado
  - 3 backlog (sin sprint)
- Backlog items: 8 por proyecto (3 asignados a sprint planificacion)
- Checklist: 2 items por tarea en un subconjunto
- Dependencias: relaciones simples sin ciclos
- Asignaciones: ~70% con asignados, algunas multi-asignadas
- Time entries: 8 por proyecto (cerradas)
- Comentarios: 2 por tarea en un subconjunto, con 1 edicion y revision
- Notificaciones: al menos una de asignacion y una de tiempo

## Adjuntos
Se crea un PDF pequeno por proyecto en storage/app/attachments.

## Notas
- Si `migrate:fresh` falla por la migracion de checklist inconsistente, debes corregirla antes.
