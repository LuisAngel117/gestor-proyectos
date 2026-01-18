# Portfolio Demo Runbook

## Quick start
1. `composer install`
2. `npm install`
3. `DEMO_SEED=true php artisan migrate:fresh --seed`
4. `php artisan serve`
5. `npm run dev`

## Demo logins
Password for all users: `password`
- admin@gestor.test (superadmin)
- carlos@gestor.test (admin project)
- sofia@gestor.test (observer)

## Demo flow (10-15 min)
1. Login as admin and open `/projects`.
2. Open a project and show:
   - Scrum board: `/projects/{project}/scrum-board`
   - Move a task to another status.
3. Open Calendar: `/projects/{project}/calendar`.
4. Open Dashboard: `/projects/{project}/dashboard`.
5. Open a task and show:
   - Comments + edit with lock_version.
   - Manual time entry (if no UI, use Postman).
   - Attachments list/upload/download.
6. Assign another user to the task (multi-assignee).
7. Open `/notifications` and mark one as read.
8. Export:
   - CSV tasks: `/projects/{project}/exports/tasks.csv`
   - PDF sprint summary: `/projects/{project}/exports/sprint-summary.pdf`

## ACL demo (quick)
1. Login as observer and open the same project.
2. Confirm view-only (no task moves, no uploads, no assignments).

