# Adjuntos locales (M-34)

## Objetivo
Permitir subir, listar, descargar y eliminar adjuntos locales asociados a tareas, con validacion estricta y ACL.

## Storage
- Disk: `local`
- Base path: `storage/app/attachments`
- Ruta interna: `attachments/{project_id}/{task_id}/{uuid}.{ext}`

## Rutas
- `GET /projects/{project}/tasks/{task}/attachments`
- `POST /projects/{project}/tasks/{task}/attachments`
- `GET /projects/{project}/tasks/{task}/attachments/{attachment}/download`
- `DELETE /projects/{project}/tasks/{task}/attachments/{attachment}`

## Payload (upload)
- Campo: `file`
- Tipos permitidos: pdf, png, jpg, jpeg, docx
- Tamano maximo: 10 MB

## ACL
- Listar/descargar: `TaskPolicy@view`
- Subir/eliminar: `TaskPolicy@update`
- Anti-fuga: attachment debe pertenecer a task y project del path.

## Eliminacion
- Soft delete en DB.
- El archivo fisico se conserva en disco.

## Configuracion
Archivo: `config/attachments.php`
- `ATTACHMENTS_DISK` (default: local)
- `ATTACHMENTS_BASE_PATH` (default: attachments)
- `ATTACHMENTS_MAX_MB` (default: 10)
