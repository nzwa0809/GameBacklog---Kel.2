<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GameBacklog - Menu</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* SEARCH BAR */
        .wii-search-wrapper {
            display: flex;
            justify-content: center;
            width: 100%;
            padding: 15px 20px 25px;
        }

        .wii-search-form {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            max-width: 720px;
        }

        .wii-search-input-wrap {
            position: relative;
            flex: 1;
            min-width: 0;
        }

        .wii-search-input-wrap .wii-search-input {
            width: 100%;
        }

        .wii-search-input {
            flex: 1;
            min-width: 0;
            padding: 13px 18px;
            border: 1px solid #58cfff;
            border-radius: 30px;
            outline: none;
            background: rgba(15, 35, 50, 0.9);
            color: #fff;
            font-size: 14px;
            transition: 0.2s;
        }

        .wii-search-input:focus {
            border-color: #65dfff;
            box-shadow: 0 0 10px rgba(70, 200, 255, 0.3);
        }

        .wii-search-input::placeholder {
            color: #a7c4d5;
        }

        .wii-search-button,
        .wii-search-clear {
            padding: 12px 20px;
            border-radius: 30px;
            border: 1px solid #58cfff;
            background: rgba(25, 65, 85, 0.9);
            color: #70dcff;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: bold;
            white-space: nowrap;
            transition: 0.2s;
        }

        .wii-search-button:hover,
        .wii-search-clear:hover {
            background: #48bce8;
            color: #102330;
        }

        /* LIVE SEARCH DROPDOWN */
        .wii-live-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: rgba(16, 36, 52, 0.98);
            border: 1px solid rgba(88, 207, 255, 0.55);
            border-radius: 16px;
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.45);
            z-index: 60;
            overflow: hidden;
            display: none;
        }

        .wii-live-dropdown.active {
            display: block;
        }

        .wii-live-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            text-decoration: none;
            color: #e6f6ff;
            border-bottom: 1px solid rgba(88, 207, 255, 0.14);
            transition: background 0.15s ease;
        }

        .wii-live-item:last-child {
            border-bottom: none;
        }

        .wii-live-item:hover {
            background: rgba(56, 165, 215, 0.22);
        }

        .wii-live-thumb {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            object-fit: cover;
            background: rgba(30, 70, 95, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }

        .wii-live-meta {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .wii-live-title {
            font-size: 14px;
            font-weight: 600;
            color: #ffffff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wii-live-sub {
            font-size: 12px;
            color: #7cc8ea;
        }

        /* FILTERS & SORT MODAL */
        .wii-filter-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(5, 15, 24, 0.72);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 120;
            padding: 16px;
        }

        .wii-filter-modal-backdrop.active {
            display: flex;
        }

        .wii-filter-modal {
            width: 100%;
            max-width: 560px;
            background: #0f293d;
            border: 1px solid rgba(88, 207, 255, 0.45);
            border-radius: 18px;
            padding: 22px;
            color: #e9f7ff;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.55);
            max-height: 90vh;
            overflow-y: auto;
        }

        .wii-filter-modal-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .wii-filter-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            color: #79c8eb;
            margin-bottom: 8px;
            display: block;
        }

        .wii-chip-group {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 16px;
        }

        .wii-chip {
            padding: 6px 13px;
            border-radius: 999px;
            border: 1px solid rgba(88, 207, 255, 0.35);
            background: rgba(20, 52, 74, 0.85);
            color: #cdeaff;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .wii-chip.active {
            background: #28a9e0;
            border-color: #65dfff;
            color: #071924;
            font-weight: 700;
        }

        .wii-filter-modal-foot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            gap: 10px;
        }

        .wii-search-result {
            text-align: center;
            color: #a8d8ef;
            margin: 0 15px 20px;
            font-size: 14px;
        }

        .wii-no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 45px 20px;
            color: #b5d9eb;
        }

        .wii-no-results h3 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #62d8ff;
        }

        @media (max-width: 600px) {
            .wii-search-form {
                flex-wrap: wrap;
            }

            .wii-search-input-wrap {
                flex-basis: 100%;
            }

            .wii-search-button,
            .wii-search-clear {
                flex: 1;
                text-align: center;
            }
        }

        /* WII MENU PAGINATION: tombol berada di luar sisi card */
        .wii-menu {
            position: relative;
            overflow: visible !important;
        }

        .channel-grid {
            position: relative;
            z-index: 1;
        }

        .page-arrows {
            position: absolute;
            top: 50%;
            left: 50% !important;
            right: auto !important;
            width: calc(100% + 112px);
            max-width: calc(100vw - 40px);
            box-sizing: border-box;
            transform: translate(-50%, -50%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 20;
            pointer-events: none;
        }

        .page-arrow {
            width: 42px;
            height: 54px;
            flex: 0 0 42px;
            display: flex;
            justify-content: center;
            align-items: center;
            border: 1px solid rgba(88, 207, 255, 0.55);
            border-radius: 24px;
            background: rgba(18, 48, 65, 0.94);
            color: #58cfff;
            font-size: 34px;
            line-height: 1;
            text-decoration: none;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.2);
            transition: background 0.2s ease, transform 0.2s ease,
                        opacity 0.2s ease;
            pointer-events: auto;
        }

        .page-arrow:hover:not(.disabled) {
            background: #58cfff;
            color: #102330;
            transform: scale(1.08);
        }

        .page-arrow.disabled {
            opacity: 0.22;
            cursor: default;
        }

        @media (max-width: 600px) {
            .page-arrows {
                width: calc(100% + 12px);
                max-width: calc(100vw - 24px);
                top: auto;
                bottom: -58px;
                transform: translateX(-50%);
            }

            .page-arrow {
                width: 38px;
                height: 42px;
                flex-basis: 38px;
                font-size: 28px;
            }
        }

    </style>
