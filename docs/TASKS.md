# Tasks (CRUD + contrato)

## Entidad
- Modelo: `app/Models/Task.php`
- Relaciona: Project, Sprint, BacklogItem, Parent/Subtasks, Assignees, Comments, TimeEntries, Attachments.
- Soft deletes habilitado.

## Campos principales (backend)
- `title` (string)
- `description` (string nullable)
- `status` (string, recomendado: `todo`, `en_progreso`, `hecho`)
- `priority` (enum local: `baja`, `media`, `alta`, `urgente`)
- `sprint_id` (nullable: backlog)
- `backlog_item_id` (nullable)
- `parent_id` (nullable; solo 1 nivel de subtareas)
- `estimated_hours` (decimal:2)
- `due_date` (date nullable)
- `created_by` (user id)
- `status_changed_at`, `completed_at`

## Rutas CRUD (JSON)
Todas bajo `auth` + `team.context` + `scopeBindings`.

- `GET /projects/{project}/tasks` → `tasks.index`
- `POST /projects/{project}/tasks` → `tasks.store`
- `GET /projects/{project}/tasks/{task}` → `tasks.show`
- `PATCH /projects/{project}/tasks/{task}` → `tasks.update`
- `DELETE /projects/{project}/tasks/{task}` → `tasks.destroy`

## Permisos
- `index`: `TaskPolicy@viewAny` (por proyecto)
- `show`: `TaskPolicy@view`
- `store`: `TaskPolicy@create`
- `update`: `TaskPolicy@update`
- `destroy`: `TaskPolicy@delete`

## Notas de estado
- Los cambios de estado disparan `TaskStatusTrackingService` y crean eventos.
- Estado `hecho` marca `completed_at`.

