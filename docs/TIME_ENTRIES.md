# Time entries manuales (M-25)

Este documento describe la edicion manual de entradas de tiempo por tarea.

## Reglas base

- Las entradas manuales siempre estan cerradas: `started_at` y `stopped_at` son obligatorios.
- `duration_seconds` se calcula desde los timestamps.
- No se permite crear/editar manual con `stopped_at = NULL`.
- No se permite crear manual si existe un temporizador activo para el usuario.
- No se permiten solapes por usuario entre entradas cerradas.
- Limites por entrada: minimo 60 segundos, maximo 43,200 segundos.

## Endpoints

- `GET /projects/{project}/tasks/{task}/time-entries`: listar entradas de la tarea.
- `POST /projects/{project}/tasks/{task}/time-entries`: crear entrada manual.
- `PATCH /projects/{project}/tasks/{task}/time-entries/{timeEntry}`: editar entrada manual.
- `DELETE /projects/{project}/tasks/{task}/time-entries/{timeEntry}`: eliminar entrada manual.

## Autorizacion

- `view`: listar entradas.
- `trackTime`: crear/editar/eliminar.
- Regla adicional: un member solo puede editar o eliminar sus propias entradas.

## Notas

- `source` distingue `timer` vs `manual`.
- `created_by` registra quien creo la entrada.
