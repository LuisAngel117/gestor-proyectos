# Edicion de comentarios (M-28)

Este documento describe el bloqueo optimista y el historial minimo de ediciones.

## Reglas base

- `lock_version` inicia en 1 y debe enviarse en cada update.
- Si `lock_version` no coincide, se devuelve conflicto y no se actualiza.
- Cada edicion crea una revision con el contenido anterior.
- Se actualizan `edited_at`, `edit_count`, `updated_by` y `lock_version`.

## Endpoints

- `PATCH /projects/{project}/tasks/{task}/comments/{comment}`
  - Requiere `body` y `lock_version`.
- `GET /projects/{project}/tasks/{task}/comments/{comment}/revisions`

## Respuestas de conflicto

Mensaje fijo:
`El comentario fue modificado por otro usuario. Actualiza la pagina e intenta de nuevo.`