</head>

<body class="wii-body" data-theme="classic">

<main class="wii-screen">

    {{-- TOP INFORMATION --}}
    <div class="wii-top">

        <span class="wii-brand">GAMEBACKLOG</span>

        <div class="wii-top-actions">

{{-- Add Game, Export & Import: Admin Only --}}
            @if (auth()->user()->role === 'admin')
                <a href="{{ route('games.create') }}"
                   class="wii-menu-button add-game">
                    + Add Game
                </a>

            @endif

            {{-- Refresh --}}
            <button type="button"
                    class="wii-menu-button refresh-game"
                    onclick="window.location.reload()">
                ↻ Refresh
            </button>

            {{-- Personal Library: User Only --}}
            @if (auth()->user()->role !== 'admin')
                <a href="{{ route('games.library') }}"
                   class="wii-menu-button library-button">
                    🎮 My Library
                </a>
            @endif

            {{-- Profile --}}
            <a href="{{ url('/dashboard') }}"
               class="wii-menu-button profile-button">
                👤 Profile
            </a>

             <a href="{{ url('/rawg-catalog') }}" class="wii-api-button">
                🌐 Browse Game
            </a>

            {{-- Logout --}}
            <form action="{{ url('/logout') }}"
                  method="POST"
                  class="logout-form">
                @csrf

                <button type="submit"
                        class="wii-menu-button logout-button">
                    ↪ Logout
                </button>
            </form>

            {{-- Game Counter --}}
            <span class="wii-counter">
                {{ $games->total() }} Games
            </span>

            {{-- Theme Toggle --}}
            <button type="button"
                    class="theme-toggle"
                    id="wii-theme-toggle">
                🌙
            </button>

        </div>

    </div>


    {{-- SEARCH, FILTER & SORT GAME --}}
    <div class="wii-search-wrapper">

        <form action="{{ route('games.index') }}"
              method="GET"
              id="wii-filter-form"
              class="wii-search-form">

            <input type="hidden" name="genre_id" id="wii-genre-input" value="{{ request('genre_id') }}">
            <input type="hidden" name="platform_id" id="wii-platform-input" value="{{ request('platform_id') }}">
            <input type="hidden" name="sort" id="wii-sort-input" value="{{ request('sort', 'title_asc') }}">

            <div class="wii-search-input-wrap">
                <input
                    type="search"
                    name="search"
                    id="wii-live-search"
                    class="wii-search-input"
                    placeholder="🔍 Search your favorite games..."
                    value="{{ request('search') }}"
                    autocomplete="off"
                >
                <div class="wii-live-dropdown" id="wii-live-dropdown"></div>
            </div>

            <button type="submit"
                    class="wii-search-button">
                🔍 Search
            </button>

            <button type="button"
                    class="wii-search-button"
                    id="wii-open-filter-modal">
                ☰ Filter
            </button>

            @if (request()->anyFilled(['search', 'genre_id', 'platform_id', 'sort']))
                <a href="{{ route('games.index') }}"
                   class="wii-search-clear">
                    ✕ Clear
                </a>
            @endif

        </form>

    </div>


    {{-- MODAL FILTERS & SORT --}}
    <div class="wii-filter-modal-backdrop" id="wii-filter-modal">
        <div class="wii-filter-modal">
            <div class="wii-filter-modal-head">
                <h3 style="margin: 0; font-size: 17px;">Filters & Sort</h3>
                <button type="button" id="wii-close-filter-modal"
                        style="background: none; border: none; color: #9ad4ee; font-size: 20px; cursor: pointer;">✕</button>
            </div>

            <span class="wii-filter-label">SORT BY</span>
            <div class="wii-chip-group" id="wii-sort-chips">
                <button type="button" class="wii-chip {{ request('sort', 'title_asc') === 'title_asc' ? 'active' : '' }}" data-sort="title_asc">A–Z</button>
                <button type="button" class="wii-chip {{ request('sort') === 'newest' ? 'active' : '' }}" data-sort="newest">Newest Release</button>
                <button type="button" class="wii-chip {{ request('sort') === 'latest_id' ? 'active' : '' }}" data-sort="latest_id">Latest Added</button>
                <button type="button" class="wii-chip {{ request('sort') === 'title_desc' ? 'active' : '' }}" data-sort="title_desc">Z–A</button>
            </div>

            <span class="wii-filter-label">GENRES</span>
            <input type="text" id="wii-genre-search" class="wii-search-input"
                   placeholder="Search genres..." style="width: 100%; padding: 9px 14px; font-size: 13px; margin-bottom: 10px;">
            <div class="wii-chip-group" id="wii-genre-chips">
                <button type="button" class="wii-chip {{ request('genre_id') ? '' : 'active' }}" data-genre="">All Genres</button>
                @foreach ($genres ?? [] as $genre)
                    <button type="button"
                            class="wii-chip {{ (string) request('genre_id') === (string) $genre->id ? 'active' : '' }}"
                            data-genre="{{ $genre->id }}"
                            data-genre-name="{{ strtolower($genre->name) }}">
                        {{ $genre->name }}
                    </button>
                @endforeach
            </div>

            <span class="wii-filter-label">PLATFORMS</span>
            <div class="wii-chip-group" id="wii-platform-chips">
                <button type="button" class="wii-chip {{ request('platform_id') ? '' : 'active' }}" data-platform="">All Platforms</button>
                @foreach ($platforms ?? [] as $platform)
                    <button type="button"
                            class="wii-chip {{ (string) request('platform_id') === (string) $platform->id ? 'active' : '' }}"
                            data-platform="{{ $platform->id }}">
                        {{ $platform->name }}
                    </button>
                @endforeach
            </div>

            <div class="wii-filter-modal-foot">
                <button type="button" class="wii-search-clear" id="wii-reset-filters">Reset All</button>
                <button type="button" class="wii-search-button" id="wii-apply-filters">Apply Filters</button>
            </div>
        </div>
    </div>


    {{-- SEARCH RESULT INFORMATION --}}
    @if (request('search'))

        <div class="wii-search-result">

            @if ($games->total() > 0)

                Menampilkan {{ $games->total() }}
                hasil untuk
                <strong>"{{ request('search') }}"</strong>

            @else

                Tidak ditemukan game dengan judul
                <strong>"{{ request('search') }}"</strong>

            @endif

        </div>

    @endif


    {{-- CHANNEL MENU --}}
    @if (session('success'))
        <div class="wii-search-result" style="color: #65ff9f; font-weight: bold;">
            ✓ {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('file'))
        <div class="wii-search-result" style="color: #ff7b7b; font-weight: bold;">
            ✕ {{ $errors->first('file') }}
        </div>
    @endif

    @if (auth()->user()->role === 'admin')
        <div class="wii-opening-overlay" id="wii-import-modal"
             onclick="if(event.target === this) this.classList.remove('active')">
            <div class="wii-opening-panel" style="padding: 28px; max-width: 460px;">
                <div class="wii-opening-header">IMPORT KATALOG (ADMIN)</div>
                <h2 class="wii-opening-title" style="margin-bottom: 10px;">Upload Katalog Game</h2>
                <p style="font-size: 13px; color: #a8d8ef; margin-bottom: 18px; line-height: 1.5;">
                    Kolom: <code>Judul Game</code>, <code>Genre</code>, <code>Platform</code>, <code>Developer</code>, <code>Publisher</code>, <code>Tanggal Rilis</code>, <code>Deskripsi</code>.
                </p>
                <form action="{{ route('games.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required class="wii-search-input" style="width: 100%; margin-bottom: 20px; border-radius: 14px;">
                    <div class="wii-opening-actions">
                        <button type="button" class="wii-action-button" onclick="document.getElementById('wii-import-modal').classList.remove('active')">Cancel</button>
                        <button type="submit" class="wii-action-button primary">📤 Upload & Import</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <section class="wii-menu">

        <div class="channel-grid">

            @forelse ($games as $game)

                {{-- GAME CHANNEL --}}
                <div class="channel"

                    @if (auth()->user()->role === 'admin')
                        data-delete-url="{{ route('games.destroy', $game->id) }}"
                    @endif

                    data-show-url="{{ route('games.show', $game->id) }}">

                    {{-- EDIT BUTTON: ADMIN ONLY --}}
                    @if (auth()->user()->role === 'admin')

                        <button type="button"
                                class="wii-edit-button"
                                data-edit-url="{{ route('games.edit', $game->id) }}"
                                title="Edit {{ $game->title }}">
                            ✎
                        </button>

                    @endif


                    {{-- GAME PREVIEW --}}
                    <div class="channel-preview">

                        @if ($game->cover_image)

                            @php

                                $coverUrl = \Illuminate\Support\Str::startsWith(
                                    $game->cover_image,
                                    ['http://', 'https://', 'data:image']
                                )
                                    ? $game->cover_image
                                    : asset('storage/' . $game->cover_image);

                            @endphp

                            <img
                                src="{{ $coverUrl }}"
                                alt="{{ $game->title }}"
                                loading="lazy"
                                onerror="this.style.display='none'"
                            >

                        @else

                            <div class="empty-channel">
                                <span>🎮</span>
                            </div>

                        @endif

                        <div class="channel-gloss"></div>

                    </div>


                    {{-- GAME TITLE --}}
                    <div class="channel-label">
                        {{ $game->title }}
                    </div>

                </div>

            @empty

                {{-- NO SEARCH RESULTS --}}
                <div class="wii-no-results">

                    <h3>🎮 Game Not Found</h3>

                    <p>
                        Maaf, game yang kamu cari tidak ditemukan.
                    </p>

                    <p>
                        Coba gunakan judul game lainnya.
                    </p>

                </div>

            @endforelse


            {{-- EMPTY CHANNELS --}}
            @if ($games->count() > 0)

                @for ($i = $games->count(); $i < 12; $i++)

                    <div class="channel empty-slot">
                        <div class="empty-channel"></div>
                    </div>

                @endfor

            @endif

        </div>


        {{-- PAGE NAVIGATION --}}
        <div class="page-arrows">

            @if ($games->appends(request()->query())->previousPageUrl())

                <a href="{{ $games->appends(request()->query())->previousPageUrl() }}"
                   class="page-arrow previous">
                    ‹
                </a>

            @else

                <span class="page-arrow disabled">
                    ‹
                </span>

            @endif


            @if ($games->appends(request()->query())->nextPageUrl())

                <a href="{{ $games->appends(request()->query())->nextPageUrl() }}"
                   class="page-arrow next">
                    ›
                </a>

            @else

                <span class="page-arrow disabled">
                    ›
                </span>

            @endif

        </div>

    </section>

