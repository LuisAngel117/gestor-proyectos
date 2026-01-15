# Reglas de visibilidad por rol (M-12)

Este documento define qué recursos son visibles en listados y por acceso directo.

## Estrategia de bloqueo

- Recurso no visible → **403** (consistente con Policies).

## Teams

- **Superadmin**: ve todos los teams.
- **Owner/Admin/Member/Observer**: ve solo los teams donde existe `team_user`.

## Projects

### Visibilidad por Team

- **Owner/Admin del team**: ve todos los proyectos del team.
- **Member/Observer del team**: ve solo proyectos donde tiene `project_user`.

### Acceso directo

Se permite si:

- `project_user` existe (cualquier rol), o
- el usuario es owner/admin del team del proyecto, o
- el usuario es superadmin.
