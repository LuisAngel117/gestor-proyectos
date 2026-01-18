# Permissions Matrix (current behavior)

Legend: V=view, C=create, U=update, D=delete, O=own only, -=not allowed.

Role shortcuts:
- Owner/Admin = project owner or admin, plus team owner/admin by policy.
- Member = project member.
- Observer = project observer.

| Resource / Action | Superadmin | Owner/Admin | Member | Observer |
| --- | --- | --- | --- | --- |
| Project view | V | V | V | V |
| Project members / ownership | V | U | - | - |
| Tasks view | V | V | V | V |
| Tasks create/update | V | V | V | - |
| Tasks delete | V | V | - | - |
| Dependencies manage | V | V | - | - |
| Checklist items | V | V | V | - |
| Timer start/stop | V | V | V | - |
| Manual time entries | V | V | O | - |
| Time summaries | V | V | V | V |
| Comments create | V | V | V | - |
| Comments update/delete | V | V | O | - |
| Assignees list | V | V | V | V |
| Assignees assign/unassign | V | V | V | - |
| Attachments list/download | V | V | V | V |
| Attachments upload/delete | V | V | V | - |
| Notifications list/read | V | V | V | V (own only) |
| CSV exports | V | V | V | V |
| PDF export | V | V | V | V |
| Scrum board view | V | V | V | V |
| Scrum board move | V | V | V | - |
| Calendar view | V | V | V | V |
| Dashboard view | V | V | V | V |

Notes
- Superadmin access is granted via `Gate::before`.
- Team owner/admin are treated as Owner/Admin across projects in policies.
- Manual time entries for members are restricted to their own entries.
- Exports (CSV/PDF) currently use `ProjectPolicy@view`, so observers can export.