</main>


{{-- WII BOTTOM DOCK --}}
<footer class="wii-dock">

    <div class="dock-left">
        <div class="wii-circle">Wii</div>
    </div>

    <div class="dock-center">

        <div class="wii-time">
            {{ now()->format('H:i') }}
            <small>{{ now()->format('A') }}</small>
        </div>

        <div class="wii-date">
            {{ now()->format('D d/m') }}
        </div>

    </div>

    <div class="dock-right">

        @if (auth()->user()->role === 'admin')

            {{-- TOOLS ADMIN --}}
            <div class="dock-tools">

                <button type="button"
                        class="tools-circle"
                        id="wii-tools-toggle"
                        aria-expanded="false"
                        aria-label="Open admin tools"
                        title="Tools">
                    ⚙
                </button>

                <div class="tools-popup" id="wii-tools-popup">
                    <div class="tools-popup-title">GAMEBACKLOG TOOLS</div>

                    <a href="{{ route('games.export') }}" class="tools-option">
                        <span>📥</span>
                        <span>Export Excel</span>
                    </a>

                    <a href="{{ route('games.export.pdf') }}" class="tools-option">
                        <span>📄</span>
                        <span>Export PDF Catalog</span>
                    </a>

                    <button type="button" class="tools-option" id="wii-open-import">
                        <span>📤</span>
                        <span>Import Excel</span>
                    </button>
                </div>

            </div>

        @else

            {{-- MAIL PLACEHOLDER USER --}}
            <button type="button"
                    class="tools-circle user-mail-placeholder"
                    title="Mail - Coming Soon"
                    aria-label="Mail coming soon"
                    disabled>
                ✉
            </button>

        @endif

    </div>

