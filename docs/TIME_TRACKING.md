# Time tracking por temporizador (M-24)

Este documento describe el registro de tiempo real por tarea mediante temporizador.

## Entidad

- `task_time_entries` registra sesiones de tiempo por usuario y tarea.

## Reglas base

- Un usuario solo puede tener **un temporizador activo** a la vez.
- Un temporizador activo se identifica con `stopped_at = NULL`.
- Al detenerse, se calcula `duration_seconds` con diferencia entre `started_at` y `stopped_at`.
- Las operaciones se validan por contexto de proyecto y ACL.

## Endpoints mínimos

- `GET /projects/{project}/tasks/{task}/timer`: estado del temporizador.
- `POST /projects/{project}/tasks/{task}/timer/start`: iniciar temporizador.
- `POST /projects/{project}/tasks/{task}/timer/stop`: detener temporizador.

## Autorización

- `view`: ver estado.
- `trackTime`: iniciar/detener temporizador (owner/admin/member).

## Notas

- La edición manual de entradas se aborda en M-25.
- Los acumulados y reportes se abordan en M-26.
