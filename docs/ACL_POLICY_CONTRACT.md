# Contrato de Policies (M-10)

Este documento describe cómo invocar y extender las Policies base de Team y Project.

## Registro de Policies

- `Team` → `TeamPolicy`
- `Project` → `ProjectPolicy`

El registro se realiza en `app/Providers/AuthServiceProvider.php`.

## Superadmin override

El override global se aplica con `Gate::before`, de modo que:

- Si `users.role = superadmin`, cualquier ability retorna permitido.
- El resto de usuarios evalúa la policy correspondiente.

## Uso en controladores

### Autorización directa

```php
$this->authorize('view', $team);
$this->authorize('update', $project);
```

### Verificación con Gate

```php
if (Gate::allows('delete', $project)) {
    // acción permitida
}
```

## Create de Project con contexto de Team

El create de Project depende del rol en el Team. Si se dispone del team:

```php
$this->authorize('create', [Project::class, $team]);
```

Si no se pasa un team explícito, la policy evalúa si el usuario es owner/admin de al menos un team.

## Notas de extensión

- Las abilities avanzadas (manageMembers, export, attachFiles, etc.) se implementarán en módulos posteriores.
- No se deben crear atajos de autorización en UI; todas las decisiones deben pasar por Policies o Gate.
