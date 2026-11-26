
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Game - GameBacklog</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Quill WYSIWYG Editor -->
    <link
        href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css"
        rel="stylesheet"
    >
</head>

<body class="wii-body" data-theme="classic">

<div class="wii-form-page">

    <!-- TOP NAVIGATION -->
    <div class="wii-form-top">

        <a href="{{ route('games.index') }}" class="wii-back-button">
            ← Menu
        </a>

        <button type="button"
                class="theme-toggle"
                id="wii-theme-toggle"
                aria-label="Ganti tema"
                title="Ganti tema">
            🌙
        </button>

    </div>


    <!-- MAIN FORM CONTAINER -->
    <div class="wii-form-container">

        <!-- HEADING -->
        <div class="wii-form-heading">

            <div class="wii-form-icon">🎮</div>

            <h1>Edit Game</h1>

            <p>
                Perbarui informasi game dalam koleksi GameBacklog.
            </p>

        </div>


        <!-- VALIDATION ERRORS -->
        @if ($errors->any())
            <div class="wii-form-errors">

                <strong>Terjadi kesalahan!</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

            </div>
        @endif


        <!-- EDIT FORM -->
        <form action="{{ route('games.update', $game->id) }}"
              method="POST"
              enctype="multipart/form-data"
              class="wii-game-form">

            @csrf
            @method('PUT')


            <!-- GAME INFORMATION -->
            <div class="wii-form-section">

                <h3>Game Information</h3>


                <div class="wii-field">

                    <label for="title">Judul Game</label>

                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title', $game->title) }}"
                           placeholder="Masukkan judul game"
                           required>

                </div>


                <div class="wii-field">

                    <label for="genre_id">Genre</label>

                    <select id="genre_id" name="genre_id" required>

                        <option value="">Pilih Genre</option>

                        @foreach ($genres as $genre)

                            <option value="{{ $genre->id }}"
                                {{ old('genre_id', $game->genre_id) == $genre->id ? 'selected' : '' }}>

                                {{ $genre->name }}

                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="wii-field-row">

                    <div class="wii-field">

                        <label for="developer">Developer</label>

                        <input type="text"
                               id="developer"
                               name="developer"
                               value="{{ old('developer', $game->developer) }}"
                               placeholder="Developer"
                               required>

                    </div>


                    <div class="wii-field">

                        <label for="publisher">Publisher</label>

                        <input type="text"
                               id="publisher"
                               name="publisher"
                               value="{{ old('publisher', $game->publisher) }}"
                               placeholder="Publisher"
                               required>

                    </div>

                </div>


                <div class="wii-field">

                    <label for="release_date">Tanggal Rilis</label>

                    <input type="date"
                           id="release_date"
                           name="release_date"
                           value="{{ old('release_date', $game->release_date->format('Y-m-d')) }}"
                           required>

                </div>

            </div>



            <!-- PLATFORM SELECTION -->
            <div class="wii-form-section">

                <h3>Available Platforms</h3>

                <p class="wii-section-description">
                    Pilih platform tempat game ini tersedia.
                </p>


                <div class="wii-platform-grid">

                    @foreach ($platforms as $platform)

                        <label class="wii-platform-option">

                            <input type="checkbox"
                                   name="platforms[]"
                                   value="{{ $platform->id }}"
                                   id="platform_{{ $platform->id }}"
                                   {{ in_array($platform->id, old('platforms', $game->platforms->pluck('id')->toArray())) ? 'checked' : '' }}>

                            <span>{{ $platform->name }}</span>

                        </label>

                    @endforeach

                </div>

            </div>



            <!-- ADDITIONAL INFORMATION -->
            <div class="wii-form-section">

                <h3>Additional Information</h3>


                <div class="wii-field">

                    <label for="description">Deskripsi</label>

                    <!-- Quill Editor -->
                    <div id="description-editor" class="wii-quill-editor"></div>

                    <!-- Hidden textarea untuk mengirim data ke Laravel -->
                    <textarea
                        id="description"
                        name="description"
                        hidden
                    >{{ old('description', $game->description) }}</textarea>

                </div>


                <!-- CURRENT COVER -->
                <div class="wii-field">

                    <label>Cover Image</label>

                    @if ($game->cover_image)

                        @php
                            $currentCover = \Illuminate\Support\Str::startsWith(
                                $game->cover_image,
                                ['http://', 'https://', 'data:image']
                            )
                                ? $game->cover_image
                                : asset('storage/' . $game->cover_image);
                        @endphp

                        <div class="wii-current-cover">

                            <p>Cover saat ini:</p>

                            <img src="{{ $currentCover }}"
                                 alt="Cover {{ $game->title }}">

                        </div>

                    @endif


                    <input type="file"
                           id="cover_image"
                           name="cover_image"
                           accept="image/jpeg,image/png,image/webp">

                    <small>
                        Kosongkan jika tidak ingin mengganti cover.
                        Format JPG, JPEG, PNG, atau WEBP. Maksimal 2 MB.
                    </small>

                </div>

            </div>



            <!-- FORM ACTIONS -->
            <div class="wii-form-actions">

                <a href="{{ route('games.index') }}"
                   class="wii-cancel-button">

                    Cancel

                </a>


                <button type="submit" class="wii-edit-submit">

                    ✓ Update Game

                </button>

            </div>

        </form>

    </div>


    <!-- FOOTER -->
    <div class="wii-form-footer">

        GAMEBACKLOG · WII COLLECTION SYSTEM

    </div>

</div>


<!-- Quill JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>


<!-- WYSIWYG EDITOR -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const descriptionInput = document.getElementById('description');
    const form = document.querySelector('.wii-game-form');

    const quill = new Quill('#description-editor', {
        theme: 'snow',

        placeholder: 'Ceritakan sedikit tentang game ini...',

        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['link', 'clean']
            ]
        }
    });

    // Mengambil deskripsi lama dari input atau database
    const initialContent = @json(old('description', $game->description ?? ''));

    // Menampilkan deskripsi lama di dalam Quill Editor
    if (initialContent) {
        quill.clipboard.dangerouslyPasteHTML(initialContent);
    }

    // Memasukkan hasil editor ke textarea sebelum form dikirim
    form.addEventListener('submit', function () {
        descriptionInput.value = quill.root.innerHTML;
    });

});
</script>


<!-- THEME SWITCHER -->
<script>
document.addEventListener('DOMContentLoaded', function () {

    const themeToggle = document.getElementById('wii-theme-toggle');

    if (!themeToggle) return;

    const savedTheme = localStorage.getItem('wii-theme') || 'classic';


    function applyTheme(theme) {

        document.body.dataset.theme = theme;

        localStorage.setItem('wii-theme', theme);


        if (theme === 'midnight') {

            themeToggle.textContent = '☀️';

            themeToggle.title = 'Aktifkan Wii Classic';

            themeToggle.setAttribute(
                'aria-label',
                'Aktifkan Wii Classic'
            );

        } else {

            themeToggle.textContent = '🌙';

            themeToggle.title = 'Aktifkan Wii Midnight';

            themeToggle.setAttribute(
                'aria-label',
                'Aktifkan Wii Midnight'
            );

        }

    }


    applyTheme(savedTheme);


    themeToggle.addEventListener('click', function () {

        const currentTheme = document.body.dataset.theme;

        const newTheme = currentTheme === 'classic'
            ? 'midnight'
            : 'classic';

        applyTheme(newTheme);

    });

});
</script>

</body>
</html>
