# Planificación de Sprint (M-16)

Este documento describe el flujo de planificación del sprint backlog a partir del backlog del proyecto.

## Objetivo

- Asignar ítems del backlog a un sprint.
- Reordenar ítems dentro del sprint.
- Desasignar ítems del sprint para devolverlos al backlog.

## Reglas de integridad

- El sprint y los ítems deben pertenecer al **mismo proyecto**.
- Un ítem solo puede estar asignado a **un sprint** a la vez.
- Ítems archivados no se pueden asignar.

## Permisos

- `plan` y `reorderBacklog`: solo **owner/admin** (proyecto o team) y superadmin.
- `view`: cualquier usuario que pueda ver el proyecto.
- La planificación solo está disponible si el sprint está en `planificacion`.

## Orden dentro del sprint

- Al asignar, los ítems se agregan al final del sprint (`sprint_position`).
- El reordenamiento normaliza posiciones a `1..N`.

## Desasignación

- Al devolver un ítem al backlog: `sprint_id = null`, `sprint_position = null`.
- El ítem vuelve al final del backlog (`position = max + 1`).
