<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - GameBacklog</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="wii-body dashboard-page" data-theme="classic">

    <main class="dashboard-container">

        <!-- Top Bar -->
        <header class="dashboard-top">

            <span class="wii-brand">GAMEBACKLOG</span>

            <div class="dashboard-top-actions">

                <span class="dashboard-role">
                    {{ Auth::user()->role }}
                </span>

                <button type="button"
                        class="theme-toggle"
                        id="wii-theme-toggle"
                        title="Aktifkan Wii Midnight">
                    🌙
                </button>

            </div>

        </header>


        <!-- Welcome Card -->
        <section class="dashboard-card">

            <div class="dashboard-welcome">

                <div class="dashboard-avatar">
                    🎮
                </div>

                <p class="dashboard-subtitle">
                    GAMEBACKLOG · WII COLLECTION SYSTEM
                </p>

                <h1>Welcome Back!</h1>

                <h2>
                    Hello, {{ Auth::user()->name }}!
                </h2>

                <p class="dashboard-description">
                    Your gaming journey continues here.
                    Explore your collection and enjoy your games.
                </p>

            </div>


            <!-- Main Navigation -->
            <div class="dashboard-actions">

                <a href="{{ route('games.index') }}"
                   class="dashboard-menu-card">

                    <div class="dashboard-menu-icon">
                        🎮
                    </div>

                    <div>
                        <h3>My Collection</h3>
                        <p>Explore your game collection</p>
                    </div>

                    <span class="dashboard-arrow">›</span>

                </a>


                <a href="{{ route('account') }}"
                   class="dashboard-menu-card account-card">

                    <div class="dashboard-menu-icon">
                        👤
                    </div>

                    <div>
                        <h3>My Account</h3>
                        <p>View your account information</p>
                    </div>

                    <span class="dashboard-arrow">›</span>

                </a>


                <a href="{{ route('recommendations') }}"
                   class="dashboard-menu-card"
                   style="grid-column: 1 / -1;">

                    <div class="dashboard-menu-icon">
                        ✨
                    </div>

                    <div>
                        <h3>Recommended Games</h3>
                        <p>Personalized picks based on your play history</p>
                    </div>

                    <span class="dashboard-arrow">›</span>

                </a>

            </div>

            <!-- Most Played / Top Players Leaderboard -->
            <div class="dashboard-account" id="most-played-section" style="margin-top: 24px;">

                <div class="dashboard-section-heading">
                    <h3>Most Played · Top Players</h3>
                    <span>LEADERBOARD</span>
                </div>

                <div class="dashboard-info-item" style="display: block; padding: 20px;">
                    <p style="margin: 0 0 14px 0; font-size: 13px; color: #94a3b8;">
                        Peringkat pemain dengan total jam bermain (<strong style="color: #38bdf8;">Playtime</strong>) dan penyelesaian game (<strong style="color: #38bdf8;">Completed</strong>) tertinggi.
                    </p>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                            <thead>
                                <tr style="border-bottom: 1px solid rgba(88, 207, 255, 0.2); color: #94a3b8; font-size: 11px; letter-spacing: 0.05em;">
                                    <th style="padding: 10px 8px; width: 70px;">RANK</th>
                                    <th style="padding: 10px 8px;">PLAYER</th>
                                    <th style="padding: 10px 8px; text-align: center;">COMPLETED</th>
                                    <th style="padding: 10px 8px; text-align: center;">KOLEKSI</th>
                                    <th style="padding: 10px 8px; text-align: right;">TOTAL PLAYTIME</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topPlayers ?? [] as $index => $player)
                                    @php
                                        $medal = match($index) {
                                            0 => '🥇 #1',
                                            1 => '🥈 #2',
                                            2 => '🥉 #3',
                                            default => '#' . ($index + 1),
                                        };
                                        $hours = (int) ($player->user_games_sum_playtime ?? 0);
                                    @endphp
                                    <tr style="border-bottom: 1px solid rgba(88, 207, 255, 0.1); {{ $player->id === Auth::id() ? 'background: rgba(56, 189, 248, 0.08);' : '' }}">
                                        <td style="padding: 12px 8px; font-weight: 700; color: #38bdf8;">
                                            {{ $medal }}
                                        </td>
                                        <td style="padding: 12px 8px; font-weight: 600;">
                                            {{ $player->name }}
                                            @if ($player->id === Auth::id())
                                                <span style="margin-left: 6px; padding: 2px 8px; border-radius: 999px; font-size: 10px; background: rgba(56, 189, 248, 0.2); color: #38bdf8;">You</span>
                                            @endif
                                        </td>
                                        <td style="padding: 12px 8px; text-align: center; font-weight: 600; color: #4ade80;">
                                            {{ $player->completed_games }} Game
                                        </td>
                                        <td style="padding: 12px 8px; text-align: center; color: #94a3b8;">
                                            {{ $player->total_games }} Game
                                        </td>
                                        <td style="padding: 12px 8px; text-align: right; font-weight: 700; color: #38bdf8;">
                                            {{ number_format($hours) }} Jam
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" style="padding: 20px 8px; text-align: center; color: #94a3b8;">
                                            Belum ada data aktivitas bermain dari user.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>


            <!-- Admin Analytics (Admin Only) -->
            @if (Auth::user()->role === 'admin')
                <div class="dashboard-account" id="admin-analytics-section" style="margin-top: 24px;">

                    <div class="dashboard-section-heading">
                        <h3>Admin Analytics</h3>
                        <span>CATALOG & POPULARITY</span>
                    </div>

                    <!-- Card 1: Distribusi Game per Genre -->
                    <div class="dashboard-info-item" style="display: block; padding: 20px; margin-bottom: 18px;">
                        <h4 style="margin: 0 0 16px 0; font-size: 16px; color: #38bdf8; display: flex; align-items: center; gap: 8px;">
                            <span>📊</span> Distribusi Game per Genre
                        </h4>
                        <div style="position: relative; height: 240px; width: 100%;">
                            <canvas id="adminGenreChart"></canvas>
                        </div>
                    </div>

                    <!-- Card 2: Top 5 Game Terpopuler di Library -->
                    <div class="dashboard-info-item" style="display: block; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                            <h4 style="margin: 0; font-size: 16px; color: #38bdf8; display: flex; align-items: center; gap: 8px;">
                                <span>🔥</span> Top 5 Game Terpopuler di Library
                            </h4>

                            <form action="{{ route('dashboard') }}#admin-analytics-section" method="GET" style="margin: 0;">
                                <select name="period"
                                        onchange="this.form.submit()"
                                        style="padding: 8px 16px; border-radius: 999px; border: 1px solid #38bdf8; background: rgba(15, 35, 50, 0.95); color: #e2f4ff; font-size: 13px; font-weight: 600; cursor: pointer; outline: none;">
                                    <option value="day" {{ ($period ?? 'day') === 'day' ? 'selected' : '' }}>Per Day (Hari Ini)</option>
                                    <option value="week" {{ ($period ?? '') === 'week' ? 'selected' : '' }}>Per Week (Minggu Ini)</option>
                                    <option value="month" {{ ($period ?? '') === 'month' ? 'selected' : '' }}>Per Month (Bulan Ini)</option>
                                    <option value="all" {{ ($period ?? '') === 'all' ? 'selected' : '' }}>All Time (Semua)</option>
                                </select>
                            </form>
                        </div>

                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                                <thead>
                                    <tr style="border-bottom: 1px solid rgba(88, 207, 255, 0.2); color: #94a3b8; font-size: 11px; letter-spacing: 0.05em;">
                                        <th style="padding: 10px 8px; width: 70px;">RANK</th>
                                        <th style="padding: 10px 8px;">JUDUL GAME</th>
                                        <th style="padding: 10px 8px;">GENRE</th>
                                        <th style="padding: 10px 8px; text-align: right;">DITAMBAHKAN</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($topPopularGames ?? [] as $index => $pop)
                                        <tr style="border-bottom: 1px solid rgba(88, 207, 255, 0.1);">
                                            <td style="padding: 12px 8px; font-weight: 700; color: #38bdf8;">
                                                #{{ $index + 1 }}
                                            </td>
                                            <td style="padding: 12px 8px; font-weight: 600;">
                                                {{ optional($pop->game)->title ?? 'Game' }}
                                            </td>
                                            <td style="padding: 12px 8px; color: #94a3b8;">
                                                {{ optional(optional($pop->game)->genre)->name ?? '-' }}
                                            </td>
                                            <td style="padding: 12px 8px; text-align: right; font-weight: 600;">
                                                {{ $pop->total_added }}x
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" style="padding: 24px 8px; text-align: center; color: #94a3b8;">
                                                Belum ada game yang ditambahkan ke library pada periode {{ $periodText ?? 'hari ini' }}.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            <!-- Card 3: User Login & Online Status Monitor -->
                    <div class="dashboard-info-item" style="display: block; padding: 20px; margin-top: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px;">
                            <h4 style="margin: 0; font-size: 16px; color: #38bdf8; display: flex; align-items: center; gap: 8px;">
                                <span>🟢</span> Daftar User & Status Login
                            </h4>
                            <span style="padding: 4px 12px; border-radius: 999px; font-size: 12px; font-weight: 700; background: rgba(74, 222, 128, 0.15); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.4);">
                                {{ $onlineCount ?? 0 }} Sedang Online / {{ ($monitoredUsers ?? collect())->count() }} Total Akun
                            </span>
                        </div>

                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                                <thead>
                                    <tr style="border-bottom: 1px solid rgba(88, 207, 255, 0.2); color: #94a3b8; font-size: 11px; letter-spacing: 0.05em;">
                                        <th style="padding: 10px 8px;">NAMA USER</th>
                                        <th style="padding: 10px 8px;">ROLE</th>
                                        <th style="padding: 10px 8px;">STATUS LOGIN</th>
                                        <th style="padding: 10px 8px;">AKTIVITAS TERAKHIR</th>
                                        <th style="padding: 10px 8px; text-align: right;">KOLEKSI / PLAYTIME</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($monitoredUsers ?? [] as $mu)
                                        <tr style="border-bottom: 1px solid rgba(88, 207, 255, 0.1);">
                                            <td style="padding: 11px 8px;">
                                                <div style="font-weight: 600;">{{ $mu->name }}</div>
                                                <div style="font-size: 11px; color: #94a3b8;">{{ $mu->email }}</div>
                                            </td>
                                            <td style="padding: 11px 8px;">
                                                <span style="padding: 2px 9px; border-radius: 999px; font-size: 11px; font-weight: 600; text-transform: uppercase; {{ $mu->role === 'admin' ? 'background: rgba(250, 204, 21, 0.15); color: #facc15;' : 'background: rgba(56, 189, 248, 0.15); color: #38bdf8;' }}">
                                                    {{ $mu->role }}
                                                </span>
                                            </td>
                                            <td style="padding: 11px 8px;">
                                                @if ($mu->is_online)
                                                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; background: rgba(74, 222, 128, 0.18); color: #4ade80;">
                                                        ● Online
                                                    </span>
                                                @else
                                                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 3px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; background: rgba(148, 163, 184, 0.15); color: #94a3b8;">
                                                        ○ Offline
                                                    </span>
                                                @endif
                                            </td>
                                            <td style="padding: 11px 8px; color: #cbd5e1; font-size: 12px;">
                                                {{ $mu->last_seen_text }}
                                                @if ($mu->ip_address !== '-')
                                                    <span style="display: block; font-size: 10px; color: #64748b;">IP: {{ $mu->ip_address }}</span>
                                                @endif
                                            </td>
                                            <td style="padding: 11px 8px; text-align: right; font-weight: 600; color: #38bdf8;">
                                                {{ $mu->user_games_count }} Game · {{ number_format((int) ($mu->user_games_sum_playtime ?? 0)) }} Jam
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" style="padding: 20px 8px; text-align: center; color: #94a3b8;">
                                                Belum ada data pengguna terdaftar.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Card 4: Tracking Kegiatan User (Recent Activity Logs) -->
                    <div class="dashboard-info-item" style="display: block; padding: 20px; margin-top: 18px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 14px;">
                            <h4 style="margin: 0; font-size: 16px; color: #38bdf8; display: flex; align-items: center; gap: 8px;">
                                <span>🕵️</span> Tracking Kegiatan User Terbaru
                            </h4>
                            <span style="font-size: 11px; color: #94a3b8;">
                                Menampilkan 15 aktivitas terbaru
                            </span>
                        </div>

                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                                <thead>
                                    <tr style="border-bottom: 1px solid rgba(88, 207, 255, 0.2); color: #94a3b8; font-size: 11px; letter-spacing: 0.05em;">
                                        <th style="padding: 10px 8px;">USER</th>
                                        <th style="padding: 10px 8px; width: 130px;">AKSI</th>
                                        <th style="padding: 10px 8px;">DETAIL KEGIATAN</th>
                                        <th style="padding: 10px 8px; text-align: right; width: 140px;">WAKTU</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($userActivityLogs ?? [] as $log)
                                        <tr style="border-bottom: 1px solid rgba(88, 207, 255, 0.1);">
                                            <td style="padding: 11px 8px; font-weight: 600;">
                                                {{ $log['user_name'] }}
                                            </td>
                                            <td style="padding: 11px 8px;">
                                                <span style="padding: 3px 9px; border-radius: 6px; font-size: 11px; font-weight: 600; background: rgba(56, 189, 248, 0.15); color: #38bdf8;">
                                                    {{ $log['action'] }}
                                                </span>
                                            </td>
                                            <td style="padding: 11px 8px; color: #e2e8f0;">
                                                {{ $log['activity'] }}
                                            </td>
                                            <td style="padding: 11px 8px; text-align: right; color: #94a3b8; font-size: 12px;">
                                                {{ $log['time_ago'] }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" style="padding: 20px 8px; text-align: center; color: #94a3b8;">
                                                Belum ada riwayat aktivitas user yang tercatat.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
            @endif


            <!-- Bottom Actions -->
            <div class="dashboard-bottom-actions">

                <a href="{{ route('games.index') }}"
                   class="dashboard-enter-button">

                    Open Menu

                    <span>→</span>

                </a>


                <form action="/logout" method="POST">
                    @csrf

                    <button type="submit"
                            class="dashboard-logout-button"
                            onclick="return confirm('Apakah kamu yakin ingin logout?')">

                        <span>↪</span>
                        Logout

                    </button>

                </form>

            </div>


            <!-- Footer -->
            <footer class="dashboard-footer">
                GAMEBACKLOG · WII COLLECTION SYSTEM
            </footer>

        </section>


        <div class="dashboard-bottom-text">
            YOUR GAME COLLECTION STARTS HERE
        </div>

    </main>


    <!-- Theme Switcher & Admin Genre Chart -->
    <script>

        document.addEventListener('DOMContentLoaded', function () {

            // Toggle My Account Information
            const accountCard = document.getElementById('toggle-account-card');
            const accountPanel = document.getElementById('account-info-panel');
            const accountArrow = document.getElementById('account-card-arrow');

            if (accountCard && accountPanel) {
                const toggleAccountPanel = function () {
                    const isHidden = accountPanel.style.display === 'none';
                    accountPanel.style.display = isHidden ? 'block' : 'none';
                    accountCard.setAttribute('aria-expanded', String(isHidden));
                    if (accountArrow) {
                        accountArrow.style.transform = isHidden ? 'rotate(90deg)' : 'rotate(0deg)';
                    }
                    if (isHidden) {
                        accountPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                };

                accountCard.addEventListener('click', toggleAccountPanel);
                accountCard.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        toggleAccountPanel();
                    }
                });
            }

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

            @if (Auth::user()->role === 'admin')
                const genreCtx = document.getElementById('adminGenreChart');
                if (genreCtx && typeof Chart !== 'undefined') {
                    new Chart(genreCtx, {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode(($genreDistribution ?? collect())->pluck('name')) !!},
                            datasets: [{
                                label: 'Games',
                                data: {!! json_encode(($genreDistribution ?? collect())->pluck('games_count')) !!},
                                backgroundColor: '#299fd6',
                                borderRadius: 6,
                                barThickness: 28
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        color: '#94a3b8',
                                        font: { size: 11 }
                                    },
                                    grid: {
                                        color: 'rgba(148, 163, 184, 0.14)'
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: '#cbd5e1',
                                        font: { size: 11, weight: 'bold' }
                                    },
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            return ' ' + context.parsed.y + ' Games';
                                        }
                                    }
                                }
                            }
                        }
                    });
                }
            @endif

        });

    </script>

</body>
</html>
