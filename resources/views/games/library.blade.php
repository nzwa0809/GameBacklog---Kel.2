<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Library - GameBacklog</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="wii-body" data-theme="classic">

<div class="library-page">

    <!-- Header -->
    <div class="library-header">

        <div>
            <a href="{{ route('games.index') }}" class="wii-back-button">
                ← Menu
            </a>
        </div>

        <div class="library-heading">
            <span class="library-icon">🎮</span>
            <h1>My Personal Library</h1>
            <p>Koleksi game pribadi kamu di GameBacklog.</p>
        </div>

        <button type="button"
                class="theme-toggle"
                id="wii-theme-toggle"
                aria-label="Ganti tema">
            🌙
        </button>

    </div>

    <!-- Ringkasan koleksi -->
    <div class="library-summary">
        <div class="library-summary-card">
            <span>Total Games</span>
            <strong>{{ $userGames->total() }}</strong>
        </div>

        <div class="library-summary-card">
            <span>Total Playtime</span>
            <strong>{{ $totalPlaytime ?? 0 }} Hours</strong>
        </div>
    </div>

    <!-- Library Statistics (Sisi User) -->
    <section class="library-content" style="margin-bottom: 20px;">
        <div class="library-section-heading">
            <div>
                <h2>Library Statistics</h2>
                <span>Playtime & Status Overview</span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; padding: 14px 0;">
            <div class="library-summary-card" style="display: block; text-align: left; padding: 16px;">
                <h3 style="margin-bottom: 10px; font-size: 14px; color: #58cfff;">Top 5 Most Played Games (Hours)</h3>
                <div style="position: relative; height: 200px; width: 100%;">
                    <canvas id="userTopPlaytimeChart"></canvas>
                </div>
            </div>

            <div class="library-summary-card" style="display: block; text-align: left; padding: 16px;">
                <h3 style="margin-bottom: 10px; font-size: 14px; color: #58cfff;">Game Status Distribution</h3>
                <div style="position: relative; height: 200px; width: 100%;">
                    <canvas id="userStatusChart"></canvas>
                </div>
            </div>
        </div>
    </section>

    <!-- Daftar game -->
    <section class="library-content">

        @if (session('success'))
            <div class="wii-success-message">
                {{ session('success') }}
            </div>
        @endif

@if ($errors->has('file'))
            <div class="wii-success-message" style="background: rgba(255, 80, 80, 0.2); border-color: #ff7b7b; color: #ffb3b3;">
                ✕ {{ $errors->first('file') }}
            </div>
        @endif

        <div class="library-section-heading" style="flex-wrap: wrap; gap: 10px;">
            <div>
                <h2>My Games</h2>
                <span>{{ $userGames->total() }} Games</span>
            </div>

            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('games.library.export') }}" class="wii-back-button">
                    📥 Export Excel
                </a>

                <a href="{{ route('games.library.export.pdf') }}" class="wii-back-button">
                    📄 Export PDF
                </a>

                <button type="button"
                        class="wii-back-button"
                        style="cursor: pointer;"
                        onclick="document.getElementById('library-import-modal').classList.add('active')">
                    📤 Import Library
                </button>
            </div>
        </div>

        @if ($userGames->isEmpty())

            <div class="library-empty">
                <div>🎮</div>
                <h3>Library kamu masih kosong</h3>
                <p>Tambahkan game dari Menu untuk mulai membangun koleksimu.</p>

                <a href="{{ route('games.index') }}" class="wii-edit-submit">
                    Browse Games
                </a>
            </div>

        @else

            <div class="library-grid">

                @foreach ($userGames as $userGame)

                    @php
                        $coverImage = trim((string) ($userGame->game->cover_image ?? ''));

                        // Deteksi jenis gambar
                        if ($coverImage === '') {
                            $coverUrl = null;
                        } elseif (str_starts_with($coverImage, 'data:image') || filter_var($coverImage, FILTER_VALIDATE_URL)) {
                            // Gambar Base64 atau dari RAWG API (URL eksternal)
                            $coverUrl = $coverImage;
                        } elseif (str_starts_with($coverImage, '/storage/')) {
                            // Path yang sudah mengandung /storage/
                            $coverUrl = asset(ltrim($coverImage, '/'));
                        } elseif (str_starts_with($coverImage, 'storage/')) {
                            // Path yang sudah mengandung storage/
                            $coverUrl = asset($coverImage);
                        } else {
                            // Gambar lokal dari Laravel Storage
                            $coverUrl = asset('storage/' . ltrim($coverImage, '/'));
                        }
                    @endphp

                    <article class="library-game-card">

                        <!-- Cover Game -->
                        <a href="{{ route('games.show', $userGame->game->id) }}"
                           class="library-game-cover">

                            @if ($coverUrl)

                                <img
                                    src="{{ $coverUrl }}"
                                    alt="Cover {{ $userGame->game->title }}"
                                    loading="lazy"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                >

                                <div class="library-no-cover" style="display: none;">
                                    🎮
                                </div>

                            @else

                                <div class="library-no-cover">
                                    🎮
                                </div>

                            @endif

                        </a>

                        <!-- Informasi Game -->
                        <div class="library-game-info">

                            <h3>
                                {{ $userGame->game->title }}
                            </h3>

                            <div class="library-game-meta">

                                <div>
                                    <span>Status</span>
                                    <strong class="library-status">
                                        {{ $userGame->status }}
                                    </strong>
                                </div>

                                <div>
                                    <span>Platform</span>
                                    <strong>
                                        {{ $userGame->platform->name ?? 'Belum dipilih' }}
                                    </strong>
                                </div>

                                <div>
                                    <span>Playtime</span>
                                    <strong>
                                        {{ $userGame->playtime }} Hours
                                    </strong>
                                </div>

                                <div>
                                    <span>Priority</span>
                                    <strong>
                                        {{ $userGame->priority ?? '-' }}
                                    </strong>
                                </div>

                            </div>

                            <!-- Tombol Aksi -->
                            <div class="library-card-actions">

                                <a href="{{ route('games.show', $userGame->game->id) }}"
                                   class="library-detail-button">
                                    View Details
                                </a>

                                <a href="{{ route('games.library.edit', $userGame->id) }}"
                                   class="library-detail-button">
                                    ✎ Edit Library
                                </a>

                                <form action="{{ route('games.library.remove', $userGame->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Apakah kamu yakin ingin menghapus game ini dari Personal Library?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="library-remove-button">
                                        ✕ Remove
                                    </button>

                                </form>

                            </div>

                        </div>

                    </article>

                @endforeach

            </div>

            <!-- Pagination -->
            <div class="library-pagination">
                {{ $userGames->links() }}
            </div>

        @endif

    </section>

    <footer class="library-footer">
        GAMEBACKLOG · MY PERSONAL COLLECTION
    </footer>

