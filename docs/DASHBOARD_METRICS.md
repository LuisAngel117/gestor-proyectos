# Dashboard de Metricas (M-33)

## Objetivo
Mostrar metricas por proyecto enfocadas en el sprint activo (o uno seleccionado) y mantener trazabilidad de cambios de estado.

## Rutas
- `GET /projects/{project}/dashboard`

## Parametros
- `sprint` (opcional):
  - `active` (default)
  - `{sprint_id}`

## Instrumentacion de estado
Se registran transiciones en `task_status_events` cuando una tarea cambia de estado desde el tablero Scrum.

Campos:
- `from_status`, `to_status`, `changed_by`, `changed_at`
- `project_id` para consultas rapidas por proyecto

La tabla `tasks` agrega:
- `status_changed_at` (ultimo cambio)
- `completed_at` (cuando entra a `hecho`)

## Metricas
### Velocidad
- Suma de `estimated_hours` de tareas completadas en el sprint.
- Si no hay `estimated_hours`, cuenta como 0.

### Tiempo en estado
- Promedio de tiempo por estado, basado en eventos de estado.
- Se calcula dentro del rango del sprint (start_date/end_date si existen).

### Carga por usuario
- Planificada: suma de `estimated_hours` de tareas abiertas asignadas al "primer asignado".
- Real: suma de `duration_seconds` en `task_time_entries` por usuario.

## ACL
- Requiere `ProjectPolicy@view`.
- Todas las consultas se acotan a `project_id` y al sprint seleccionado.

## Notas
- El historial de estado solo se registra desde M-33 en adelante.
- Si no hay sprint activo, el dashboard pide seleccionar uno.
