# Contexto de Team (M-13)

Este documento define el comportamiento oficial del **Team activo** en el sistema.

## Concepto

El **Team activo** es el equipo seleccionado por el usuario para operar en rutas de proyectos
(y futuras rutas de sprints/tareas). Se almacena en sesión y se valida en cada request
protegido por middleware.

## Fuentes del contexto (orden)

1. Parámetro de ruta `{team}` si existe.
2. Sesión previa (`context.team_id`).
3. Auto-selección si el usuario pertenece a **un solo team**.

Si no se puede determinar un team válido, se bloquea el acceso.

## Validaciones

- Si el usuario **no es superadmin**, debe pertenecer al team activo.
- Si la ruta resuelve un `Project`, se valida que `project.team_id` coincida con el team activo.

## Respuestas ante fallos

- Sin contexto y sin auto-selección posible → redirect a `/teams` con mensaje.
- Team inválido o recurso fuera de contexto → **403**.

## Reglas de implementación

- El middleware de contexto es obligatorio en rutas de proyectos.
- Los controllers deben asumir que el contexto ya fue validado por middleware.
- No duplicar validaciones de contexto dentro de los controllers.
