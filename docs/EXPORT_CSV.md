# Export CSV (M-35)

## Dependencia
Se usa `maatwebsite/excel` para generar CSV en streaming.

## Rutas
- `GET /projects/{project}/exports/tasks.csv`
- `GET /projects/{project}/exports/time-entries.csv`
- `GET /projects/{project}/exports/workload.csv`

## Parametros y filtros
### Tasks
- `sprint` (opcional): `{sprint_id}` o `backlog`
- `status` (opcional): `todo | en_progreso | hecho`
- `assignee` (opcional): `user_id`

Columnas:
- `task_id`, `title`, `status`, `priority`, `sprint_id`, `sprint_name`, `estimated_hours`
- `due_date`, `completed_at`, `assignees`, `created_by`, `created_by_name`, `created_at`

### Time entries
- `from` (obligatorio, date)
- `to` (obligatorio, date)
- `task_id` (opcional)
- `user_id` (opcional)
- `source` (opcional): `manual | timer`

Notas:
- Se exportan solo entradas cerradas (`stopped_at` no null).
- El rango se aplica sobre `started_at`.

Columnas:
- `entry_id`, `task_id`, `task_title`, `user_id`, `user_name`, `source`
- `started_at`, `stopped_at`, `duration_seconds`, `duration_hours`
- `note`, `created_by`, `created_at`

### Workload
- `sprint` (opcional): `active` (default) o `{sprint_id}`

Columnas:
- `user_id`, `user_name`, `planned_hours`, `planned_tasks`, `real_hours`, `real_seconds`

## ACL
- Requiere `ProjectPolicy@view`.
- Filtros validados para pertenencia al proyecto.

## Nombres de archivo
- `project_{id}_tasks_YYYYMMDD.csv`
- `project_{id}_time_entries_YYYYMMDD_YYYYMMDD.csv`
- `project_{id}_workload_{sprint_id}_YYYYMMDD.csv`
