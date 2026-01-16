# Validación anti-circularidad de dependencias (M-22)

Este documento describe el enfoque para prevenir ciclos en el grafo de dependencias de tareas.

## Regla principal

- El grafo de dependencias por proyecto debe ser acíclico (DAG).

## Criterio de validación

Al crear una dependencia **A → B**:

- Se considera inválida si **A** es alcanzable desde **B** en el grafo actual.
- Esto previene ciclos directos e indirectos.

## Estrategia de verificación

- Se utiliza un recorrido BFS/DFS desde **B**.
- Si se encuentra **A** en el recorrido, la dependencia se rechaza.

## Guardrails

- Se aplica validación por proyecto.
- Se usan límites de profundidad y nodos para evitar recorridos excesivos.

## Mensaje de error

- "No se puede crear la dependencia porque generaría un ciclo."
