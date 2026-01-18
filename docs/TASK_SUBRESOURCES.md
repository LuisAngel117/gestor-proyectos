# Task subrecursos (rutas y reglas)

Todas bajo `auth` + `team.context` + `scopeBindings`.

## Checklist
- `POST /projects/{project}/tasks/{task}/checklist` → store
- `PATCH /projects/{project}/tasks/{task}/checklist/{item}` → update
- `DELETE /projects/{project}/tasks/{task}/checklist/{item}` → destroy
- `POST /projects/{project}/tasks/{task}/checklist/reorder` → reorder
- Policy: `TaskPolicy@update`

## Dependencias
- `GET /projects/{project}/tasks/{task}/dependencies` → index
- `POST /projects/{project}/tasks/{task}/dependencies` → store
- `DELETE /projects/{project}/tasks/{task}/dependencies/{dependsOnTask}` → destroy
- Policy: `TaskPolicy@view` / `TaskPolicy@manageDependencies`
- Anti-ciclos en `TaskDependencyService`

## Asignaciones
- `GET /projects/{project}/tasks/{task}/assignees` → index
- `POST /projects/{project}/tasks/{task}/assignees` → store
- `DELETE /projects/{project}/tasks/{task}/assignees/{user}` → destroy
- Policy: `TaskPolicy@view` / `TaskPolicy@update`

## Timer + time entries
- `GET /projects/{project}/tasks/{task}/timer` → estado
- `POST /projects/{project}/tasks/{task}/timer/start`
- `POST /projects/{project}/tasks/{task}/timer/stop`
- `GET /projects/{project}/tasks/{task}/time-entries`
- `POST /projects/{project}/tasks/{task}/time-entries`
- `PATCH /projects/{project}/tasks/{task}/time-entries/{timeEntry}`
- `DELETE /projects/{project}/tasks/{task}/time-entries/{timeEntry}`
- Policy: `TaskPolicy@view` / `TaskPolicy@trackTime`

## Resumen de tiempo
- `GET /projects/{project}/tasks/{task}/time-summary`
- `GET /projects/{project}/sprints/{sprint}/time-summary`
- Policy: `TaskPolicy@view` / `SprintPolicy@view`

## Comentarios + revisiones
- `GET /projects/{project}/tasks/{task}/comments`
- `POST /projects/{project}/tasks/{task}/comments`
- `PATCH /projects/{project}/tasks/{task}/comments/{comment}`
- `DELETE /projects/{project}/tasks/{task}/comments/{comment}`
- `GET /projects/{project}/tasks/{task}/comments/{comment}/revisions`
- Policy: `CommentPolicy` (create/update/delete/view)

## Adjuntos
- `GET /projects/{project}/tasks/{task}/attachments`
- `POST /projects/{project}/tasks/{task}/attachments`
- `GET /projects/{project}/tasks/{task}/attachments/{attachment}/download`
- `DELETE /projects/{project}/tasks/{task}/attachments/{attachment}`
- Policy: `TaskPolicy@view` / `TaskPolicy@update`

## Respuesta
- JSON cuando `Accept: application/json` o `expectsJson()`.
- Redirect + flash cuando se usa desde formularios Blade.

