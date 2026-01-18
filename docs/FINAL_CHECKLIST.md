# Final Checklist (M-38)

## Environment
- [ ] `composer install` completed without errors.
- [ ] `npm install` completed without errors.
- [ ] `php artisan optimize:clear` runs clean.
- [ ] App boots with `php artisan serve` and `npm run dev`.

## Database + seed
- [ ] `DEMO_SEED=true php artisan migrate:fresh --seed` completes.
- [ ] Demo users, team, projects, sprints, tasks exist (3x20x10).
- [ ] No migration errors or missing tables.

## Core modules (M-01..M-22)
- [ ] Teams and projects load under team context.
- [ ] Backlog and sprint planning work.
- [ ] Task dependencies reject cycles.
- [ ] Task checklist works (create/update/reorder).

## Time (M-23..M-26)
- [ ] `estimated_hours` persists and validates.
- [ ] Timer start/stop works (single active per user).
- [ ] Manual time entries validate duration + overlap.
- [ ] Task/Sprint time summaries return correct totals.

## Collaboration (M-27..M-30)
- [ ] Comments CRUD works with optimistic locking + revisions.
- [ ] Multi-assignees assign/unassign without duplicates.
- [ ] Notifications list/read/read-all work and are scoped to user.

## Visualization (M-31..M-33)
- [ ] Scrum board renders and moves tasks with ACL.
- [ ] Calendar renders month + filters.
- [ ] Dashboard shows velocity, time-in-state, workload.

## Files + reports (M-34..M-36)
- [ ] Attachments upload/download/delete (soft) work.
- [ ] CSV exports download and respect filters.
- [ ] PDF export generates summary + burndown SVG.

## Security + anti-fuga
- [ ] Cross-project URL tampering returns 404/403.
- [ ] Observer cannot mutate tasks, time, comments, assignments.
- [ ] Members can only update their own time entries.

## Docs + handoff
- [ ] `docs/DEMO_SEEDERS.md` reflects current seed data.
- [ ] `docs/QA_MANUAL_TESTS.md` matches actual routes.
- [ ] `docs/PERMISSIONS_MATRIX.md` matches policies.
- [ ] `docs/KNOWN_ISSUES.md` is up to date.

