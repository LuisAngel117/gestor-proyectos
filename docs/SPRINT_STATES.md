# Estados y transiciones de Sprint (M-17)

Este documento define el ciclo de vida de un sprint y las restricciones de planificación.

## Estados oficiales

- `planificacion`: sprint creado, se puede planificar.
- `activo`: sprint iniciado y en ejecución.
- `cerrado`: sprint finalizado; el backlog queda solo lectura.

## Transiciones permitidas

- `planificacion` → `activo`
- `activo` → `cerrado`

## Transiciones prohibidas

- `planificacion` → `cerrado` (no permitido).
- `cerrado` → cualquier otro estado.

## Regla crítica: solo 1 sprint activo por proyecto

Antes de iniciar un sprint, se valida que no exista otro sprint activo en el mismo proyecto.

## Restricciones de planificación

- **Planificación** (`plan`, `reorderBacklog`) solo disponible si el sprint está en `planificacion`.
- En `activo` o `cerrado`, el backlog del sprint queda bloqueado.
