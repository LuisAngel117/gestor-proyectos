# Notificaciones in-app (M-30)

Este documento describe el sistema minimo de notificaciones internas.

## Regla general

- Se usa el canal `database` de Laravel.
- No hay colas ni realtime.

## Eventos actuales

- `task_assigned`: cuando un usuario es asignado a una tarea.
- `task_time_logged`: cuando se registra tiempo manual o se detiene un timer.

## Endpoints

- `GET /notifications`: lista paginada.
- `PATCH /notifications/{notification}/read`: marcar como leida.
- `PATCH /notifications/read-all`: marcar todas como leidas.
