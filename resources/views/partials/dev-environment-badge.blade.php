@env('local')
    <div class="systex-dev-environment-badge" role="status" aria-label="Ambiente local de desenvolvimento">
        DEV LOCAL
    </div>

    <style>
        .systex-dev-environment-badge {
            position: fixed;
            top: 10px;
            right: 12px;
            z-index: 2147483647;
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 0 10px;
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 6px;
            background: rgba(17, 24, 39, 0.92);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28);
            color: #ffffff;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            line-height: 1;
            pointer-events: none;
            text-transform: uppercase;
        }

        .systex-dev-environment-badge::before {
            content: "";
            width: 7px;
            height: 7px;
            margin-right: 7px;
            border-radius: 50%;
            background: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.18);
        }

        @media (max-width: 575.98px) {
            .systex-dev-environment-badge {
                top: 8px;
                right: 8px;
                min-height: 24px;
                padding: 0 8px;
                font-size: 10px;
            }
        }
    </style>
@endenv
