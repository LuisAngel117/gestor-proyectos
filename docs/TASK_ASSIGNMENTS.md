# Asignacion multiple de usuarios a tareas (M-29)

Este documento describe la asignacion multiple de usuarios a tareas.

## Reglas base

- Una tarea puede tener 0..N usuarios asignados.
- Un usuario puede estar asignado a 0..N tareas.
- No se permiten duplicados (task_id, user_id).
- El usuario asignado debe pertenecer al proyecto o ser owner/admin del team.
- Se registra auditoria minima: assigned_by y assigned_at.

## Endpoints

- `GET /projects/{project}/tasks/{task}/assignees`
- `POST /projects/{project}/tasks/{task}/assignees`
  - Payload: `user_ids` (array de ids)
- `DELETE /projects/{project}/tasks/{task}/assignees/{user}`

## Autorizacion

- Ver asignados: `view` de la tarea.
- Asignar/desasignar: `update` de la tarea.
