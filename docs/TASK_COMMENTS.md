# Comentarios en tareas (M-27)

Este documento describe el soporte base de comentarios como subrecurso de Task.

## Reglas base

- Cada comentario pertenece a una tarea (`task_id`).
- `body` es obligatorio y maximo 5000 caracteres.
- Auditoria minima: `created_by` obligatorio, `updated_by` opcional.
- Los comentarios usan soft deletes.

## Endpoints

- `GET /projects/{project}/tasks/{task}/comments`: lista comentarios (JSON).
- `POST /projects/{project}/tasks/{task}/comments`: crea comentario.
- `PATCH /projects/{project}/tasks/{task}/comments/{comment}`: edita comentario.
- `DELETE /projects/{project}/tasks/{task}/comments/{comment}`: elimina comentario.

## Autorizacion

- Ver comentarios: mismo acceso que `view` de la tarea.
- Crear: owner/admin/member.
- Editar/eliminar: author o owner/admin (moderacion).

## Notas

- El cuerpo debe escaparse en Blade al renderizar.
