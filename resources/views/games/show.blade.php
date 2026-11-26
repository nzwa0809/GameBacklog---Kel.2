
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $game->title }} - GameBacklog</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="wii-body" data-theme="classic">

<div class="wii-form-page">

    {{-- Header --}}
    <div class="wii-form-top">

        <a href="{{ route('games.index') }}" class="wii-back-button">
            ← Menu
        </a>

        <button type="button"
                class="theme-toggle"
                id="wii-theme-toggle">
            🌙
        </button>

    </div>


    <div class="wii-form-container">

        {{-- Heading --}}
        <div class="wii-form-heading">

            <div class="wii-form-icon">🎮</div>

            <h1>Game Details</h1>

            <p>Informasi lengkap game dalam koleksi GameBacklog.</p>

        </div>


        <div class="wii-detail-content">

            {{-- Notifikasi --}}
            @if (session('success'))

                <div class="wii-alert wii-alert-success">
                    {{ session('success') }}
                </div>

            @endif


            @if (session('error'))

                <div class="wii-alert wii-alert-error">
                    {{ session('error') }}
                </div>

            @endif


            {{-- Menentukan URL Cover --}}
            @php

                $coverUrl = null;

                if ($game->cover_image) {

                    if (\Illuminate\Support\Str::startsWith(
                        $game->cover_image,
                        ['http://', 'https://', 'data:image']
                    )) {

                        // Cover dari RAWG API atau Base64
                        $coverUrl = $game->cover_image;

                    } else {

                        // Cover hasil upload manual
                        $coverUrl = asset(
                            'storage/' . $game->cover_image
                        );

                    }

                }

            @endphp


            {{-- Layout Utama Game Details --}}
            <div class="wii-detail-main">

                {{-- Cover Game di Tengah --}}
                @if ($coverUrl)

                    <div class="wii-detail-cover">

                        <img
                            src="{{ $coverUrl }}"
                            alt="Cover {{ $game->title }}"
                            onerror="this.style.display='none'"
                        >

                    </div>

                @endif


                {{-- Informasi Game --}}
                <div class="wii-detail-side">

                    <h2 class="wii-detail-title">
                        {{ $game->title }}
                    </h2>


                    <div class="wii-detail-info">

                        {{-- Genre --}}
                        <div class="wii-detail-item">

                            <span>Genre</span>

                            <strong>
                                {{ $game->genre->name ?? '-' }}
                            </strong>

                        </div>


                        {{-- Developer --}}
                        <div class="wii-detail-item">

                            <span>Developer</span>

                            <strong>
                                {{ $game->developer ?: '-' }}
                            </strong>

                        </div>


                        {{-- Publisher --}}
                        <div class="wii-detail-item">

                            <span>Publisher</span>

                            <strong>
                                {{ $game->publisher ?: '-' }}
                            </strong>

                        </div>


                        {{-- Release Date --}}
                        <div class="wii-detail-item">

                            <span>Release Date</span>

                            <strong>
                                {{ $game->release_date?->format('d F Y') ?? '-' }}
                            </strong>

                        </div>


                        {{-- Platforms --}}
                        <div class="wii-detail-item">

                            <span>Platforms</span>

                            <strong>
                                {{ $game->platforms->pluck('name')->implode(', ') ?: '-' }}
                            </strong>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Deskripsi Game --}}
            <div class="wii-detail-description">

                <h3>About This Game</h3>

                <div class="game-description-content">

                    @if ($game->description)

                        {{-- Menampilkan HTML hasil WYSIWYG Editor --}}
                        {!! $game->description !!}

                    @else

                        <p>Belum ada deskripsi untuk game ini.</p>

                    @endif

                </div>

            </div>


            {{-- Tombol Aksi --}}
            <div class="wii-form-actions">

                <a href="{{ route('games.index') }}"
                   class="wii-cancel-button">

                    Back to Menu

                </a>


                {{-- Tombol User --}}
                @if (auth()->user()->role === 'user')

                    <form action="{{ route('games.library.add', $game->id) }}"
                          method="POST"
                          class="wii-library-form">

                        @csrf

                        <button type="submit"
                                class="wii-library-submit">

                            ＋ Add to My Library

                        </button>

                    </form>

                @endif


                {{-- Tombol Admin --}}
                @if (auth()->user()->role === 'admin')

                    <a href="{{ route('games.edit', $game->id) }}"
                       class="wii-edit-submit">

                        ✎ Edit Game

                    </a>

                @endif

            </div>

        </div>

    </div>


    {{-- Footer --}}
    <div class="wii-form-footer">

        GAMEBACKLOG · WII COLLECTION SYSTEM

    </div>

</div>


{{-- Theme Switcher --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const themeToggle =
        document.getElementById('wii-theme-toggle');

    if (!themeToggle) return;

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

});

</script>

</body>
</html>
