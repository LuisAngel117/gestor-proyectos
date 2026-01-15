# Reglas de visibilidad por rol (M-12)

Este documento define qué recursos son visibles en listados y por acceso directo.

## Estrategia de bloqueo

- Recurso no visible → **403** (consistente con Policies).
- Contexto de team inválido o ausente en rutas protegidas → **403**.

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

## Guardrail de contexto (M-13)

Las rutas de proyectos están protegidas por un middleware de contexto que valida:

- Existe un **team activo** en sesión (o auto-selección si aplica).
- El usuario pertenece al team activo (excepto superadmin).
- El `project.team_id` coincide con el team activo.
