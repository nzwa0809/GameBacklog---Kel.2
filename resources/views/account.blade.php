<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - GameBacklog</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

        <!-- Account Card -->
        <section class="dashboard-card">

            <div class="dashboard-welcome">
                <div class="dashboard-avatar">
                    👤
                </div>

                <p class="dashboard-subtitle">
                    GAMEBACKLOG · ACCOUNT PROFILE
                </p>

                <h1>My Account</h1>

                <p class="dashboard-description">
                    Informasi lengkap akun pengguna yang terdaftar pada sistem GameBacklog.
                </p>
            </div>

            <!-- Account Information -->
            <div class="dashboard-account">

                <div class="dashboard-section-heading">
                    <h3>Account Information</h3>
                    <span>PROFILE</span>
                </div>

                <div class="dashboard-info-grid">

                    <div class="dashboard-info-item">
                        <span>Full Name</span>
                        <strong>{{ Auth::user()->name }}</strong>
                    </div>

                    <div class="dashboard-info-item">
                        <span>Email Address</span>
                        <strong>{{ Auth::user()->email }}</strong>
                    </div>

                    <div class="dashboard-info-item">
                        <span>Account Role</span>
                        <strong>{{ Auth::user()->role }}</strong>
                    </div>

                    <div class="dashboard-info-item">
                        <span>Member Since</span>
                        <strong>{{ Auth::user()->created_at ? Auth::user()->created_at->format('d M Y') : '-' }}</strong>
                    </div>

                </div>

            </div>

            <!-- Bottom Actions -->
            <div class="dashboard-bottom-actions">

                <a href="{{ route('dashboard') }}"
                   class="dashboard-enter-button">
                    <span>←</span>
                    Back to Dashboard
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

            <footer class="dashboard-footer">
                GAMEBACKLOG · WII COLLECTION SYSTEM
            </footer>

        </section>

    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const themeToggle = document.getElementById('wii-theme-toggle');
            const savedTheme = localStorage.getItem('wii-theme') || 'classic';

            function applyTheme(theme) {
                document.body.dataset.theme = theme;
                localStorage.setItem('wii-theme', theme);
                themeToggle.textContent = theme === 'midnight' ? '☀️' : '🌙';
            }

            applyTheme(savedTheme);

            themeToggle.addEventListener('click', function () {
                const next = document.body.dataset.theme === 'classic' ? 'midnight' : 'classic';
                applyTheme(next);
            });
        });
    </script>

</body>
</html>
