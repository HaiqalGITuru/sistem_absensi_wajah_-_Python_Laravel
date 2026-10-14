<style>
    .panel {
        background: #fff;
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, .05);
        margin-bottom: 24px;
    }
    .panel h3 { margin: 0 0 16px; font-size: 17px; }

    .toolbar {
        display: flex; flex-wrap: wrap; gap: 12px;
        align-items: flex-end; justify-content: space-between;
        margin-bottom: 20px;
    }
    .filter-form { display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; }
    .filter-form .field { margin: 0; }

    .btn {
        display: inline-block; border: 0; cursor: pointer;
        padding: 9px 14px; border-radius: 8px;
        font-size: 13px; font-weight: 600; text-decoration: none;
        background: #e2e8f0; color: #0f172a; font-family: inherit;
    }
    .btn:hover { filter: brightness(.96); }
    .btn-primary { background: #2563eb; color: #fff; }
    .btn-danger { background: #fee2e2; color: #b91c1c; }
    .btn-warning { background: #fef3c7; color: #92400e; }
    .btn-success { background: #dcfce7; color: #166534; }
    .btn-light { background: #f1f5f9; color: #334155; }
    .btn-sm { padding: 6px 10px; font-size: 12px; }
    .btn.disabled { opacity: .5; pointer-events: none; }
    .actions { display: flex; flex-wrap: wrap; gap: 6px; }
    .actions form { margin: 0; }

    .table-wrap { overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; min-width: 720px; }
    .data-table th, .data-table td {
        padding: 12px 10px; text-align: left; font-size: 14px;
        border-bottom: 1px solid #e5e7eb; vertical-align: middle;
    }
    .data-table th { color: #64748b; font-weight: 600; white-space: nowrap; }
    .data-table tr:hover td { background: #f8fafc; }
    .empty { padding: 30px; text-align: center; color: #94a3b8; }
    .muted { color: #64748b; font-size: 13px; }

    .badge {
        display: inline-block; padding: 3px 10px; border-radius: 999px;
        font-size: 12px; font-weight: 600;
    }
    .badge-green { background: #dcfce7; color: #166534; }
    .badge-red { background: #fee2e2; color: #991b1b; }
    .badge-yellow { background: #fef3c7; color: #92400e; }
    .badge-blue { background: #dbeafe; color: #1e40af; }
    .badge-gray { background: #e2e8f0; color: #475569; }

    .field { margin-bottom: 18px; }
    .field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
    .field input[type=text], .field input[type=number], .field input[type=time],
    .field input[type=date], .field select {
        width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1;
        border-radius: 8px; font-size: 14px; background: #fff; font-family: inherit;
    }
    .field input:focus, .field select:focus {
        outline: 0; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, .15);
    }
    .field-error { color: #b91c1c; font-size: 12px; margin-top: 5px; }
    .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0 20px; }
    .check { display: flex; align-items: center; gap: 8px; font-size: 14px; margin-bottom: 18px; }
    .form-actions { display: flex; gap: 10px; margin-top: 6px; }

    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
    .alert-warning { background: #fef3c7; color: #92400e; }
    .alert-info { background: #dbeafe; color: #1e40af; }

    .summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 24px; }
    .summary .item { background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 5px 20px rgba(0, 0, 0, .05); }
    .summary .label { color: #64748b; font-size: 13px; margin-bottom: 6px; }
    .summary .value { font-size: 26px; font-weight: 700; }

    .detail-list { display: grid; grid-template-columns: 200px 1fr; gap: 0; margin: 0; }
    .detail-list dt, .detail-list dd { padding: 12px 0; border-bottom: 1px solid #e5e7eb; margin: 0; font-size: 14px; }
    .detail-list dt { color: #64748b; font-weight: 600; }

    .pager { display: flex; flex-wrap: wrap; gap: 10px; justify-content: space-between; align-items: center; margin-top: 18px; }
    .pager-links { display: flex; gap: 8px; }

    @media (max-width: 900px) {
        .summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 650px) {
        .form-grid { grid-template-columns: 1fr; }
        .detail-list { grid-template-columns: 1fr; }
        .detail-list dt { border-bottom: 0; padding-bottom: 0; }
    }
</style>