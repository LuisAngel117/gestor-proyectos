# Catálogo de abilities (ACL)

Este documento define el listado oficial de acciones que deben ser autorizables por Policies.

## Acciones mínimas

- `view`
- `create`
- `update`
- `delete`
- `comment`
- `manageMembers`
- `manageDependencies`
- `registerTime`
- `attachFiles`
- `export`
- `startSprint`
- `closeSprint`

## Regla de extensiones

Si un módulo futuro necesita una acción nueva:

1. Se agrega al catálogo de `config/acl.php`.
2. Se documenta aquí.
3. Se aplica de forma consistente en Policies y UI.
