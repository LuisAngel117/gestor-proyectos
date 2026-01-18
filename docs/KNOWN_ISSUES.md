# Known Issues / Operational Notes

## Open issues
- None blocking reported at this time.

## Operational notes
- CSV export depends on `maatwebsite/excel` (phpspreadsheet). Ensure `ext-gd` is enabled in `php.ini` for CLI and Apache.
- PDF export depends on `barryvdh/laravel-dompdf` and uses SVG for burndown (no JS).
- Attachments are soft-deleted; files remain on disk until a manual purge is added.
- Calendar uses `tasks.due_date`; tasks without date appear only in the "undated" list.

