# Tablero Scrum (M-31)

Este modulo agrega un tablero Scrum por proyecto con swimlanes por usuario.

## Reglas base

- Fuente de tareas: sprint activo (`sprints.status = activo`).
- Columnas por estado: `todo`, `en_progreso`, `hecho`.
- Lane owner: primer asignado por `assigned_at`; tareas sin asignar van en "Sin asignar".
- Se respeta ACL: ver = `ProjectPolicy@view`, mover = `TaskPolicy@update`.
- Anti-fuga: `task.project_id == project.id` y `task.sprint_id == active_sprint.id`.

## Rutas

- `GET /projects/{project}/scrum-board`
- `PATCH /projects/{project}/tasks/{task}/scrum-board/move`

## Payload de movimiento

`status` obligatorio, valores permitidos: `todo`, `en_progreso`, `hecho`.
