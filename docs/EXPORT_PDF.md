# Export PDF (M-36)

## Dependencia
Se usa `barryvdh/laravel-dompdf` para generar PDF en servidor.

## Ruta
- `GET /projects/{project}/exports/sprint-summary.pdf`

## Parametros
- `sprint` (opcional): `active` (default) o `{sprint_id}`

## Contenido
Resumen por sprint:
- encabezado con proyecto, sprint, fechas y timestamp
- resumen de tareas (totales por estado)
- estimado total y completado
- tiempo real total (sumatoria de time entries cerradas)
- tabla de tareas del sprint
- burndown (SVG embebido)

## Burndown
Se construye con horas estimadas restantes por dia.
- Rango: `sprint.start_date` a `sprint.end_date`
- Fallback: si no hay fechas, usa `sprint.created_at` y `now()`
- Completado usa `completed_at` si existe; si no, usa `status_changed_at` o `updated_at`

## ACL
- Requiere `ProjectPolicy@view` y `SprintPolicy@view`.
- Datos acotados por `project_id` y `sprint_id`.

## Nombre de archivo
- `project_{id}_sprint_{id}_summary_YYYYMMDD.pdf`

## Limitaciones
- Dompdf no ejecuta JS; el grafico es SVG.
- Estilos simples (tablas y tipografia basica).
