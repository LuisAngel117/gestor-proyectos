# Catálogos oficiales

Este documento define los valores oficiales de estado y prioridad para proyectos.
La fuente de verdad es `config/catalogs.php`.

## Proyectos

### Estados (`projects.status`)

- `planificacion`: Proyecto creado pero aún no iniciado formalmente.
- `en_progreso`: Proyecto en ejecución.
- `en_espera`: Proyecto pausado temporalmente.
- `completado`: Proyecto terminado.
- `cancelado`: Proyecto cancelado.
- `archivado`: Proyecto cerrado y archivado (solo consulta).

#### Transiciones recomendadas

- `planificacion` → `en_progreso` | `cancelado` | `archivado`
- `en_progreso` → `en_espera` | `completado` | `cancelado`
- `en_espera` → `en_progreso` | `cancelado` | `archivado`
- `completado` → `archivado`
- `cancelado` → `archivado`
- `archivado` → (sin salida)

### Prioridades (`projects.priority`)

- `baja`: Prioridad baja.
- `media`: Prioridad media (por defecto).
- `alta`: Prioridad alta.
- `urgente`: Prioridad urgente.

## Notas de uso

- No se deben usar valores fuera del catálogo en UI, validaciones o seeders.
- Si se requiere agregar valores nuevos, actualiza primero `config/catalogs.php`.
