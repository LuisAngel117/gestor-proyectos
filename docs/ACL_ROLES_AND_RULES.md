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

Reglas adicionales:
- **create Project**: permitido solo si el rol en el team es `owner` o `admin`.
- **view Project (baseline)**: permitido si existe membresía en el proyecto o si el usuario es `owner/admin` del team del proyecto (visión administrativa).

## Regla anti-elevación

Acción no permitida → 403. Los intentos repetidos deben registrarse en auditoría cuando exista ese módulo.
