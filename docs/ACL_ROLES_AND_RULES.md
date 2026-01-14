# ACL - Control de Acceso y Permisos

## Descripción General

Este documento describe el sistema de Control de Acceso (ACL) implementado en la Plataforma de Gestión de Proyectos Colaborativos.

El sistema utiliza **Laravel Policies** combinado con **roles en tablas pivot** para proporcionar autorización granular a nivel de:
- **Usuario global** (tabla `users`, campo `role`)
- **Equipo** (tabla pivot `team_user`, campo `role`)
- **Proyecto** (tabla pivot `project_user`, campos `role` y `permissions`)

---

## Arquitectura del ACL

### Niveles de Autorización

```
┌─────────────────────────────────────────┐
│         NIVEL GLOBAL (users.role)       │
│  superadmin, admin, user, observer      │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│      NIVEL EQUIPO (team_user.role)      │
│    owner, admin, member, observer       │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│    NIVEL PROYECTO (project_user.role)   │
│    owner, admin, member, observer       │
│  + permissions (JSON) - permisos extra  │
└─────────────────────────────────────────┘
```

---

## Roles Globales (users.role)

| Rol | Descripción | Capacidades |
|-----|-------------|-------------|
| **superadmin** | Administrador del sistema | Acceso total a todos los recursos |
| **admin** | Administrador general | Puede gestionar equipos y proyectos propios |
| **user** | Usuario estándar | Puede crear equipos y participar en proyectos |
| **observer** | Observador global | Solo lectura de recursos permitidos |
| **guest** | Invitado externo | Acceso muy limitado (opcional) |

---

## Roles a Nivel de Equipo (team_user.role)

| Rol | Permisos en Equipo |
|-----|-------------------|
| **owner** | ✅ Todas las acciones en el equipo<br>✅ Eliminar equipo<br>✅ Gestionar miembros<br>✅ Crear proyectos<br>✅ Gestionar proyectos |
| **admin** | ✅ Ver equipo<br>✅ Actualizar información<br>✅ Gestionar miembros<br>✅ Crear proyectos<br>❌ Eliminar equipo |
| **member** | ✅ Ver equipo<br>✅ Ver reportes<br>❌ Gestionar miembros<br>❌ Crear proyectos |
| **observer** | ✅ Solo lectura<br>❌ Todas las acciones de modificación |

---

## Roles a Nivel de Proyecto (project_user.role)

| Rol | Permisos en Proyecto |
|-----|---------------------|
| **owner** | ✅ Todas las acciones en el proyecto<br>✅ Eliminar proyecto<br>✅ Gestionar miembros<br>✅ Gestionar sprints<br>✅ Gestionar tareas<br>✅ Exportar datos |
| **admin** | ✅ Ver proyecto<br>✅ Actualizar proyecto<br>✅ Gestionar miembros<br>✅ Gestionar sprints<br>✅ Gestionar tareas<br>✅ Exportar datos<br>❌ Eliminar proyecto |
| **member** | ✅ Ver proyecto<br>✅ Crear tareas<br>✅ Actualizar sus tareas<br>✅ Comentar<br>✅ Registrar tiempo<br>✅ Adjuntar archivos<br>✅ Exportar datos<br>❌ Gestionar miembros<br>❌ Gestionar sprints<br>❌ Eliminar proyecto |
| **observer** | ✅ Ver proyecto<br>✅ Ver tareas<br>✅ Exportar datos<br>❌ Todas las acciones de modificación |

---

## Matriz de Permisos Detallada

### Permisos a Nivel de Proyecto

| Acción | superadmin | owner | admin | member | observer |
|--------|:----------:|:-----:|:-----:|:------:|:--------:|
| **view** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **create** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **update** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **delete** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **manageMembers** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **manageSprints** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **export** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **attachFiles** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **comment** | ✅ | ✅ | ✅ | ✅ | ❌ |

### Permisos a Nivel de Tarea

| Acción | superadmin | owner | admin | member | observer |
|--------|:----------:|:-----:|:-----:|:------:|:--------:|
| **view** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **create** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **update** | ✅ | ✅ | ✅ | 🔶* | ❌ |
| **delete** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **comment** | ✅ | ✅ | ✅ | ✅ | ❌ |
| **registerTime** | ✅ | ✅ | ✅ | 🔶* | ❌ |
| **manageDependencies** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **attachFiles** | ✅ | ✅ | ✅ | ✅ | ❌ |

**🔶 = Solo en tareas asignadas al usuario*

### Permisos a Nivel de Sprint

| Acción | superadmin | owner | admin | member | observer |
|--------|:----------:|:-----:|:-----:|:------:|:--------:|
| **view** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **create** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **update** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **delete** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **startSprint** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **closeSprint** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **manageTasks** | ✅ | ✅ | ✅ | ✅ | ❌ |

---

## Permisos Personalizados (JSON)

El campo `project_user.permissions` permite asignar permisos específicos a usuarios individuales:

```json
{
  "canManageSprints": true,
  "canExportSensitiveData": true,
  "canDeleteTasks": true,
  "customPermission": "value"
}
```

### Uso en Código

```php
// Verificar permiso personalizado
$projectUser = ProjectUser::where('project_id', $projectId)
    ->where('user_id', $userId)
    ->first();

if ($projectUser && $projectUser->hasPermission('canManageSprints')) {
    // Permitir acción
}
```

---

## Implementación en Código

### Uso de Policies

```php
// En un controlador
public function update(Request $request, Project $project)
{
    $this->authorize('update', $project);
    
    // Lógica de actualización
}
```

### Uso de Gate

```php
// Verificación manual
if (Gate::allows('update', $project)) {
    // Usuario puede actualizar
}

if (Gate::denies('delete', $project)) {
    abort(403, 'No autorizado');
}
```

### Helpers del Modelo User

```php
$user = Auth::user();

// Verificar rol global
if ($user->isSuperadmin()) { }
if ($user->hasGlobalRole('admin')) { }

// Verificar membresía en equipo
if ($user->belongsToTeam($teamId)) { }
if ($user->isOwnerOfTeam($teamId)) { }
if ($user->isAdminOfTeam($teamId)) { }

// Verificar permisos en proyecto
if ($user->roleInProject($projectId) === 'owner') { }
if ($user->canInProject($projectId, 'update')) { }
```

### Helpers del Modelo Team

```php
$team = Team::find($teamId);

// Verificar permisos
if ($team->userCan($user, 'manageMembers')) { }

// Obtener rol del usuario
$role = $team->getUserRole($user);

// Verificar membresía
if ($team->hasMember($user)) { }
```

---

## Casos de Uso

### Caso 1: Usuario crea un proyecto

```php
// Policy: ProjectPolicy@create
public function create(User $user): bool
{
    if ($user->isSuperadmin()) {
        return true;
    }
    
    // Usuario debe ser owner o admin de al menos un equipo
    return $user->teams()
        ->wherePivotIn('role', ['owner', 'admin'])
        ->exists();
}
```

### Caso 2: Miembro edita su tarea

```php
// Policy: TaskPolicy@update
public function update(User $user, Task $task): bool
{
    if ($user->isSuperadmin()) {
        return true;
    }
    
    $role = $user->roleInProject($task->project_id);
    
    if (in_array($role, ['owner', 'admin'])) {
        return true;
    }
    
    // Member solo puede editar sus tareas
    if ($role === 'member') {
        return $task->assignees()->where('user_id', $user->id)->exists();
    }
    
    return false;
}
```

### Caso 3: Admin gestiona miembros del proyecto

```php
// Policy: ProjectPolicy@manageMembers
public function manageMembers(User $user, Project $project): bool
{
    if ($user->isSuperadmin()) {
        return true;
    }
    
    $role = $user->roleInProject($project->id);
    return in_array($role, ['owner', 'admin']);
}
```

---

## Seguridad y Buenas Prácticas

### ✅ Hacer

1. **Siempre verificar permisos** antes de cualquier acción sensible
2. **Usar Policies** en lugar de lógica manual en controladores
3. **Documentar permisos personalizados** en el campo JSON
4. **Auditar cambios** de roles y permisos (implementar en módulos futuros)
5. **Validar contexto** (equipo/proyecto) antes de permitir acciones

### ❌ Evitar

1. **No confiar en el frontend** para control de acceso
2. **No hardcodear roles** en múltiples lugares
3. **No elevar permisos** sin validación adecuada
4. **No permitir escalada de privilegios** (ej: member → admin sin autorización)
5. **No omitir validación** en rutas API

---

## Middleware de Contexto

Para rutas que requieren contexto de equipo:

```php
// En routes/web.php
Route::middleware(['auth', 'team.context'])->group(function () {
    Route::resource('projects', ProjectController::class);
});
```

---

## Testing de Permisos

```php
/** @test */
public function owner_can_delete_project()
{
    $user = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $user->id]);
    
    $this->actingAs($user);
    
    $response = $this->delete(route('projects.destroy', $project));
    
    $response->assertStatus(200);
}

/** @test */
public function member_cannot_delete_project()
{
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $project = Project::factory()->create(['owner_id' => $owner->id]);
    
    $project->users()->attach($member->id, ['role' => 'member']);
    
    $this->actingAs($member);
    
    $response = $this->delete(route('projects.destroy', $project));
    
    $response->assertStatus(403);
}
```

---

## Extensibilidad Futura

El sistema está diseñado para ser extensible:

- **Permisos JSON** permiten agregar permisos específicos sin cambiar la estructura de la BD
- **Policies** pueden ser extendidas con nuevos métodos
- **Roles personalizados** pueden ser añadidos al enum si es necesario
- **Auditoría** puede ser implementada escuchando eventos de autorización

---

## Referencias

- Laravel Policies: https://laravel.com/docs/10.x/authorization#creating-policies
- Laravel Gates: https://laravel.com/docs/10.x/authorization#gates
- Middleware: https://laravel.com/docs/10.x/middleware
