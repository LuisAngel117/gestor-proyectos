# QA Manual Tests (M-38)

## Preconditions
- Run `composer install` and `npm install`.
- Run `DEMO_SEED=true php artisan migrate:fresh --seed`.
- Start app with `php artisan serve` and `npm run dev`.
- Use demo credentials from `docs/DEMO_SEEDERS.md`.

## 1) Smoke (boot)
1. Open `/login` and login as `admin@gestor.test`.
2. Open `/dashboard` and confirm no 500 errors.
3. Open `/projects` and confirm demo projects list.

## 2) Auth + profile
1. Logout and confirm protected routes redirect to login.
2. Login again and open `/profile` and `/profile/account`.
3. Update a field and confirm it persists.

## 3) Team/Project context + anti-fuga
1. Pick project A and open any task route under it.
2. Change the URL to use project B with a task from project A.
3. Expect 404 for:
   - `/projects/{project}/tasks/{task}/comments`
   - `/projects/{project}/tasks/{task}/time-entries`
   - `/projects/{project}/tasks/{task}/attachments`
   - `/projects/{project}/tasks/{task}/time-summary`
   - `/projects/{project}/exports/tasks.csv`

## 4) Sprints + backlog
1. Open `/projects/{project}/sprints` and view one sprint.
2. Open `/projects/{project}/backlog` and confirm backlog items list.
3. Use sprint planning endpoints to assign/unassign backlog to sprint.

## 5) Tasks (core)
1. Create a task (UI or API) and confirm it appears in project.
2. Update title/description and confirm saved.
3. If subtasks exist, create a child with `parent_id`.

## 6) Dependencies
1. Create a dependency: POST `/projects/{project}/tasks/{task}/dependencies`.
2. Attempt a cycle and confirm it is rejected.

## 7) Time tracking (timer)
1. Start timer: POST `/projects/{project}/tasks/{task}/timer/start`.
2. Try start another task without stopping; expect 409.
3. Stop timer: POST `/projects/{project}/tasks/{task}/timer/stop`.

## 8) Manual time entries
1. Create manual entry: POST `/projects/{project}/tasks/{task}/time-entries`.
2. Validate 60s min and 12h max.
3. Create overlap and confirm it is rejected.
4. Update entry and confirm duration recalculates.

## 9) Time summaries
1. Task summary: GET `/projects/{project}/tasks/{task}/time-summary`.
2. Sprint summary: GET `/projects/{project}/sprints/{sprint}/time-summary`.
3. Confirm running entries do not affect totals unless requested.

## 10) Comments + revisions
1. Create comment: POST `/projects/{project}/tasks/{task}/comments`.
2. Edit with `lock_version`, confirm revision created.
3. Simulate conflict (two tabs) and confirm conflict message.
4. List revisions: GET `/projects/{project}/tasks/{task}/comments/{comment}/revisions`.

## 11) Assignments
1. Assign 2 users: POST `/projects/{project}/tasks/{task}/assignees`.
2. Reassign same users; expect no duplicates.
3. Unassign: DELETE `/projects/{project}/tasks/{task}/assignees/{user}`.

## 12) Notifications
1. Generate a notification via assignment.
2. Open `/notifications` and confirm entry.
3. Mark read: PATCH `/notifications/{notification}/read`.
4. Mark all read: PATCH `/notifications/read-all`.

## 13) Scrum board
1. Open `/projects/{project}/scrum-board`.
2. Move task: PATCH `/projects/{project}/tasks/{task}/scrum-board/move`.
3. Confirm status changes and tracking events update.

## 14) Calendar
1. Open `/projects/{project}/calendar`.
2. Navigate months and confirm data changes.
3. Apply filters (sprint, status, assignee).
4. Confirm "undated tasks" list appears for tasks without `due_date`.

## 15) Dashboard
1. Open `/projects/{project}/dashboard`.
2. Confirm velocity, time in state, workload sections.
3. Move a task to Done and confirm velocity changes.

## 16) Attachments
1. Upload a PDF under 10MB: POST `/projects/{project}/tasks/{task}/attachments`.
2. Download it: GET `/projects/{project}/tasks/{task}/attachments/{attachment}/download`.
3. Delete it: DELETE `/projects/{project}/tasks/{task}/attachments/{attachment}`.
4. Try a forbidden type and confirm validation.

## 17) Exports (CSV/PDF)
1. CSV tasks: GET `/projects/{project}/exports/tasks.csv`.
2. CSV time entries: GET `/projects/{project}/exports/time-entries.csv?from=YYYY-MM-DD&to=YYYY-MM-DD`.
3. CSV workload: GET `/projects/{project}/exports/workload.csv?sprint=active`.
4. PDF summary: GET `/projects/{project}/exports/sprint-summary.pdf`.

## 18) Permissions by role
Repeat a small subset with:
- observer: view only, no mutations.
- member: can update tasks and time entries, but only own time entries.
- admin/owner: full project scope.
- superadmin: full access.

