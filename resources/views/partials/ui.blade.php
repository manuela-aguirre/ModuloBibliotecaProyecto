<style>
    .ui-wrap { padding: 1.5rem 1rem; }
    .ui-title { font-family: 'Figtree', Arial, sans-serif; font-weight: 600; font-size: 1.25rem; color: #14532d; }
    .ui-page-intro { color: #64748b; font-size: 0.9rem; margin-top: 0.25rem; }
    .ui-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; }
    .ui-field { margin-bottom: 1rem; }
    .ui-field.mb-0 { margin-bottom: 0; }
    .ui-label { display: block; font-weight: 600; color: #14532d; font-size: 0.85rem; margin-bottom: 0.3rem; }
    .ui-required { color: #b91c1c; margin-left: 0.15rem; }
    .ui-form-note { margin: 0 0 1rem; color: #64748b; font-size: 0.82rem; }
    .ui-input, .ui-select { width: 100%; border-radius: 0.65rem; border: 1px solid #cbd5e1; background: #fff; padding: 0.6rem 0.85rem; color: #0f172a; transition: border-color 0.15s ease, box-shadow 0.15s ease; }
    .ui-input:focus, .ui-select:focus { border-color: #15803d; outline: 3px solid rgba(21, 128, 61, 0.15); outline-offset: 1px; }
    .ui-field-hint { display: block; margin-top: 0.3rem; color: #64748b; font-size: 0.78rem; line-height: 1.45; }
    .ui-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 1rem; }
    .ui-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; max-width: 720px; }
    .ui-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 1.1rem; display: flex; flex-direction: column; gap: 0.5rem; }
    .ui-muted { color: #64748b; font-size: 0.8rem; }
    .ui-btn { background: #14532d; color: #fff; padding: 0.55rem 1.1rem; border-radius: 0.75rem; font-weight: 600; font-size: 0.82rem; text-decoration: none; border: 0; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; }
    .ui-btn:hover { background: #166534; }
    .ui-btn-light { background: #f1f5f9; color: #475569; }
    .ui-btn-light:hover { background: #e2e8f0; }
    .ui-btn-danger { background: #b91c1c; }
    .ui-btn-danger:hover { background: #991b1b; }
    .ui-btn-sm { padding: 0.3rem 0.7rem; font-size: 0.75rem; }
    .ui-btn:focus-visible, .ui-tabs a:focus-visible, summary:focus-visible { outline: 3px solid rgba(21, 128, 61, 0.3); outline-offset: 2px; }
    .ui-table-scroll { width: 100%; max-width: 100%; overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 1rem; background: #fff; overscroll-behavior-inline: contain; }
    .ui-table-scroll:focus { outline: 3px solid rgba(21, 128, 61, 0.2); outline-offset: 2px; }
    .ui-table { width: 100%; border-collapse: collapse; background: #fff; }
    .ui-table th { background: #f0fdf4; color: #14532d; text-transform: uppercase; font-size: 0.68rem; letter-spacing: 0.055em; padding: 0.7rem 0.85rem; text-align: left; white-space: nowrap; }
    .ui-table td { padding: 0.68rem 0.85rem; border-top: 1px solid #f1f5f9; font-size: 0.84rem; line-height: 1.45; color: #334155; vertical-align: top; }
    .ui-table td:last-child { white-space: nowrap; }
    .ui-table tbody tr:not(.bg-red-50):not(.bg-amber-50):hover { background: #f8fafc; }
    .ui-table tbody tr:not(.bg-red-50):not(.bg-amber-50):hover td { background: #f8fafc; }
    .ui-badge { display: inline-block; background: #f0fdf4; color: #14532d; padding: 0.15rem 0.6rem; border-radius: 999px; font-size: 0.72rem; font-weight: 600; }
    .ui-badge.ok { background: #dcfce7; color: #14532d; }
    .ui-badge.warn { background: #fef9c3; color: #854d0e; }
    .ui-badge.bad { background: #fee2e2; color: #991b1b; }
    .ui-badge.muted { background: #e2e8f0; color: #475569; }
    .ui-tabs { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
    .ui-filter-tabs { gap: 0.35rem; }
    .ui-filter-tabs .ui-btn-sm { padding: 0.25rem 0.55rem; font-size: 0.7rem; gap: 0.3rem; }
    .ui-empty { text-align: center; padding: 3rem 1rem; color: #64748b; }
    .ui-inline { display: inline-flex; gap: 0.4rem; align-items: center; }
    .ui-result-count { color: #64748b; font-size: 0.85rem; }
    .ui-mobile-records { display: none; }
    .ui-book-cover { position: relative; display: grid; width: 100%; aspect-ratio: 4 / 3; overflow: hidden; place-items: center; border-radius: 0.75rem; background: linear-gradient(135deg, #f0fdf4, #e2e8f0); color: #15803d; }
    .ui-book-cover-placeholder { display: grid; justify-items: center; gap: 0.45rem; color: #64748b; font-size: 0.75rem; text-align: center; }
    .ui-book-cover-placeholder i { font-size: 2rem; color: #15803d; }
    .ui-book-cover img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: contain; background: #f8fafc; }
    .ui-book-cover--detail { width: 180px; aspect-ratio: 3 / 4; margin: 0 auto 1rem; }
    .ui-book-cover--detail img { object-fit: contain; }

    @media (max-width: 720px) {
        .ui-table-scroll > .ui-table { min-width: 680px; }
        .ui-table th, .ui-table td { padding: 0.7rem 0.75rem; }
        .ui-toolbar { align-items: stretch; }
        .ui-form-grid { grid-template-columns: 1fr; }
        .ui-desktop-records { display: none; }
        .ui-mobile-records { display: grid; gap: 0.75rem; }
    }

    @media (prefers-reduced-motion: no-preference) {
        .ui-btn { transition: background-color 0.15s ease, box-shadow 0.15s ease; }
    }
</style>