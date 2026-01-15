# Backlog del Proyecto (M-15)

El backlog es la fuente única de trabajo pendiente por proyecto antes de asignar ítems a un sprint.

## Reglas de negocio

- Cada ítem pertenece a un proyecto.
- En M-15 los ítems no tienen `sprint_id`.
- El orden se controla con `position` y se normaliza al reordenar.
- Estados permitidos: `backlog`, `refinado`, `archivado`.
- Soft deletes para archivado (no se elimina físicamente).

## Prioridades

Se reutiliza el catálogo de prioridades de proyecto: `baja`, `media`, `alta`, `urgente`.

## Roles y permisos

- Owner/Admin: crear, editar, archivar y reordenar.
- Member/Observer: solo lectura.
- Superadmin: override global.

## Decisiones técnicas

- El backlog se protege con el middleware de contexto de team/proyecto.
- Las rutas están anidadas bajo `/projects/{project}/backlog`.
- El reordenamiento requiere incluir todos los ítems del proyecto para normalizar posiciones.

## Pendiente para M-16

- Asignación de ítems de backlog a sprints.
