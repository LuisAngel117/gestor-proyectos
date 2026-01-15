# Reglas de membresía de proyectos (M-11)

Este documento define las reglas operativas para gestionar miembros de un proyecto.

## Principios de integridad

- Un usuario **no puede** pertenecer a un proyecto si no pertenece al team del proyecto.
- Siempre debe existir **al menos un owner** en el proyecto.
- Las acciones sensibles se controlan con `ProjectPolicy`.

## Operaciones permitidas

### Listar miembros

- Visible para cualquier miembro del proyecto.

### Agregar miembro

- Requiere `manageMembers`.
- El usuario a agregar debe pertenecer al team del proyecto.
- Rol por defecto sugerido: `member`.
- No se debe duplicar la membresía.

### Cambiar rol

- Requiere `manageMembers`.
- No se puede degradar al **último owner**.

### Remover miembro

- Requiere `manageMembers`.
- No se puede remover al **último owner**.

### Transferir ownership

- Requiere `transferOwnership`.
- Solo el owner actual (o `superadmin`) puede transferir.
- El receptor debe pertenecer al team y ser miembro del proyecto.
