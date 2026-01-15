# Modelo ACL (autorización por recurso)

Este documento define el modelo oficial de autorización del sistema.
El objetivo es eliminar ambigüedades sobre cómo se decide si un usuario puede realizar una acción en un recurso.

## Fuentes de verdad

El ACL se evalúa con tres capas:

1. **Rol global**: `users.role`
2. **Rol por Team**: `team_user.role`
3. **Rol por Project**: `project_user.role`

### Orden de evaluación

- Si el usuario es `superadmin`, se permite todo (override global).
- Si no es `superadmin`, se evalúa el contexto (team o project) con su rol en el pivot correspondiente.

## Roles oficiales

### Global

- `superadmin`
- `user`

### Team/Project

- `owner`
- `admin`
- `member`
- `observer`

**Decisión de almacenamiento:** los roles se almacenan como `string` en los pivots.

## Acciones controladas (abilities)

Las abilities oficiales están definidas en `docs/ACL-ABILITIES.md` y en `config/acl.php`.

## Reglas base por rol (baseline)

### Owner

- Todas las acciones del recurso en su contexto.

### Admin

- Gestión de miembros.
- Edición/configuración del recurso.
- Operación avanzada (sprints, tareas y dependencias).

### Member

- Trabajo operativo (comentarios, registro de tiempo, tareas propias).
- No puede eliminar ni gestionar miembros.

### Observer

- Solo lectura (`view`).

## Política anti-elevación

Si un usuario intenta una acción que requiere rol mayor:

- Se responde con **403**.
- Se deja previsto el registro de eventos repetidos (auditoría futura).

## Restricciones técnicas

- La autorización se resuelve en **Policies** del lado servidor.
- No se usarán paquetes externos de permisos (Spatie).
- No se usa JSON de permisos en `project_user` (solo roles).

## Archivos fuente

- `config/acl.php`
- `docs/ACL-ABILITIES.md`