</div>

<!-- Modal Import Personal Library -->
<div class="wii-opening-overlay" id="library-import-modal"
     onclick="if(event.target === this) this.classList.remove('active')">
    <div class="wii-opening-panel" style="padding: 28px; max-width: 460px;">
        <div class="wii-opening-header">IMPORT PERSONAL LIBRARY</div>
        <h2 class="wii-opening-title" style="margin-bottom: 10px;">Upload Koleksi Game</h2>
        <p style="font-size: 13px; color: #a8d8ef; margin-bottom: 18px; line-height: 1.5;">
            Gunakan template dari <strong>Export Library</strong>.<br>
            Kolom: <code>Judul Game</code> (sesuai katalog), <code>Status</code> (Plan to Play / Backlog / Playing / Completed / Dropped), <code>Platform</code>, <code>Playtime (Jam)</code>, <code>Priority</code> (Low / Medium / High).
        </p>
        <form action="{{ route('games.library.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                   style="width: 100%; padding: 10px; margin-bottom: 20px; border-radius: 12px; border: 1px solid #58cfff; background: rgba(15,35,50,0.9); color: #fff;">
            <div class="wii-opening-actions">
                <button type="button" class="wii-action-button"
                        onclick="document.getElementById('library-import-modal').classList.remove('active')">
                    Cancel
                </button>
                <button type="submit" class="wii-action-button primary">
                    📤 Upload & Import
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Theme Toggle & User Charts -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const themeToggle = document.getElementById('wii-theme-toggle');

    const savedTheme = localStorage.getItem('wii-theme') || 'classic';

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

    applyTheme(savedTheme);

    themeToggle.addEventListener('click', function () {

        const currentTheme = document.body.dataset.theme;

        applyTheme(
            currentTheme === 'classic' ? 'midnight' : 'classic'
        );

    });

    const playtimeCtx = document.getElementById('userTopPlaytimeChart');
    if (playtimeCtx && typeof Chart !== 'undefined') {
        new Chart(playtimeCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(($topPlayedGames ?? collect())->map(fn($item) => optional($item->game)->title ?? 'Game')) !!},
                datasets: [{
                    label: 'Playtime (Hours)',
                    data: {!! json_encode(($topPlayedGames ?? collect())->pluck('playtime')) !!},
                    backgroundColor: '#29b6f6',
                    borderRadius: 6,
                    barThickness: 16
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            color: '#cfe9f7',
                            font: { size: 11 },
                            callback: (val) => val + 'h'
                        },
                        grid: { color: 'rgba(255,255,255,0.06)' }
                    },
                    y: {
                        ticks: { color: '#e2f4ff', font: { size: 11, weight: 'bold' } },
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    const statusCtx = document.getElementById('userStatusChart');
    if (statusCtx && typeof Chart !== 'undefined') {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(($statusCounts ?? collect())->keys()) !!},
                datasets: [{
                    data: {!! json_encode(($statusCounts ?? collect())->values()) !!},
                    backgroundColor: ['#58cfff', '#78909c', '#ffd166', '#2ecc71', '#ff7070'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#e2f4ff',
                            boxWidth: 12,
                            font: { size: 11 }
                        }
                    }
                }
            }
        });
    }

});
</script>

</body>
</html>
