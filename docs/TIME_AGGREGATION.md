# Time aggregation (M-26)

Este documento describe el calculo de tiempo acumulado por tarea y sprint.

## Reglas base

- Solo se suman entradas cerradas (`stopped_at` no null).
- Se ignoran `duration_seconds` null o <= 0.
- Las entradas activas no afectan acumulados.
- `running_seconds` es informativo y se calcula al vuelo.

## Endpoints

- `GET /projects/{project}/tasks/{task}/time-summary`
  - Query: `include_subtasks=0|1`, `include_running=0|1`
- `GET /projects/{project}/sprints/{sprint}/time-summary`
  - Query: `include_running=0|1`, `group_by_user=0|1`

## Respuestas

- Task:
  - `own_seconds`, `own_hours`
  - `rollup_seconds`, `rollup_hours`
  - `running_seconds` si se solicita
  - `warnings` si hay outliers
- Sprint:
  - `total_seconds`, `total_hours`
  - `running_seconds` si se solicita
  - `by_user` si se solicita
  - `warnings` si hay outliers