</footer>


{{-- WII CHANNEL OPENING SYSTEM --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    // =================================
    // WII DOCK TOOLS MENU (ADMIN ONLY)
    // =================================

    const toolsToggle = document.getElementById('wii-tools-toggle');
    const toolsPopup = document.getElementById('wii-tools-popup');
    const openImport = document.getElementById('wii-open-import');
    const importModal = document.getElementById('wii-import-modal');

    if (toolsToggle && toolsPopup) {
        toolsToggle.addEventListener('click', function (event) {
            event.stopPropagation();

            const isOpen = toolsPopup.classList.toggle('active');
            toolsToggle.setAttribute('aria-expanded', String(isOpen));
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.dock-tools')) {
                toolsPopup.classList.remove('active');
                toolsToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    if (openImport && importModal) {
        openImport.addEventListener('click', function () {
            toolsPopup?.classList.remove('active');
            toolsToggle?.setAttribute('aria-expanded', 'false');
            importModal.classList.add('active');
        });
    }

    // =================================
    // 0. LIVE SEARCH DROPDOWN & FILTER MODAL
    // =================================

    const liveSearchInput =
        document.getElementById('wii-live-search');

    const liveDropdown =
        document.getElementById('wii-live-dropdown');

    const filterForm =
        document.getElementById('wii-filter-form');

    let searchDebounce = null;

    if (liveSearchInput && liveDropdown) {

        liveSearchInput.addEventListener('input', function () {

            const q = this.value.trim();
            clearTimeout(searchDebounce);

            if (q.length < 1) {
                liveDropdown.classList.remove('active');
                liveDropdown.innerHTML = '';
                return;
            }

            searchDebounce = setTimeout(async function () {

                try {
                    const res = await fetch(`{{ route('games.index') }}?ajax=1&search=${encodeURIComponent(q)}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();
                    const list = data.games || [];

                    if (!list.length) {
                        liveDropdown.innerHTML = `<div class="wii-live-item"><div class="wii-live-meta"><span class="wii-live-sub">Game tidak ditemukan</span></div></div>`;
                        liveDropdown.classList.add('active');
                        return;
                    }

                    liveDropdown.innerHTML = list.map(item => `
                        <a href="${item.show_url}" class="wii-live-item">
                            ${item.cover_url
                                ? `<img src="${item.cover_url}" alt="${item.title}" class="wii-live-thumb">`
                                : `<div class="wii-live-thumb">🎮</div>`
                            }
                            <div class="wii-live-meta">
                                <span class="wii-live-title">${item.title}</span>
                                <span class="wii-live-sub">${item.genre}${item.year ? ' · ' + item.year : ''}</span>
                            </div>
                        </a>
                    `).join('');
                    liveDropdown.classList.add('active');
                } catch (e) {
                    liveDropdown.classList.remove('active');
                }

            }, 220);

        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.wii-search-input-wrap')) {
                liveDropdown.classList.remove('active');
            }
        });

    }

    const filterModal = document.getElementById('wii-filter-modal');
    const openFilterBtn = document.getElementById('wii-open-filter-modal');
    const closeFilterBtn = document.getElementById('wii-close-filter-modal');
    const applyFilterBtn = document.getElementById('wii-apply-filters');
    const resetFilterBtn = document.getElementById('wii-reset-filters');
    const genreSearchInput = document.getElementById('wii-genre-search');
    const sortInput = document.getElementById('wii-sort-input');
    const genreInput = document.getElementById('wii-genre-input');
    const platformInput = document.getElementById('wii-platform-input');

    if (openFilterBtn && filterModal) {
        openFilterBtn.addEventListener('click', function () {
            filterModal.classList.add('active');
        });
    }

    if (closeFilterBtn && filterModal) {
        closeFilterBtn.addEventListener('click', function () {
            filterModal.classList.remove('active');
        });
    }

    if (filterModal) {
        filterModal.addEventListener('click', function (event) {
            if (event.target === filterModal) {
                filterModal.classList.remove('active');
            }
        });
    }

    document.querySelectorAll('#wii-sort-chips .wii-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            document.querySelectorAll('#wii-sort-chips .wii-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            if (sortInput) sortInput.value = this.dataset.sort;
        });
    });

    document.querySelectorAll('#wii-genre-chips .wii-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            document.querySelectorAll('#wii-genre-chips .wii-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            if (genreInput) genreInput.value = this.dataset.genre;
        });
    });

    document.querySelectorAll('#wii-platform-chips .wii-chip').forEach(chip => {
        chip.addEventListener('click', function () {
            document.querySelectorAll('#wii-platform-chips .wii-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            if (platformInput) platformInput.value = this.dataset.platform;
        });
    });

    if (genreSearchInput) {
        genreSearchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase().trim();
            document.querySelectorAll('#wii-genre-chips .wii-chip').forEach(chip => {
                if (!chip.dataset.genreName) return;
                chip.style.display = chip.dataset.genreName.includes(term) ? '' : 'none';
            });
        });
    }

    if (applyFilterBtn && filterForm) {
        applyFilterBtn.addEventListener('click', function () {
            filterForm.submit();
        });
    }

    if (resetFilterBtn) {
        resetFilterBtn.addEventListener('click', function () {
            if (sortInput) sortInput.value = 'title_asc';
            if (genreInput) genreInput.value = '';
            if (platformInput) platformInput.value = '';
            if (liveSearchInput) liveSearchInput.value = '';
            window.location.href = `{{ route('games.index') }}`;
        });
    }


    // =================================
    // 1. WII THEME SWITCHER
    // =================================

    const themeToggle =
        document.getElementById('wii-theme-toggle');

    const savedTheme =
        localStorage.getItem('wii-theme') || 'classic';


    function applyTheme(theme) {

        document.body.dataset.theme = theme;

        localStorage.setItem('wii-theme', theme);

        if (theme === 'midnight') {

            themeToggle.textContent = '☀️';
            themeToggle.title = 'Aktifkan Wii Classic';

        } else {

            themeToggle.textContent = '🌙';
            themeToggle.title = 'Aktifkan Wii Midnight';

        }

    }


    if (themeToggle) {

        applyTheme(savedTheme);

        themeToggle.addEventListener('click', function () {

            const currentTheme =
                document.body.dataset.theme;

            const newTheme =
                currentTheme === 'classic'
                    ? 'midnight'
                    : 'classic';

            applyTheme(newTheme);

        });

    }


    // =================================
    // 2. EDIT GAME BUTTON
    // =================================

    document.querySelectorAll('.wii-edit-button')
        .forEach(button => {

            button.addEventListener('click', function(event) {

                event.preventDefault();
                event.stopPropagation();

                const editUrl = this.dataset.editUrl;

                if (editUrl) {
                    window.location.href = editUrl;
                }

            });

        });


    // =================================
    // 3. CREATE WII OPENING OVERLAY
    // =================================

    const overlay = document.createElement('div');

    overlay.className = 'wii-opening-overlay';

    overlay.innerHTML = `

        <div class="wii-opening-panel">

            <div class="wii-opening-backdrop"></div>

            <div class="wii-opening-header">
                GAMEBACKLOG
            </div>

            <div class="wii-opening-cover"></div>

            <h2 class="wii-opening-title"></h2>

            <div class="wii-opening-actions">

                <button class="wii-action-button"
                        id="wii-back">
                    Back
                </button>

                <button class="wii-action-button primary"
                        id="wii-start">
                    View Details
                </button>

                @if (auth()->user()->role === 'admin')

                    <form id="wii-delete-form"
                          method="POST"
                          action="">

                        @csrf
                        @method('DELETE')

                        <button type="submit"
                                class="wii-action-button"
                                id="wii-delete">
                            Delete Game
                        </button>

                    </form>

                @endif

            </div>

        </div>

    `;

    document.body.appendChild(overlay);


    // =================================
    // 4. OVERLAY ELEMENTS
    // =================================

    const titleElement =
        overlay.querySelector('.wii-opening-title');

    const coverElement =
        overlay.querySelector('.wii-opening-cover');

    const backdropElement =
        overlay.querySelector('.wii-opening-backdrop');

    let selectedGame = null;

    const deleteForm =
        document.getElementById('wii-delete-form');


    // =================================
    // 5. OPEN GAME CHANNEL
    // =================================

    document.addEventListener('click', function(event) {

        const channel =
            event.target.closest('.channel');

        if (!channel) return;

        if (channel.classList.contains('empty-slot')) {
            return;
        }

        const title =
            channel.querySelector('.channel-label');

        if (!title) return;

        event.preventDefault();

        selectedGame = channel;

        // Set Delete URL untuk Admin
        if (deleteForm) {

            deleteForm.action =
                channel.dataset.deleteUrl || '';

        }

        // Set judul game
        titleElement.textContent =
            title.textContent.trim();

        // Ambil cover game
        const image =
            channel.querySelector('img');

        if (image && image.src && image.style.display !== 'none') {

            const coverUrl = `url("${image.src}")`;

            coverElement.style.backgroundImage = coverUrl;

            backdropElement.style.backgroundImage = coverUrl;

        } else {

            coverElement.style.backgroundImage = '';

            backdropElement.style.backgroundImage = '';

        }

        overlay.classList.add('active');

    });


    // =================================
    // 6. BACK BUTTON
    // =================================

    document.getElementById('wii-back')
        .addEventListener('click', function() {

            overlay.classList.remove('active');

            selectedGame = null;

        });


    // =================================
    // 7. VIEW DETAILS BUTTON
    // =================================

    document.getElementById('wii-start')
        .addEventListener('click', function() {

            if (!selectedGame) return;

            const detailUrl =
                selectedGame.dataset.showUrl;

            if (detailUrl) {

                window.location.href = detailUrl;

            }

        });


    // =================================
    // 8. CLICK OUTSIDE OVERLAY
    // =================================

    overlay.addEventListener('click', function(event) {

        if (event.target === overlay) {

            overlay.classList.remove('active');

            selectedGame = null;

        }

    });


    // =================================
    // 9. ESCAPE KEY
    // =================================

    document.addEventListener('keydown', function(event) {

        if (event.key === 'Escape') {

            overlay.classList.remove('active');

            if (filterModal) {
                filterModal.classList.remove('active');
            }

            selectedGame = null;

        }

    });

    // =================================
    // 10. DELETE GAME CONFIRMATION
    // =================================

    if (deleteForm) {

        deleteForm.addEventListener('submit', function(event) {

            const confirmed = confirm(
                'Apakah kamu yakin ingin menghapus game ini?'
            );

            if (!confirmed) {

                event.preventDefault();

            }
            
        });

    }

    // =================================
    // 11. WII REAL-TIME LOCAL CLOCK
    // =================================

    const timeEl = document.querySelector('.wii-time');
    const dateEl = document.querySelector('.wii-date');

    function updateWiiClock() {
        const now = new Date(); // Otomatis membaca jam & zona waktu lokasi perangkat user

        let hours = now.getHours();
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12; // Jam 0 menjadi 12
        const formattedHours = String(hours).padStart(2, '0');

        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const dayName = days[now.getDay()];
        const day = String(now.getDate()).padStart(2, '0');
        const month = String(now.getMonth() + 1).padStart(2, '0');

        if (timeEl) {
            timeEl.innerHTML = `${formattedHours}:${minutes}\n            <small>${ampm}</small>`;
        }

        if (dateEl) {
            dateEl.textContent = `${dayName} ${day}/${month}`;
        }
    }

    updateWiiClock();
    setInterval(updateWiiClock, 1000);

});

</script>

</body>
</html>
