# Vista Calendario (M-32)

## Objetivo
Visualizar tareas por fecha dentro de un proyecto usando un calendario mensual, con filtros basicos y sin edicion directa.

## Campo de fecha usado
- `tasks.due_date` (date, nullable).
- Si la tarea no tiene fecha, aparece en el bloque "Tareas sin fecha".

## Ruta
- `GET /projects/{project}/calendar`

## Parametros (query)
- `month` (opcional, formato `YYYY-MM`): mes visible.
- `sprint` (opcional): `active` | `backlog` | `{sprint_id}`.
- `status` (opcional): `todo` | `en_progreso` | `hecho`.
- `assignee` (opcional): `user_id`.

## Reglas
- Solo se muestran tareas del proyecto (`task.project_id == project.id`).
- Filtro sprint:
  - `active` usa el sprint con `status = activo`.
  - `backlog` usa tareas sin sprint.
  - `sprint_id` valida que el sprint pertenezca al proyecto.
- Filtro assignee valida que el usuario sea miembro del proyecto.

## ACL
- Requiere `ProjectPolicy@view` sobre el proyecto.
- No expone tareas fuera del proyecto (anti-fuga).

## Notas
- Las tareas con multiples asignados no se duplican.
- El calendario solo lectura en esta fase.
