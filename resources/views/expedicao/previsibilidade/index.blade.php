@extends('layouts.app')

@section('body_class', 'expedicao-tv-mode')

@section('content')
    @php
        function tempoBoard($minutos)
        {
            if ($minutos === null) {
                return '--:--';
            }

            $minutos = (int) $minutos;

            $sinal = $minutos < 0 ? '-' : '';

            return $sinal . floor(abs($minutos) / 60) . ':' . str_pad(abs($minutos) % 60, 2, '0', STR_PAD_LEFT);
        }
    @endphp

    <style>
        /* =========================
                   TV MODE
                ========================= */

        .expedicao-tv-mode .leftside-menu,
        .expedicao-tv-mode .navbar-custom,
        .expedicao-tv-mode .topnav,
        .expedicao-tv-mode footer {
            display: none !important;
        }

        .expedicao-tv-mode .content-page,
        .expedicao-tv-mode .content,
        .expedicao-tv-mode .container-fluid {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        body.expedicao-tv-mode {
            --ops-header-height: 0px;
            --ops-summary-height: 0px;
            --ops-footer-height: 0px;
            background:
                radial-gradient(circle at top,
                    rgba(0, 174, 255, .08),
                    transparent 40%),
                linear-gradient(180deg,
                    #040c16 0%,
                    #07111f 40%,
                    #020812 100%);
            color: #fff;
            overflow-x: hidden;
            overflow-y: hidden;
            min-height: 100vh;
        }

        .expedicao-tv-mode *,
        .expedicao-tv-mode *::before,
        .expedicao-tv-mode *::after {
            box-sizing: border-box;
        }

        /* =========================
                   HEADER
                ========================= */

        .ops-topbar {
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
            background: rgba(2, 10, 18, .88);
            border-bottom: 1px solid rgba(0, 174, 255, .18);
            padding: clamp(12px, 1.4vw, 18px) clamp(12px, 2vw, 26px);
        }

        .ops-topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-width: 0;
        }

        .ops-title-wrap {
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 0;
        }

        .ops-airport-icon {
            width: clamp(46px, 5vw, 74px);
            height: clamp(46px, 5vw, 74px);
            flex: 0 0 clamp(46px, 5vw, 74px);
            border-radius: clamp(12px, 1.2vw, 18px);
            background:
                radial-gradient(circle at center,
                    rgba(0, 174, 255, .22),
                    rgba(0, 174, 255, .08));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #00aeff;
            font-size: clamp(26px, 3vw, 42px);
            box-shadow: 0 0 28px rgba(0, 174, 255, .18);
        }

        .ops-kicker {
            color: #00aeff;
            font-size: clamp(11px, 1.2vw, 18px);
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .ops-title {
            font-size: clamp(26px, 3.2vw, 54px);
            font-weight: 1000;
            line-height: 1;
            text-transform: uppercase;
            border-bottom: 2px solid rgba(255, 255, 255, .75);
            display: inline-block;
            padding-bottom: 6px;
            overflow-wrap: anywhere;
        }

        .ops-subtitle {
            margin-top: 8px;
            color: #7fa6c9;
            font-size: clamp(11px, 1.2vw, 18px);
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .ops-clock {
            flex: 0 1 260px;
            min-width: 180px;
            border-radius: clamp(12px, 1.2vw, 18px);
            padding: clamp(12px, 1.2vw, 18px) clamp(14px, 1.6vw, 24px);
            background: rgba(0, 0, 0, .22);
            border: 1px solid rgba(255, 255, 255, .10);
            text-align: center;
        }

        .ops-clock strong {
            font-size: clamp(28px, 2.8vw, 42px);
            font-weight: 1000;
            line-height: 1;
        }

        .ops-clock small {
            display: block;
            margin-top: 8px;
            color: #00aeff;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 900;
        }

        /* =========================
                   BOARD
                ========================= */

        .ops-board-wrapper {
            height: calc(100vh - var(--ops-header-height) - var(--ops-summary-height) - var(--ops-footer-height));
            min-height: 260px;
            padding: clamp(10px, 1.6vw, 20px) clamp(8px, 1.4vw, 16px) clamp(10px, 1.6vw, 20px);
            overflow-x: auto;
            overflow-y: hidden;
            overscroll-behavior: contain;
            scrollbar-color: rgba(0, 174, 255, .42) rgba(255, 255, 255, .06);
        }

        .ops-summary {
            padding: clamp(10px, 1.6vw, 20px) clamp(8px, 1.4vw, 16px) 0;
        }

        .ops-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(178px, 1fr));
            gap: clamp(8px, .9vw, 14px);
        }

        .ops-summary-card {
            min-width: 0;
            border-radius: 12px;
            padding: clamp(12px, 1vw, 16px);
            border: 1px solid rgba(255, 255, 255, .10);
            background:
                linear-gradient(180deg,
                    rgba(8, 24, 43, .92),
                    rgba(3, 10, 18, .96));
            box-shadow:
                0 18px 34px rgba(0, 0, 0, .22),
                inset 0 0 22px rgba(0, 174, 255, .025);
        }

        .ops-summary-card.ok {
            border-color: rgba(57, 211, 83, .28);
        }

        .ops-summary-card.warning {
            border-color: rgba(255, 193, 7, .34);
        }

        .ops-summary-card.danger {
            border-color: rgba(255, 56, 56, .38);
        }

        .summary-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-width: 0;
        }

        .summary-title {
            color: #9fb3c8;
            font-size: clamp(10px, .78vw, 12px);
            font-weight: 900;
            letter-spacing: .06em;
            line-height: 1.25;
            text-transform: uppercase;
        }

        .summary-icon {
            flex: 0 0 auto;
            color: #00aeff;
            font-size: clamp(20px, 1.5vw, 26px);
        }

        .ops-summary-card.ok .summary-icon {
            color: #39d353;
        }

        .ops-summary-card.warning .summary-icon {
            color: #ffc107;
        }

        .ops-summary-card.danger .summary-icon {
            color: #ff3838;
        }

        .summary-value-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 10px;
            margin-top: 12px;
        }

        .summary-value {
            color: #fff;
            font-size: clamp(26px, 2vw, 38px);
            font-weight: 1000;
            line-height: .9;
        }

        .summary-percent {
            border-radius: 999px;
            padding: 5px 8px;
            background: rgba(0, 174, 255, .12);
            color: #bde9ff;
            font-size: clamp(11px, .78vw, 13px);
            font-weight: 1000;
            white-space: nowrap;
        }

        .ops-summary-card.ok .summary-percent {
            background: rgba(57, 211, 83, .14);
            color: #9ef3ac;
        }

        .ops-summary-card.warning .summary-percent {
            background: rgba(255, 193, 7, .16);
            color: #ffe08a;
        }

        .ops-summary-card.danger .summary-percent {
            background: rgba(255, 56, 56, .16);
            color: #ffb1b1;
        }

        .summary-detail {
            margin-top: 9px;
            color: #7592ad;
            font-size: clamp(10px, .78vw, 12px);
            font-weight: 800;
            line-height: 1.25;
            text-transform: uppercase;
            overflow-wrap: anywhere;
        }

        .flight-board {
            width: 100%;
            min-width: 1420px;
            height: 100%;
            border-radius: 14px;
            overflow: hidden;
            border: 1px solid rgba(0, 174, 255, .24);
            background:
                linear-gradient(180deg,
                    rgba(6, 20, 36, .96),
                    rgba(4, 10, 18, .98));
            box-shadow:
                0 0 40px rgba(0, 0, 0, .45),
                inset 0 0 30px rgba(0, 174, 255, .03);
            display: flex;
            flex-direction: column;
        }

        .flight-board-header,
        .flight-board-row {
            display: grid;
            grid-template-columns:
                minmax(120px, .75fr)
                minmax(160px, 1fr)
                minmax(105px, .62fr)
                minmax(95px, .55fr)
                minmax(160px, 1fr)
                minmax(160px, 1fr)
                minmax(160px, 1fr)
                minmax(135px, .8fr)
                minmax(135px, .8fr)
                minmax(180px, 1fr);
        }

        .flight-board-header {
            flex: 0 0 auto;
            background:
                linear-gradient(180deg,
                    rgba(8, 36, 64, .98),
                    rgba(6, 24, 44, .98));
            border-bottom: 1px solid rgba(0, 174, 255, .22);
            border-radius: 14px 14px 0 0;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .34);
        }

        .flight-board-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-x: hidden;
            overflow-y: auto;
            scrollbar-color: rgba(0, 174, 255, .42) rgba(255, 255, 255, .06);
        }

        .flight-board-header>div {
            padding: clamp(12px, 1vw, 18px) clamp(10px, .9vw, 14px);
            border-right: 1px solid rgba(255, 255, 255, .06);
            font-size: clamp(11px, .82vw, 15px);
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #e7f4ff;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .flight-board-row {
            position: relative;
            min-height: clamp(150px, 11.2vw, 215px);
            background:
                linear-gradient(90deg,
                    rgba(3, 10, 18, .98),
                    rgba(8, 24, 43, .98));
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .flight-board-row::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 7px;
        }

        .flight-board-row.status-danger::before {
            background: #ff3838;
            box-shadow: 0 0 18px rgba(255, 56, 56, .45);
        }

        .flight-board-row.status-warning::before {
            background: #ffc107;
            box-shadow: 0 0 18px rgba(255, 193, 7, .45);
        }

        .flight-board-row.status-ok::before {
            background: #39d353;
            box-shadow: 0 0 18px rgba(57, 211, 83, .45);
        }

        .flight-board-row.status-pending::before {
            background: #8792a2;
        }

        .flight-cell {
            min-width: 0;
            padding: clamp(12px, 1vw, 18px) clamp(10px, .9vw, 16px);
            border-right: 1px solid rgba(255, 255, 255, .06);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* =========================
                   TEXTOS
                ========================= */

        .flight-main {
            font-size: clamp(17px, 1.28vw, 24px);
            font-weight: 1000;
            line-height: 1.1;
            color: #fff;
            overflow-wrap: anywhere;
        }

        .flight-sub {
            margin-top: 10px;
            color: #58a6ff;
            font-size: clamp(12px, .95vw, 16px);
            font-weight: 700;
        }

        .flight-mini {
            margin-top: 8px;
            color: #9fb3c8;
            font-size: clamp(10px, .78vw, 13px);
            font-weight: 700;
            text-transform: uppercase;
        }

        .flight-transit-time {
            width: fit-content;
            max-width: 100%;
            margin-top: 12px;
            padding: 5px 8px;
            border-radius: 7px;
            border: 1px solid rgba(0, 174, 255, .22);
            background: rgba(0, 174, 255, .10);
            color: #bde9ff;
            line-height: 1.2;
            overflow-wrap: anywhere;
        }

        /* =========================
                   BADGES
                ========================= */

        .flight-badge {
            width: fit-content;
            border-radius: 7px;
            padding: 6px 10px;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
        }

        .badge-grey {
            background: #7d8793;
            color: #fff;
        }

        .badge-yellow {
            background: #ffc107;
            color: #111;
        }

        .badge-green {
            background: #39d353;
            color: #fff;
        }

        /* =========================
                   ETAPAS
                ========================= */

        .stage-box {
            border-radius: 12px;
            min-height: clamp(118px, 8.8vw, 170px);
            padding: clamp(10px, .9vw, 16px);
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .03);
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: all .2s ease;
        }

        .stage-box.ok {
            background: rgba(57, 211, 83, .10);
            border-color: rgba(57, 211, 83, .45);
            box-shadow: inset 0 0 20px rgba(57, 211, 83, .08);
        }

        .stage-box.warning {
            background: rgba(255, 193, 7, .10);
            border-color: rgba(255, 193, 7, .45);
            box-shadow: inset 0 0 20px rgba(255, 193, 7, .06);
        }

        .stage-box.danger {
            background: rgba(255, 56, 56, .10);
            border-color: rgba(255, 56, 56, .55);
            box-shadow: inset 0 0 22px rgba(255, 56, 56, .08);
        }

        .stage-box.pending {
            background: rgba(135, 146, 162, .06);
            border-color: rgba(135, 146, 162, .18);
        }

        .stage-label {
            color: #9aa8b8;
            font-size: clamp(10px, .72vw, 12px);
            text-transform: uppercase;
            font-weight: 900;
            margin-bottom: 2px;
        }

        .stage-time {
            font-size: clamp(16px, 1.15vw, 22px);
            font-weight: 1000;
            line-height: 1;
            color: #fff;
            margin-bottom: 12px;
        }

        .stage-real {
            font-size: clamp(22px, 1.65vw, 30px);
            font-weight: 900;
            line-height: 1;
            color: #fff;
        }

        .stage-pill {
            width: fit-content;
            border-radius: 8px;
            padding: 6px 10px;
            margin-top: 10px;
            font-size: clamp(11px, .82vw, 14px);
            font-weight: 1000;
        }

        .pill-danger {
            background: #ff3838;
            color: #fff;
            box-shadow: 0 0 16px rgba(255, 56, 56, .35);
        }

        .pill-warning {
            background: #ffc107;
            color: #111;
        }

        .pill-ok {
            background: #39d353;
            color: #fff;
        }

        .pill-pending {
            background: #7d8793;
            color: #fff;
        }

        /* =========================
                   SAÍDAS
                ========================= */

        .saida-time {
            font-size: clamp(30px, 2.6vw, 46px);
            font-weight: 1000;
            line-height: .95;
            color: #fff;
        }

        .saida-date {
            font-size: clamp(15px, 1.05vw, 20px);
            color: #9fb3c8;
            margin-bottom: 8px;
            font-weight: 700;
        }

        /* =========================
                   STATUS
                ========================= */

        .status-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
        }

        .status-panel-inner {
            width: 100%;
            border-radius: 12px;
            padding: clamp(14px, 1.35vw, 24px) clamp(12px, 1vw, 18px);
            border: 1px solid rgba(255, 255, 255, .10);
        }

        .status-danger .status-panel-inner {
            background:
                radial-gradient(circle at center,
                    rgba(255, 56, 56, .22),
                    rgba(55, 8, 12, .75));
            border-color: rgba(255, 56, 56, .45);
        }

        .status-warning .status-panel-inner {
            background:
                radial-gradient(circle at center,
                    rgba(255, 193, 7, .18),
                    rgba(55, 38, 0, .75));
            border-color: rgba(255, 193, 7, .45);
        }

        .status-ok .status-panel-inner {
            background:
                radial-gradient(circle at center,
                    rgba(57, 211, 83, .18),
                    rgba(5, 40, 18, .75));
            border-color: rgba(57, 211, 83, .45);
        }

        .status-pending .status-panel-inner {
            background:
                radial-gradient(circle at center,
                    rgba(135, 146, 162, .14),
                    rgba(25, 30, 40, .78));
            border-color: rgba(135, 146, 162, .25);
        }

        .status-icon {
            font-size: clamp(38px, 3.6vw, 68px);
            margin-bottom: 14px;
        }

        .status-danger-text {
            color: #ff3838;
            text-shadow: 0 0 18px rgba(255, 56, 56, .35);
        }

        .status-warning-text {
            color: #ffc107;
            text-shadow: 0 0 18px rgba(255, 193, 7, .28);
        }

        .status-ok-text {
            color: #39d353;
            text-shadow: 0 0 18px rgba(57, 211, 83, .28);
        }

        .status-pending-text {
            color: #8792a2;
        }

        .status-text {
            font-size: clamp(16px, 1.15vw, 22px);
            font-weight: 1000;
            line-height: 1;
            text-transform: uppercase;
        }

        .status-detail {
            margin-top: 14px;
            color: #d2dbe4;
            font-size: clamp(11px, .85vw, 15px);
            font-weight: 800;
            text-transform: uppercase;
        }

        /* =========================
                   FOOTER
                ========================= */

        .ops-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 90;
            background: rgba(2, 10, 18, .92);
            border-top: 1px solid rgba(0, 174, 255, .18);
            backdrop-filter: blur(10px);
            padding: 10px clamp(10px, 1.5vw, 20px);
        }

        .ops-footer-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .ops-legend {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 22px;
            color: #d7e3ef;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .legend-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 7px;
        }

        .dot-ok {
            background: #39d353;
        }

        .dot-warning {
            background: #ffc107;
        }

        .dot-danger {
            background: #ff3838;
        }

        .dot-pending {
            background: #8792a2;
        }

        .ops-footer-brand {
            color: #00aeff;
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
            white-space: nowrap;
        }

        .ops-live-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            display: inline-block;
            margin-right: 7px;
            background: #39d353;
            box-shadow: 0 0 14px rgba(57, 211, 83, .7);
            vertical-align: 1px;
        }

        .ops-live-note {
            color: #7fa6c9;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        @media (min-width: 1420px) {
            .flight-board {
                min-width: 0;
            }
        }

        @media (max-width: 1024px) {
            body.expedicao-tv-mode {
                overflow-y: auto;
            }

            .ops-topbar {
                position: static;
            }

            .ops-topbar-inner {
                align-items: flex-start;
                flex-direction: column;
            }

            .ops-clock {
                width: 100%;
            }

            .ops-board-wrapper {
                height: auto;
                padding-bottom: 120px;
            }

            .ops-summary {
                padding-bottom: 4px;
            }

            .ops-footer-inner {
                align-items: flex-start;
                flex-direction: column;
            }

            .ops-footer-brand {
                white-space: normal;
            }
        }

        @media (max-width: 720px) {
            body.expedicao-tv-mode {
                overflow-x: hidden;
            }

            .ops-title-wrap {
                align-items: flex-start;
                gap: 12px;
            }

            .ops-subtitle {
                line-height: 1.35;
            }

            .ops-board-wrapper {
                height: auto;
                overflow-x: visible;
                padding-bottom: 24px;
            }

            .ops-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .flight-board {
                min-width: 0;
                height: auto;
                overflow: visible;
                border: 0;
                background: transparent;
                box-shadow: none;
            }

            .flight-board-header {
                display: none;
            }

            .flight-board-body {
                overflow: visible;
            }

            .flight-board-row {
                display: grid;
                grid-template-columns: 1fr;
                min-height: 0;
                margin-bottom: 14px;
                overflow: hidden;
                border: 1px solid rgba(0, 174, 255, .22);
                border-radius: 14px;
                background: linear-gradient(180deg, rgba(6, 20, 36, .96), rgba(4, 10, 18, .98));
            }

            .flight-board-row::before {
                width: 5px;
            }

            .flight-cell {
                border-right: 0;
                border-bottom: 1px solid rgba(255, 255, 255, .06);
                padding: 14px 14px 14px 20px;
            }

            .flight-cell:last-child {
                border-bottom: 0;
            }

            .flight-cell::before {
                content: attr(data-label);
                display: block;
                margin-bottom: 8px;
                color: #8fb5d7;
                font-size: 11px;
                font-weight: 900;
                letter-spacing: .06em;
                line-height: 1.2;
                text-transform: uppercase;
            }

            .stage-box {
                min-height: 0;
            }

            .stage-pill,
            .flight-badge {
                max-width: 100%;
                overflow-wrap: anywhere;
            }

            .status-panel {
                align-items: stretch;
            }

            .status-panel-inner {
                display: grid;
                grid-template-columns: auto 1fr;
                align-items: center;
                column-gap: 12px;
                text-align: left;
            }

            .status-icon {
                grid-row: span 2;
                margin-bottom: 0;
            }

            .status-detail {
                margin-top: 4px;
            }

            .ops-footer {
                position: static;
                margin-top: 8px;
            }
        }

        @media (max-width: 420px) {
            .ops-airport-icon {
                display: none;
            }

            .ops-title {
                font-size: 22px;
            }

            .ops-clock strong {
                font-size: 26px;
            }

            .ops-legend {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px 12px;
                width: 100%;
                font-size: 12px;
            }

            .ops-summary-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="ops-topbar">
        <div class="ops-topbar-inner">
            <div class="ops-title-wrap">
                <div class="ops-airport-icon">
                    <i class="mdi mdi-truck-fast-outline"></i>
                </div>

                <div>
                    <div class="ops-kicker">
                        Torre Operacional
                    </div>

                    <div class="ops-title">
                        Previsibilidade da Expedição
                    </div>

                    <div class="ops-subtitle">
                        Painel operacional • previsto x realizado
                    </div>
                </div>
            </div>

            <div class="ops-clock">
                <strong id="opsClock">--:--:--</strong>
                <small id="opsLiveRefresh">Atualizado agora</small>
            </div>
        </div>
    </div>

    <div class="ops-summary">
        <div class="ops-summary-grid">
            @foreach (($resumoOperacional['cards'] ?? []) as $card)
                <div class="ops-summary-card {{ $card['classe'] ?? 'neutral' }}">
                    <div class="summary-head">
                        <div class="summary-title">
                            {{ $card['titulo'] }}
                        </div>

                        <i class="mdi {{ $card['icone'] }} summary-icon"></i>
                    </div>

                    <div class="summary-value-row">
                        <div class="summary-value">
                            {{ $card['valor'] }}
                        </div>

                        <div class="summary-percent">
                            {{ number_format((float) $card['percentual'], 1, ',', '.') }}%
                        </div>
                    </div>

                    <div class="summary-detail">
                        {{ $card['detalhe'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="ops-board-wrapper">
        <div class="flight-board">

            <div class="flight-board-header">
                <div>FO / DT</div>
                <div>Destino</div>
                <div>Agenda</div>
                <div>Tipo</div>
                <div><i class="mdi mdi-package-variant-closed"></i> Separação</div>
                <div><i class="mdi mdi-clipboard-check-outline"></i> Conferência</div>
                <div><i class="mdi mdi-truck-cargo-container"></i> Carregamento</div>
                <div><i class="mdi mdi-calendar-clock"></i> Saída Prevista</div>
                <div><i class="mdi mdi-clock-outline"></i> Saída Projetada</div>
                <div><i class="mdi mdi-flag"></i> Status</div>
            </div>

            <div class="flight-board-body">
                @forelse($programacoes as $programacao)
                    @php
                        $previsao = $programacao->ultimaPrevisao;
                        $etapas = $programacao->etapas_operacionais ?? [];

                        $desvioSaida = (int) ($programacao->desvio_saida_min ?? 0);

                        if ($programacao->status_operacional === 'SEM_EXPLOSAO') {
                            $statusGeral = 'warning';
                            $statusTexto = 'Sem Explosão';
                            $statusIcon = 'mdi-database-alert-outline';
                            $statusDetalhe = 'Aguardando demanda';
                        } elseif ($programacao->status_operacional === 'SEM_ROTA') {
                            $statusGeral = 'warning';
                            $statusTexto = 'Sem Rota';
                            $statusIcon = 'mdi-map-marker-alert-outline';
                            $statusDetalhe = 'Cadastrar rota';
                        } elseif ($programacao->status_operacional === 'SEM_CRITERIO') {
                            $statusGeral = 'warning';
                            $statusTexto = 'Sem Critério';
                            $statusIcon = 'mdi-tune-variant';
                            $statusDetalhe = 'Cadastrar regra';
                        } elseif ($programacao->status_operacional === 'ANOMALIA_OPERACIONAL') {
                            $statusGeral = 'warning';
                            $statusTexto = 'Anomalia';
                            $statusIcon = 'mdi-alert-octagon';
                            $statusDetalhe = 'Processo possivelmente aberto';
                        } elseif ($programacao->agenda_vencida) {
                            $statusGeral = 'danger';
                            $statusTexto = 'Atrasado';
                            $statusIcon = 'mdi-calendar-alert';
                            $statusDetalhe = 'Agenda vencida';
                        } elseif ($programacao->status_saida_projetada === 'FORA_PREVISTO') {
                            $statusGeral = $desvioSaida > 30 ? 'danger' : 'warning';
                            $statusTexto = $desvioSaida > 30 ? 'Atrasado' : 'Atenção';
                            $statusIcon = $desvioSaida > 30 ? 'mdi-truck-alert' : 'mdi-alert-circle';
                            $statusDetalhe = $desvioSaida > 30 ? 'Saída impactada' : 'Em atenção';
                        } elseif (!$programacao->saida_projetada_em) {
                            $statusGeral = 'pending';
                            $statusTexto = 'Pendente';
                            $statusIcon = 'mdi-clock-outline';
                            $statusDetalhe = 'Aguardando etapas';
                        } else {
                            $statusGeral = 'ok';
                            $statusTexto = 'No Prazo';
                            $statusIcon = 'mdi-truck-check';
                            $statusDetalhe = 'Dentro previsto';
                        }

                        $statusClass = 'status-' . $statusGeral;
                    @endphp

                    <div class="flight-board-row {{ $statusClass }}">

                    {{-- FO --}}
                    <div class="flight-cell" data-label="FO / DT">
                        <div class="flight-main">
                            {{ $programacao->fo }}
                        </div>

                        <div class="flight-sub">
                            DT {{ $programacao->dt_sap }}
                        </div>
                    </div>

                    {{-- DESTINO --}}
                    <div class="flight-cell" data-label="Destino">
                        <div class="flight-main">
                            {{ $programacao->cidade_destino }}/{{ $programacao->uf_destino }}
                        </div>

                        <div class="flight-sub">
                            {{ $programacao->uf_destino }}
                        </div>

                        <div class="flight-mini flight-transit-time">
                            Transit time {{ tempoBoard($previsao?->tempo_viagem_min) }}
                        </div>
                    </div>

                    {{-- AGENDA --}}
                    <div class="flight-cell" data-label="Agenda">
                        <div class="flight-main">
                            {{ optional($programacao->agenda_entrega_em)->format('d/m') ?? '--/--' }}
                        </div>

                        <div class="flight-main">
                            {{ optional($programacao->agenda_entrega_em)->format('H:i') ?? '--:--' }}
                        </div>
                    </div>

                    {{-- TIPO --}}
                    <div class="flight-cell" data-label="Tipo">
                        <span class="flight-badge badge-grey">
                            {{ $programacao->tipo_carga ?? '-' }}
                        </span>

                        @if ($previsao)
                            @php
                                $riscoClass = match ($previsao->risco_operacional) {
                                    'BAIXO' => 'badge-green',
                                    'MEDIO' => 'badge-yellow',
                                    default => 'badge-grey',
                                };
                            @endphp

                            <span class="flight-badge {{ $riscoClass }}">
                                {{ $previsao->risco_operacional }}
                            </span>
                        @endif
                    </div>

                    {{-- ETAPAS --}}
                    @foreach ([
            'separacao' => 'Separação',
            'conferencia' => 'Conferência',
            'carregamento' => 'Carregamento',
        ] as $chave => $titulo)
                        @php
                            $etapa = $etapas[$chave] ?? [];
                            $status = $etapa['status'] ?? 'SEM_REALIZADO';
                            $desvio = (int) ($etapa['desvio'] ?? 0);

                            $stageClass = match ($status) {
                                'FORA_PREVISTO' => $desvio > 30 ? 'danger' : 'warning',
                                'DENTRO_PREVISTO' => 'ok',
                                default => 'pending',
                            };
                        @endphp

                        <div class="flight-cell" data-label="{{ $titulo }}">
                            <div class="stage-box {{ $stageClass }}">

                                <div class="stage-label">
                                    Planejado
                                </div>

                                <div class="stage-time">
                                    {{ tempoBoard($etapa['previsto'] ?? null) }}
                                </div>

                                <div class="stage-label">
                                    Executado
                                </div>

                                <div class="stage-real">
                                    {{ tempoBoard($etapa['realizado'] ?? null) }}
                                </div>

                                @if ($status === 'FORA_PREVISTO')
                                    <span class="stage-pill pill-danger">
                                        +{{ tempoBoard($etapa['desvio'] ?? null) }}
                                    </span>
                                @elseif($status === 'DENTRO_PREVISTO')
                                    <span class="stage-pill pill-ok">
                                        {{ $desvio < 0 ? tempoBoard($desvio) : 'OK' }}
                                    </span>
                                @elseif($programacao->status_operacional === 'SEM_EXPLOSAO')
                                    <span class="stage-pill pill-pending">
                                        Sem explosão
                                    </span>
                                @elseif($programacao->status_operacional === 'SEM_CRITERIO')
                                    <span class="stage-pill pill-pending">
                                        Sem critério
                                    </span>
                                @else
                                    <span class="stage-pill pill-pending">
                                        Pendente
                                    </span>
                                @endif

                            </div>
                        </div>
                    @endforeach

                    {{-- SAÍDA PREVISTA --}}
                    <div class="flight-cell" data-label="Saída Prevista">
                        @php
                            $saidaPrevistaClass = $previsao?->previsao_saida_caminhao ? 'ok' : 'pending';
                        @endphp

                        <div class="stage-box {{ $saidaPrevistaClass }}">

                            <div class="stage-label">
                                Planejado
                            </div>

                            <div class="stage-time">
                                {{ $previsao?->previsao_saida_caminhao?->format('d/m') ?? '--/--' }}
                            </div>

                            <div class="stage-label">
                                Horário
                            </div>

                            <div class="stage-real">
                                {{ $previsao?->previsao_saida_caminhao?->format('H:i') ?? '--:--' }}
                            </div>

                            @if ($previsao?->previsao_saida_caminhao)
                                <span class="stage-pill pill-ok">
                                    Previsto
                                </span>
                            @elseif($programacao->status_operacional === 'SEM_EXPLOSAO')
                                <span class="stage-pill pill-pending">
                                    Sem explosão
                                </span>
                            @elseif($programacao->status_operacional === 'SEM_ROTA')
                                <span class="stage-pill pill-pending">
                                    Sem rota
                                </span>
                            @else
                                <span class="stage-pill pill-pending">
                                    Pendente
                                </span>
                            @endif

                        </div>
                    </div>

                    {{-- SAÍDA PROJETADA --}}
                    <div class="flight-cell" data-label="Saída Projetada">
                        @php
                            $saidaClass = match ($statusGeral) {
                                'danger' => 'danger',
                                'warning' => 'warning',
                                'ok' => 'ok',
                                default => 'pending',
                            };
                        @endphp

                        <div class="stage-box {{ $saidaClass }}">

                            <div class="stage-label">
                                Projetado
                            </div>

                            <div class="stage-time">
                                {{ $programacao->saida_projetada_em?->format('d/m') ?? '--/--' }}
                            </div>

                            <div class="stage-label">
                                Horário
                            </div>

                            <div class="stage-real">
                                {{ $programacao->saida_projetada_em?->format('H:i') ?? '--:--' }}
                            </div>

                            @if ($programacao->agenda_vencida)
                                <span class="stage-pill pill-danger">
                                    Vencido
                                </span>
                            @elseif ($desvioSaida > 0)
                                <span class="stage-pill pill-danger">
                                    +{{ tempoBoard($desvioSaida) }}
                                </span>
                            @elseif($programacao->saida_projetada_em)
                                <span class="stage-pill pill-ok">
                                    OK
                                </span>
                            @elseif($programacao->status_operacional === 'SEM_EXPLOSAO')
                                <span class="stage-pill pill-pending">
                                    Sem explosão
                                </span>
                            @elseif($programacao->status_operacional === 'SEM_ROTA')
                                <span class="stage-pill pill-pending">
                                    Sem rota
                                </span>
                            @else
                                <span class="stage-pill pill-pending">
                                    Pendente
                                </span>
                            @endif

                        </div>
                    </div>

                    {{-- STATUS --}}
                    <div class="flight-cell status-panel" data-label="Status">
                        <div class="status-panel-inner">

                            <i class="mdi {{ $statusIcon }} status-icon status-{{ $statusGeral }}-text"></i>

                            <div class="status-text status-{{ $statusGeral }}-text">
                                {{ $statusTexto }}
                            </div>

                            <div class="status-detail">
                                {{ $statusDetalhe }}
                            </div>

                        </div>
                    </div>

                    </div>
                @empty
                    <div class="p-5 text-center text-muted">
                        Nenhuma programação encontrada.
                    </div>
                @endforelse
            </div>

        </div>
    </div>

    <div class="ops-footer">
        <div class="ops-footer-inner">

            <div class="ops-legend">
                <span><span class="legend-dot dot-ok"></span>No prazo</span>
                <span><span class="legend-dot dot-warning"></span>Atenção</span>
                <span><span class="legend-dot dot-danger"></span>Atrasado</span>
                <span><span class="legend-dot dot-pending"></span>Pendente</span>
            </div>

            <div class="ops-footer-brand">
                <span class="ops-live-dot"></span>
                <span id="opsLiveStatus">MODO VIVO • ROLAGEM AUTOMÁTICA</span>
            </div>

        </div>
    </div>

    <script>
        function updateOpsClock() {
            const clock = document.getElementById('opsClock');

            if (!clock) return;

            const now = new Date();

            clock.textContent = now.toLocaleTimeString('pt-BR', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }

        updateOpsClock();

        setInterval(updateOpsClock, 1000);

        const liveRefresh = document.getElementById('opsLiveRefresh');
        const liveStatus = document.getElementById('opsLiveStatus');
        const summaryGrid = document.querySelector('.ops-summary-grid');
        let boardScroller = document.querySelector('.flight-board-body');
        const refreshIntervalMs = 60 * 1000;
        const autoScrollDelayMs = 3500;
        const autoScrollStepPx = 1.1;
        const autoScrollIntervalMs = 30;
        const autoScrollLoopPauseMs = 5000;
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        let autoScrollTimer = null;
        let autoScrollPauseTimer = null;
        let refreshStartedAt = Date.now();
        let syncInProgress = false;

        function syncPanelHeights() {
            const topbar = document.querySelector('.ops-topbar');
            const summary = document.querySelector('.ops-summary');
            const footer = document.querySelector('.ops-footer');

            document.body.style.setProperty('--ops-header-height', `${topbar ? topbar.offsetHeight : 0}px`);
            document.body.style.setProperty('--ops-summary-height', `${summary ? summary.offsetHeight : 0}px`);
            document.body.style.setProperty('--ops-footer-height', `${footer ? footer.offsetHeight : 0}px`);
        }

        function updateLiveRefreshLabel() {
            if (!liveRefresh) return;

            const elapsedSeconds = Math.max(0, Math.floor((Date.now() - refreshStartedAt) / 1000));

            if (syncInProgress) {
                liveRefresh.textContent = 'Sincronizando dados';
                return;
            }

            liveRefresh.textContent = elapsedSeconds < 3
                ? 'Atualizado agora'
                : `Atualizado há ${elapsedSeconds}s`;
        }

        async function syncLivePanel() {
            if (syncInProgress) return;

            syncInProgress = true;
            updateLiveRefreshLabel();

            if (liveStatus) {
                liveStatus.textContent = 'MODO VIVO • SINCRONIZANDO';
            }

            const currentScrollTop = boardScroller ? boardScroller.scrollTop : 0;
            const currentMaxScroll = boardScroller
                ? Math.max(1, boardScroller.scrollHeight - boardScroller.clientHeight)
                : 1;
            const scrollRatio = currentScrollTop / currentMaxScroll;

            try {
                const url = new URL(window.location.href);
                url.searchParams.set('_live', Date.now().toString());

                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    cache: 'no-store'
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const nextSummaryGrid = doc.querySelector('.ops-summary-grid');
                const nextBoardBody = doc.querySelector('.flight-board-body');

                if (summaryGrid && nextSummaryGrid) {
                    summaryGrid.innerHTML = nextSummaryGrid.innerHTML;
                }

                if (boardScroller && nextBoardBody) {
                    boardScroller.innerHTML = nextBoardBody.innerHTML;

                    const nextMaxScroll = Math.max(0, boardScroller.scrollHeight - boardScroller.clientHeight);
                    boardScroller.scrollTop = Math.min(nextMaxScroll, Math.round(nextMaxScroll * scrollRatio));
                }

                refreshStartedAt = Date.now();
                syncPanelHeights();
                startAutoScroll();
            } catch (error) {
                if (liveStatus) {
                    liveStatus.textContent = 'MODO VIVO • FALHA NA SINCRONIZAÇÃO';
                }
            } finally {
                syncInProgress = false;
                updateLiveRefreshLabel();
            }
        }

        function canAutoScroll() {
            return window.innerWidth > 720 &&
                !reduceMotion.matches &&
                boardScroller &&
                boardScroller.scrollHeight > boardScroller.clientHeight + 160;
        }

        function stopAutoScroll() {
            if (autoScrollTimer) {
                clearInterval(autoScrollTimer);
                autoScrollTimer = null;
            }

            if (autoScrollPauseTimer) {
                clearTimeout(autoScrollPauseTimer);
                autoScrollPauseTimer = null;
            }
        }

        function startAutoScroll() {
            stopAutoScroll();

            if (!canAutoScroll()) {
                if (liveStatus) {
                    liveStatus.textContent = 'MODO VIVO • ATUALIZAÇÃO AUTOMÁTICA';
                }

                return;
            }

            if (liveStatus) {
                liveStatus.textContent = 'MODO VIVO • ROLAGEM AUTOMÁTICA';
            }

            autoScrollPauseTimer = setTimeout(() => {
                autoScrollTimer = setInterval(() => {
                    const maxScroll = boardScroller.scrollHeight - boardScroller.clientHeight;

                    if (boardScroller.scrollTop >= maxScroll - 2) {
                        stopAutoScroll();

                        autoScrollPauseTimer = setTimeout(() => {
                            boardScroller.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });

                            autoScrollPauseTimer = setTimeout(startAutoScroll, 3200);
                        }, autoScrollLoopPauseMs);

                        return;
                    }

                    boardScroller.scrollTop += autoScrollStepPx;
                }, autoScrollIntervalMs);
            }, autoScrollDelayMs);
        }

        syncPanelHeights();
        updateLiveRefreshLabel();
        startAutoScroll();

        setInterval(updateLiveRefreshLabel, 1000);
        setInterval(syncLivePanel, refreshIntervalMs);

        window.addEventListener('resize', () => {
            syncPanelHeights();
            boardScroller = document.querySelector('.flight-board-body');
            startAutoScroll();
        });

        window.addEventListener('load', () => {
            syncPanelHeights();
            boardScroller = document.querySelector('.flight-board-body');
            startAutoScroll();
        });
    </script>
@endsection
