# Plataforma de Gestión de Proyectos Colaborativos

Proyecto local para la gestión de proyectos colaborativos orientado a universidades y equipos pequeños.
Este repositorio implementa los módulos M-01 a M-15, con enfoque en equipos, proyectos, ACL, backlog y base Scrum.

## Stack

- PHP 8.1+ (Laravel 10)
- Blade + Tailwind + Alpine
- MySQL (XAMPP)
- Vite + Node.js/NPM

## Requisitos locales

- XAMPP (Apache + MySQL) o equivalente
- PHP 8.1+ con extensiones: pdo_mysql, mbstring, openssl, fileinfo, tokenizer, xml, gd
- Composer
- Node.js (v16+) y npm
- Git

## Instalación rápida (desarrollo local)

1. Instala dependencias PHP:
   ```bash
   composer install
   ```
2. Copia el archivo de entorno y ajusta credenciales:
   ```bash
   cp .env.example .env
   ```
3. Genera la clave de la app:
   ```bash
   php artisan key:generate
   ```
4. Instala dependencias front-end:
   ```bash
   npm install
   npm run dev
   ```
5. Ejecuta migraciones:
   ```bash
   php artisan migrate
   ```
6. (Opcional) Datos de ejemplo:
   ```bash
   php artisan db:seed
   ```
7. Crea el enlace de storage:
   ```bash
   php artisan storage:link
   ```
8. Inicia el servidor:
   ```bash
   php artisan serve
   ```

## Autenticación (Breeze)

- Rutas disponibles: `/login`, `/register`, `/password/reset`
- Dashboard protegido: `/dashboard` (requiere login)

## Notas

- El repositorio usa **superadmin** como rol global para pruebas de acceso.
- La zona horaria por defecto es `America/Guayaquil`.
- Estado actual: modulos M-01 a M-38.

## Demo rapido

1. Ejecuta seed demo:
   ```bash
   DEMO_SEED=true php artisan migrate:fresh --seed
   ```
2. Inicia la app:
   ```bash
   php artisan serve
   npm run dev
   ```
3. Credenciales demo: ver `docs/DEMO_SEEDERS.md`.

## QA rapido

- Checklist y pasos: `docs/QA_MANUAL_TESTS.md`
- Cierre final: `docs/FINAL_CHECKLIST.md`
