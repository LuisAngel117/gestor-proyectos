# ACL - Roles y reglas base (M-10)

Este documento resume la matriz base de permisos para Teams y Projects según el contrato de M-10.
Para el detalle completo del modelo ACL, consulta `docs/ACL.md` y `docs/ACL-ABILITIES.md`.

## Roles globales (users.role)

- `superadmin`: acceso total (override global).
- `user`: usuario autenticado estándar.

## Roles por Team (team_user.role)

- `owner`
- `admin`
- `member`
- `observer`

## Roles por Project (project_user.role)

- `owner`
- `admin`
- `member`
- `observer`

## Matriz base — TeamPolicy

| Acción | Owner | Admin | Member | Observer |
|--------|:-----:|:-----:|:------:|:--------:|
| viewAny / view | ✅ | ✅ | ✅ | ✅ |
| create (team) | ✅ | ✅ | ✅ | ✅ |
| update | ✅ | ✅ | ❌ | ❌ |
| delete | ✅ | ❌ | ❌ | ❌ |
| manageMembers | ✅ | ✅ | ❌ | ❌ |

Notas:
- `view` solo aplica si el usuario pertenece al team (salvo superadmin).

## Matriz base — ProjectPolicy

| Acción | Owner | Admin | Member | Observer |
|--------|:-----:|:-----:|:------:|:--------:|
| viewAny / view | ✅ | ✅ | ✅ | ✅ |
| update | ✅ | ✅ | ❌ | ❌ |
| delete | ✅ | ❌ | ❌ | ❌ |
| transferOwnership | ✅ | ❌ | ❌ | ❌ |

Reglas adicionales:
- **create Project**: permitido solo si el rol en el team es `owner` o `admin`.
- **view Project (baseline)**: permitido si existe membresía en el proyecto o si el usuario es `owner/admin` del team del proyecto (visión administrativa).
- **owner mínimo**: no se permite eliminar al último owner del proyecto.

## Regla anti-elevación

Acción no permitida → 403. Los intentos repetidos deben registrarse en auditoría cuando exista ese módulo.

## Visibilidad vs permisos

- La visibilidad define qué recursos aparecen en listados y se pueden acceder por URL directa.
- Los permisos (create/update/delete/manageMembers/transferOwnership) aplican después.
- La estrategia de bloqueo para recursos no visibles es **403** (consistente en UI y endpoints).

## Matriz base — BacklogItemPolicy

| Acción | Owner | Admin | Member | Observer |
|--------|:-----:|:-----:|:------:|:--------:|
| viewAny / view | ✅ | ✅ | ✅ | ✅ |
| create | ✅ | ✅ | ❌ | ❌ |
| update | ✅ | ✅ | ❌ | ❌ |
| delete | ✅ | ✅ | ❌ | ❌ |
| reorder | ✅ | ✅ | ❌ | ❌ |

Notas:
- La visibilidad del backlog depende de `ProjectPolicy@view`.

## Matriz base — SprintPolicy (planificación)

| Acción | Owner | Admin | Member | Observer |
|--------|:-----:|:-----:|:------:|:--------:|
| view | ✅ | ✅ | ✅ | ✅ |
| plan | ✅ | ✅ | ❌ | ❌ |
| reorderBacklog | ✅ | ✅ | ❌ | ❌ |
| startSprint | ✅ | ✅ | ❌ | ❌ |
| closeSprint | ✅ | ✅ | ❌ | ❌ |

Notas:
- La planificación de sprint requiere rol `owner/admin` en proyecto o team.
- `startSprint` y `closeSprint` requieren `owner/admin` en proyecto o team.
