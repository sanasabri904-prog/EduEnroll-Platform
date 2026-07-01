<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>

    :root {
        --bg:           #f0f7ff;
        --surface:      #ffffff;
        --border:       #bfdbfe;
        --border-light: #dbeafe;
        --text:         #1e3a5f;
        --text-muted:   #64748b;
        --primary:      #2563eb;
        --primary-lt:   #3b82f6;
        --primary-bg:   #eff6ff;
        --success:      #16a34a;
        --success-bg:   #f0fdf4;
        --danger:       #dc2626;
        --danger-bg:    #fef2f2;
        --warning-bg:   #fffbeb;
        --shadow-sm:    0 2px 8px rgba(59,130,246,.10);
        --shadow-md:    0 4px 20px rgba(59,130,246,.13);
        --radius:       12px;
        --radius-sm:    8px;
    }

    *, *::before, *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Outfit', sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    /* ── Navbar ───────────────────────────────────────────── */
    .navbar {
        background: #fff;
        border-bottom: 1px solid var(--border-light);
        box-shadow: 0 1px 6px rgba(59,130,246,.08);
        padding: 0 2rem;
        height: 58px;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        position: sticky;
        top: 0;
        z-index: 100;
    }
    /* Logo — left anchor */
    .navbar-brand {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--primary);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: .4rem;
        flex-shrink: 0;
    }
    .navbar-brand span {
        font-size: 1.25rem;
    }

    /* Nav links — sit right after logo, push right side to far end */
    .navbar-nav {
        display: flex;
        align-items: center;
        gap: .1rem;
        list-style: none;
        flex: 1;
    }
    .navbar-nav a {
        text-decoration: none;
        color: var(--text-muted);
        font-size: .9rem;
        font-weight: 500;
        padding: .38rem .9rem;
        border-radius: 6px;
        transition: background .15s, color .15s;
    }
    .navbar-nav a:hover {
        background: var(--primary-bg);
        color: var(--primary);
    }
    .navbar-nav a.active {
        color: var(--primary);
        font-weight: 600;
        border-bottom: 2px solid var(--primary);
        border-radius: 0;
        padding-bottom: .22rem;
    }

    /* Right side — username + role badge + logout */
    .navbar-right {
        display: flex;
        align-items: center;
        gap: .65rem;
        flex-shrink: 0;
    }
    .navbar-user {
        font-size: .85rem;
        color: var(--text-muted);
        font-weight: 500;
    }
    .navbar-role {
        font-size: .82rem;
        font-weight: 600;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: .25rem;
    }
    /* Logout — red outlined pill matching old design */
    .btn-logout {
        background: transparent;
        color: var(--danger);
        border: 1.5px solid var(--danger);
        border-radius: 7px;
        font-family: 'Outfit', sans-serif;
        font-size: .82rem;
        font-weight: 600;
        padding: .28rem .8rem;
        cursor: pointer;
        text-decoration: none;
        transition: background .15s, color .15s;
    }
    .btn-logout:hover {
        background: var(--danger);
        color: #fff;
    }

    /* ── Layout ───────────────────────────────────────────── */
    .wrap {
        max-width: 1080px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
        flex: 1;
    }

    /* ── Page Header ──────────────────────────────────────── */
    .page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 1.75rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .page-header h1 {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--text);
        line-height: 1.2;
    }
    .page-header p {
        font-size: .88rem;
        color: var(--text-muted);
        margin-top: .25rem;
    }

    /* ── Cards ────────────────────────────────────────────── */
    .card {
        background: var(--surface);
        border: 1px solid var(--border-light);
        border-radius: var(--radius);
        box-shadow: var(--shadow-sm);
        overflow: hidden;
    }
    .card-body {
        padding: 1.5rem;
    }
    .form-card  {
        max-width: 680px;
    }
    .mb-2       {
        margin-bottom: 1rem;
    }

    /* ── Stats Row ────────────────────────────────────────── */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 1rem;
        margin-bottom: 1.75rem;
    }
    .stat-card {
        background: #fff;
        border: 1px solid var(--border-light);
        border-radius: var(--radius);
        padding: 1.25rem 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .35rem;
        box-shadow: var(--shadow-sm);
        transition: transform .18s, box-shadow .18s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }
    .stat-icon {
        font-size: 1.8rem;
    }
    .stat-num  {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1;
    }
    .stat-lbl  {
        font-size: .78rem;
        color: var(--text-muted);
        font-weight: 500;
        text-align: center;
    }

    /* ── Tables ───────────────────────────────────────────── */
    .table-wrap  {
        overflow-x: auto;
    }
    .table-head  {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border-light);
    }
    .table-head h2 {
        font-size: 1rem;
        font-weight: 600;
    }
    table  {
        width: 100%;
        border-collapse: collapse;
    }
    thead  {
        background: var(--primary-bg);
    }
    th     {
        font-size: .78rem;
        font-weight: 600;
        color: var(--primary);
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: .7rem 1.1rem;
        text-align: left;
    }
    td     {
        padding: .75rem 1.1rem;
        font-size: .88rem;
        border-bottom: 1px solid var(--border-light);
    }
    tr:last-child td {
        border-bottom: none;
    }
    tr:hover td {
        background: #f8fbff;
    }
    .text-muted {
        color: var(--text-muted);
    }
    .empty-msg  {
        text-align: center;
        color: var(--text-muted);
        font-size: .88rem;
        padding: 2rem !important;
    }
    .empty-msg a {
        color: var(--primary);
    }
    .badge {
        display: inline-block;
        background: var(--primary-bg);
        color: var(--primary);
        border: 1px solid var(--border);
        border-radius: 5px;
        font-size: .72rem;
        font-weight: 700;
        padding: .1rem .5rem;
        margin-right: .3rem;
        letter-spacing: .03em;
    }

    /* ── Buttons ──────────────────────────────────────────── */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .52rem 1.1rem;
        border-radius: var(--radius-sm);
        font-family: 'Outfit', sans-serif;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        border: 1.5px solid transparent;
        transition: opacity .18s, transform .12s, box-shadow .18s;
        white-space: nowrap;
    }
    .btn:hover:not(.btn-disabled) {
        opacity: .88;
        transform: translateY(-1px);
    }
    .btn-sm {
        padding: .32rem .75rem;
        font-size: .8rem;
    }
    .btn-primary  {
        background: linear-gradient(135deg, var(--primary-lt), var(--primary));
        color: #fff;
        box-shadow: 0 2px 8px rgba(37,99,235,.25);
    }
    .btn-success  {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: #fff;
        box-shadow: 0 2px 8px rgba(22,163,74,.2);
    }
    .btn-danger   {
        background: linear-gradient(135deg, #f87171, #dc2626);
        color: #fff;
        box-shadow: 0 2px 8px rgba(220,38,38,.2);
    }
    .btn-outline  {
        background: #fff;
        color: var(--primary);
        border-color: var(--border);
    }
    .btn-outline:hover {
        background: var(--primary-bg);
        border-color: var(--primary-lt);
    }
    .btn-disabled {
        opacity: .55;
        cursor: default;
        pointer-events: none;
    }

    /* ── Flash Messages ───────────────────────────────────── */
    .flash {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: .8rem 1.1rem;
        border-radius: var(--radius-sm);
        margin-bottom: 1.25rem;
        font-size: .88rem;
        font-weight: 500;
    }
    .flash-success {
        background: var(--success-bg);
        color: var(--success);
        border: 1px solid #bbf7d0;
    }
    .flash-error   {
        background: var(--danger-bg);
        color: var(--danger);
        border: 1px solid #fecaca;
    }
    .flash-close   {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        color: inherit;
    }

    /* ── Forms ────────────────────────────────────────────── */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .field {
        display: flex;
        flex-direction: column;
        gap: .3rem;
    }
    .field-full {
        grid-column: 1 / -1;
    }
    .field label {
        font-size: .78rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .field input,
    .field select,
    .field textarea {
        background: #f0f7ff;
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        color: var(--text);
        font-family: 'Outfit', sans-serif;
        font-size: .9rem;
        padding: .55rem .85rem;
        outline: none;
        transition: border-color .2s, box-shadow .2s, background .2s;
        width: 100%;
    }
    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        border-color: var(--primary-lt);
        box-shadow: 0 0 0 3px rgba(59,130,246,.15);
        background: #fff;
    }
    .field-error {
        font-size: .78rem;
        color: var(--danger);
    }
    .flex    {
        display: flex;
    }
    .gap-1   {
        gap: .75rem;
    }
    .mt-2    {
        margin-top: 1.25rem;
    }

    /* ── Course Cards ─────────────────────────────────────── */
    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.25rem;
    }
    .course-card {
        background: #fff;
        border: 1px solid var(--border-light);
        border-radius: var(--radius);
        padding: 1.35rem;
        box-shadow: var(--shadow-sm);
        display: flex;
        flex-direction: column;
        gap: .6rem;
        opacity: 0;
        animation: fadeUp .35s ease forwards;
        transition: box-shadow .2s, transform .2s;
    }
    .course-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
    }
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }
    .course-code  {
        font-size: .72rem;
        font-weight: 700;
        color: var(--primary);
        background: var(--primary-bg);
        border: 1px solid var(--border);
        border-radius: 5px;
        padding: .15rem .55rem;
        display: inline-block;
        letter-spacing: .06em;
    }
    .course-title {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text);
        line-height: 1.3;
    }
    .course-meta  {
        font-size: .82rem;
        color: var(--text-muted);
        line-height: 1.7;
    }
    .course-desc  {
        font-size: .82rem;
        color: var(--text-muted);
        line-height: 1.5;
        border-top: 1px solid var(--border-light);
        padding-top: .6rem;
    }
    .course-actions {
        display: flex;
        gap: .5rem;
        flex-wrap: wrap;
        margin-top: auto;
        padding-top: .5rem;
        border-top: 1px solid var(--border-light);
    }

    /* ── Confirm Box ──────────────────────────────────────── */
    .confirm-box {
        max-width: 460px;
        margin: 3rem auto;
        background: #fff;
        border: 1px solid var(--border-light);
        border-radius: var(--radius);
        padding: 2.5rem;
        text-align: center;
        box-shadow: var(--shadow-md);
    }
    .confirm-icon {
        font-size: 2.8rem;
        display: block;
        margin-bottom: 1rem;
    }
    .confirm-box h2 {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: .75rem;
    }
    .confirm-box p  {
        font-size: .9rem;
        color: var(--text-muted);
        line-height: 1.7;
    }
    .confirm-actions {
        display: flex;
        gap: .75rem;
        justify-content: center;
        margin-top: 1.5rem;
    }

    /* ── Empty State ──────────────────────────────────────── */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-muted);
    }
    .empty-icon {
        font-size: 3rem;
        display: block;
        margin-bottom: 1rem;
    }
    .empty-state h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: .4rem;
        color: var(--text);
    }
    .empty-state p  {
        font-size: .88rem;
    }
    .empty-state a  {
        color: var(--primary);
    }

    /* ── Footer ───────────────────────────────────────────── */
    footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 2rem;
        border-top: 1px solid var(--border-light);
        background: #fff;
        font-size: .78rem;
        color: var(--text-muted);
        flex-wrap: wrap;
        gap: .5rem;
    }

    @media (max-width: 640px) {
        .wrap {
            padding: 1.25rem 1rem;
        }
        .form-grid {
            grid-template-columns: 1fr;
        }
        .page-header {
            flex-direction: column;
        }
        .navbar {
            padding: 0 1rem;
        }
        .navbar-nav {
            display: none;
        }
    }
</style>