<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserGame;
use App\Models\Game;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Exports\GamesExport;
use App\Imports\GamesImport;
use App\Exports\UserLibraryExport;
use App\Imports\UserLibraryImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Genre;
use App\Models\Platform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class GameController extends Controller
{

    /**
     * Membersihkan HTML deskripsi game.
     * Hanya mempertahankan format yang diizinkan.
     */
    private function sanitizeDescription(?string $description): ?string
    {
        if ($description === null || trim($description) === '') {
            return null;
        }

        $allowedTags = [
            'p', 'br',
            'strong', 'b',
            'em', 'i',
            'u', 's',
            'h1', 'h2', 'h3',
            'ul', 'ol', 'li',
            'blockquote',
            'a',
        ];

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previousSetting = libxml_use_internal_errors(true);

        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div>' . $description . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($previousSetting);

        $container = $dom->getElementsByTagName('div')->item(0);

        if (!$container) {
            return null;
        }

        // Mengubah struktur daftar Quill menjadi HTML standar.
        $normalizeLists = function ($node) use (&$normalizeLists, $dom) {
            foreach (iterator_to_array($node->childNodes) as $child) {
                if (!$child instanceof \DOMElement) {
                    continue;
                }

                $normalizeLists($child);

                if (strtolower($child->tagName) !== 'ol') {
                    continue;
                }

                $groups = [];
                $currentType = null;

                foreach (iterator_to_array($child->childNodes) as $item) {
                    if (!$item instanceof \DOMElement || strtolower($item->tagName) !== 'li') {
                        continue;
                    }

                    $type = $item->getAttribute('data-list') === 'bullet' ? 'ul' : 'ol';

                    if ($currentType !== $type) {
                        $groups[] = [
                            'type' => $type,
                            'items' => [],
                        ];
                        $currentType = $type;
                    }

                    $groups[count($groups) - 1]['items'][] = $item;
                }

                if (empty($groups)) {
                    continue;
                }

                $fragment = $dom->createDocumentFragment();

                foreach ($groups as $group) {
                    $list = $dom->createElement($group['type']);

                    foreach ($group['items'] as $item) {
                        $item->removeAttribute('data-list');
                        $list->appendChild($item);
                    }

                    $fragment->appendChild($list);
                }

                $node->replaceChild($fragment, $child);
            }
        };

        $normalizeLists($container);

        // Membersihkan elemen dan atribut HTML.
        $cleanNode = function ($node) use (&$cleanNode, $allowedTags) {
            foreach (iterator_to_array($node->childNodes) as $child) {
                if (!$child instanceof \DOMElement) {
                    continue;
                }

                $tag = strtolower($child->tagName);

                if (!in_array($tag, $allowedTags, true)) {
                    $node->removeChild($child);
                    continue;
                }

                // Simpan hanya URL link yang aman.
                $safeHref = null;

                if ($tag === 'a') {
                    $href = trim($child->getAttribute('href'));

                    if (preg_match('/^(https?:\/\/|mailto:)/i', $href)) {
                        $safeHref = $href;
                    }
                }

                // Hapus semua atribut yang tidak diperlukan.
                while ($child->attributes->length > 0) {
                    $child->removeAttributeNode($child->attributes->item(0));
                }

                // Kembalikan href setelah URL diperiksa.
                if ($safeHref !== null) {
                    $child->setAttribute('href', $safeHref);
                }

                $cleanNode($child);
            }
        };

        $cleanNode($container);

        // Menghasilkan HTML yang sudah dibersihkan.
        $result = '';

        foreach ($container->childNodes as $child) {
            $result .= $dom->saveHTML($child);
        }

        return $result;
    }
    private function recordUserActivity(?int $userId, string $action, string $description): void
    {
        if (!$userId) {
            return;
        }

        try {
            if (Schema::hasTable('activity_logs')) {
                $data = [
                    'user_id'    => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('activity_logs', 'action')) {
                    $data['action'] = $action;
                }
                if (Schema::hasColumn('activity_logs', 'activity')) {
                    $data['activity'] = $description;
                }
                if (Schema::hasColumn('activity_logs', 'description')) {
                    $data['description'] = $description;
                }
                if (Schema::hasColumn('activity_logs', 'details')) {
                    $data['details'] = $description;
                }
                if (Schema::hasColumn('activity_logs', 'ip_address')) {
                    $data['ip_address'] = request()->ip();
                }

                DB::table('activity_logs')->insert($data);
            }
        } catch (\Throwable $e) {
            // Abaikan error log agar aksi utama tetap berjalan
        }
    }
    /**
- Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search     = $request->query('search');
        $genreId    = $request->query('genre_id');
        $platformId = $request->query('platform_id');
        $sort       = $request->query('sort', 'title_asc');

        $query = Game::query()
            ->with(['genre', 'platforms'])
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('developer', 'LIKE', "%{$search}%")
                        ->orWhere('publisher', 'LIKE', "%{$search}%");
                });
            })
            ->when($genreId, function ($q, $genreId) {
                $q->where('genre_id', $genreId);
            })
            ->when($platformId, function ($q, $platformId) {
                $q->whereHas('platforms', function ($sub) use ($platformId) {
                    $sub->where('platforms.id', $platformId);
                });
            })
            ->when($sort, function ($q, $sort) {
                match ($sort) {
                    'title_desc' => $q->orderBy('title', 'desc'),
                    'newest', 'newest_release' => $q->orderByDesc('release_date')->orderByDesc('id'),
                    'oldest'     => $q->orderBy('release_date')->orderBy('id'),
                    'latest', 'latest_id' => $q->orderByDesc('id'),
                    default      => $q->orderBy('title', 'asc'),
                };
            });

        // Endpoint ringan untuk live search dropdown saat mengetik
        if ($request->wantsJson() || $request->boolean('ajax')) {
            $results = (clone $query)
                ->limit(6)
                ->get()
                ->map(function ($g) {
                    $cover = trim((string) ($g->cover_image ?? ''));
                    if ($cover !== '' && !\Illuminate\Support\Str::startsWith($cover, ['http://', 'https://', 'data:image'])) {
                        $cover = asset('storage/' . ltrim($cover, '/'));
                    }
                    return [
                        'id'        => $g->id,
                        'title'     => $g->title,
                        'genre'     => optional($g->genre)->name ?? 'Game',
                        'year'      => $g->release_date ? \Carbon\Carbon::parse($g->release_date)->format('Y') : '',
                        'cover_url' => $cover,
                        'show_url'  => route('games.show', $g->id),
                    ];
                });

            return response()->json(['games' => $results]);
        }

        $games = $query
            ->paginate(12)
            ->withQueryString();

        // Otomatis lengkapi cover_image dari RAWG API untuk game di halaman ini yang belum punya cover
        $apiKey = env('RAWG_API_KEY') ?: config('services.rawg.key');
        if ($apiKey) {
            foreach ($games as $gameItem) {
                if (empty($gameItem->cover_image)) {
                    try {
                        $res = Http::timeout(4)->get('https://api.rawg.io/api/games', [
                            'key'       => $apiKey,
                            'search'    => $gameItem->title,
                            'page_size' => 1,
                        ]);

                        if ($res->successful() && ($img = $res->json('results.0.background_image'))) {
                            $gameItem->update(['cover_image' => $img]);
                        }
                    } catch (\Exception $e) {
                        // Abaikan jika API timeout
                    }
                }
            }
        }

        $genres = Genre::orderBy('name')->get();
        $platforms = Platform::orderBy('name')->get();

        return view('games.index', compact('games', 'genres', 'platforms'));
    }

    /**
- Show the form for creating a new resource.
     */
    public function create()
    {
        $genres = Genre::all();
        $platforms = Platform::all();

        return view('games.create', compact('genres', 'platforms'));
    }

    /**
- Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi data yang dikirim dari form
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'genre_id'     => 'required|exists:genres,id',
            'developer'    => 'required|string|max:255',
            'publisher'    => 'required|string|max:255',
            'release_date' => 'required|date',
            'platforms'    => 'required|array|min:1',
            'platforms.*'  => 'exists:platforms,id',
            'description'  => 'nullable|string',
            'cover_image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Mengambil ID platform yang dipilih
        $platformIds = $validated['platforms'];

        // Menghapus platforms dari data utama
        unset($validated['platforms']);
        // Membersihkan HTML pada deskripsi game
        $validated['description'] = $this->sanitizeDescription(
        $validated['description'] ?? null
        );

        // Upload cover image jika tersedia (simpan sebagai Base64 agar permanen di Wasmer)
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $validated['cover_image'] = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        // Menyimpan data game
        $game = Game::create($validated);

        // Menyimpan relasi platform ke tabel pivot
        $game->platforms()->sync($platformIds);

        // Kembali ke katalog game
        return redirect()->route('games.index')
            ->with('success', 'Game berhasil ditambahkan!');
    }

    /**
- Display the specified resource.
     */
    public function show(string $id)
    {
        $game = Game::with(['genre', 'platforms'])
            ->findOrFail($id);

        return view('games.show', compact('game'));
    }

    public function addToLibrary(Request $request, string $id)
    {
        // Mencari game berdasarkan ID
        $game = Game::findOrFail($id);

        // Mengambil user yang sedang login
        $user = $request->user();

        // Memastikan game belum ada di library user
        $alreadyExists = UserGame::where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->exists();

        if ($alreadyExists) {
            return back()->with('error', 'Game sudah ada di Personal Library kamu!');
        }

        // Menyimpan game ke Personal Library
        UserGame::create([
            'user_id'     => $user->id,
            'game_id'     => $game->id,
            'platform_id' => null,
            'status'      => 'Backlog',
            'playtime'    => 0,
            'priority'    => null,
            'added_at'    => now()->toDateString(),
        ]);
        $this->recordUserActivity($user->id, 'Add Library', 'Menambahkan "' . $game->title . '" ke Personal Library');

        return back()->with('success', 'Game berhasil ditambahkan ke Personal Library!');
    }

    public function myLibrary(Request $request)
    {
        $user = $request->user();

        // Mengambil koleksi game milik user yang sedang login
        $userGames = $user
            ->userGames()
            ->with([
                'game.genre',
                'game.platforms',
                'platform'
            ])
            ->latest()
            ->paginate(12);

        // Data untuk chart sisi user pada Personal Library
        $allUserGames = $user
            ->userGames()
            ->with('game')
            ->get();

        $statusOrder = ['Plan to Play', 'Backlog', 'Playing', 'Completed', 'Dropped'];
        $rawStatusCounts = $allUserGames->groupBy('status')->map->count();
        $statusCounts = collect($statusOrder)->mapWithKeys(function ($status) use ($rawStatusCounts) {
            return [$status => $rawStatusCounts->get($status, 0)];
        });

        $topPlayedGames = $allUserGames
            ->sortByDesc('playtime')
            ->take(5)
            ->values();

        $totalPlaytime = (int) $allUserGames->sum('playtime');

        // Mengirim data ke halaman Personal Library
        return view('games.library', compact(
            'userGames',
            'statusCounts',
            'topPlayedGames',
            'totalPlaytime'
        ));
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();
        $period = $request->query('period', 'day');

        // Data distribusi katalog per genre (Admin Analytics)
        $genreDistribution = Genre::withCount('games')
            ->orderByDesc('games_count')
            ->get();

        // Filter periode untuk Top 5 Game Terpopuler di Library
        $popularQuery = UserGame::query()
            ->select('game_id', DB::raw('COUNT(*) as total_added'))
            ->with('game.genre')
            ->groupBy('game_id')
            ->orderByDesc('total_added')
            ->limit(5);

        if ($period === 'day') {
            $popularQuery->whereDate('created_at', now()->toDateString());
        } elseif ($period === 'week') {
            $popularQuery->where('created_at', '>=', now()->startOfWeek());
        } elseif ($period === 'month') {
            $popularQuery->where('created_at', '>=', now()->startOfMonth());
        }

        $topPopularGames = $popularQuery->get();

        $periodLabels = [
            'day'   => 'hari ini',
            'week'  => 'minggu ini',
            'month' => 'bulan ini',
            'all'   => 'semua waktu',
        ];
        $periodText = $periodLabels[$period] ?? 'hari ini';

        // Top 5 Players (Most Played & Pencapaian Tertinggi)
        $topPlayers = User::query()
            ->where('role', 'user')
            ->withSum('userGames', 'playtime')
            ->withCount([
                'userGames as total_games',
                'userGames as completed_games' => function ($q) {
                    $q->where('status', 'Completed');
                },
            ])
            ->orderByDesc('user_games_sum_playtime')
            ->orderByDesc('completed_games')
            ->orderByDesc('total_games')
            ->limit(5)
            ->get();

        // Monitoring User Login & Tracking Kegiatan User (Khusus Admin)
        $monitoredUsers = collect();
        $onlineCount = 0;
        $userActivityLogs = collect();

        if ($user->role === 'admin') {
            $activeSessions = collect();
            try {
                if (Schema::hasTable('sessions')) {
                    $activeSessions = DB::table('sessions')
                        ->whereNotNull('user_id')
                        ->select('user_id', DB::raw('MAX(last_activity) as last_activity'), DB::raw('MAX(ip_address) as ip_address'))
                        ->groupBy('user_id')
                        ->get()
                        ->keyBy('user_id');
                }
            } catch (\Throwable $e) {
                $activeSessions = collect();
            }

            $onlineThreshold = now()->subMinutes(15)->timestamp;

            $monitoredUsers = User::query()
                ->withCount('userGames')
                ->withSum('userGames', 'playtime')
                ->orderByDesc('role')
                ->orderBy('name')
                ->get()
                ->map(function ($u) use ($activeSessions, $onlineThreshold) {
                    $session = $activeSessions->get($u->id);
                    $lastActivityTs = $session ? (int) $session->last_activity : null;
                    $isOnline = $lastActivityTs !== null && $lastActivityTs >= $onlineThreshold;

                    $u->is_online = $isOnline;
                    $u->last_seen_text = $lastActivityTs
                        ? \Carbon\Carbon::createFromTimestamp($lastActivityTs)->diffForHumans()
                        : 'Belum ada sesi aktif';
                    $u->ip_address = $session->ip_address ?? '-';

                    return $u;
                })
                ->sortByDesc('is_online')
                ->values();

            $onlineCount = $monitoredUsers->where('is_online', true)->count();

            // Ambil log dari tabel activity_logs
            try {
                if (Schema::hasTable('activity_logs')) {
                    $rawLogs = DB::table('activity_logs')
                        ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
                        ->select('activity_logs.*', 'users.name as user_name', 'users.role as user_role')
                        ->orderByDesc('activity_logs.created_at')
                        ->limit(15)
                        ->get();

                    foreach ($rawLogs as $row) {
                        $desc = $row->description ?? $row->activity ?? $row->details ?? ($row->action ?? 'Melakukan aktivitas');
                        $userActivityLogs->push([
                            'user_name'  => $row->user_name ?? 'User #' . $row->user_id,
                            'user_role'  => $row->user_role ?? 'user',
                            'action'     => $row->action ?? 'Activity',
                            'activity'   => $desc,
                            'ip_address' => $row->ip_address ?? '-',
                            'time_ago'   => $row->created_at ? \Carbon\Carbon::parse($row->created_at)->diffForHumans() : '-',
                            'timestamp'  => $row->created_at ? \Carbon\Carbon::parse($row->created_at)->timestamp : 0,
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan jika kolom berbeda
            }

            // Lengkapi dengan riwayat nyata dari tabel user_games agar histori lama tetap terbaca
            $recentLibraryActivities = UserGame::query()
                ->with(['user', 'game'])
                ->orderByDesc('updated_at')
                ->limit(10)
                ->get();

            foreach ($recentLibraryActivities as $ug) {
                $isUpdated = $ug->updated_at && $ug->created_at && $ug->updated_at->gt($ug->created_at->copy()->addSeconds(5));
                $gameTitle = optional($ug->game)->title ?? 'Game';
                $activityText = $isUpdated
                    ? 'Update "' . $gameTitle . '" → Status: ' . $ug->status . ' (' . ($ug->playtime ?? 0) . ' jam)'
                    : 'Menambahkan "' . $gameTitle . '" ke Personal Library (' . $ug->status . ')';

                $userActivityLogs->push([
                    'user_name'  => optional($ug->user)->name ?? 'User',
                    'user_role'  => optional($ug->user)->role ?? 'user',
                    'action'     => $isUpdated ? 'Update Library' : 'Add Library',
                    'activity'   => $activityText,
                    'ip_address' => '-',
                    'time_ago'   => $ug->updated_at ? $ug->updated_at->diffForHumans() : '-',
                    'timestamp'  => $ug->updated_at ? $ug->updated_at->timestamp : 0,
                ]);
            }

            $userActivityLogs = $userActivityLogs
                ->sortByDesc('timestamp')
                ->take(15)
                ->values();
        }

        return view('dashboard', compact(
            'user',
            'period',
            'periodText',
            'genreDistribution',
            'topPopularGames',
            'topPlayers',
            'monitoredUsers',
            'onlineCount',
            'userActivityLogs'
        ));
    }

    public function adminActivityData()
    {
        $byUser = User::query()
            ->withCount('userGames')
            ->withSum('userGames', 'playtime')
            ->orderByDesc('user_games_count')
            ->get()
            ->map(function ($u) {
                return [
                    'name'           => $u->name,
                    'role'           => $u->role,
                    'library_count'  => (int) $u->user_games_count,
                    'total_playtime' => (int) ($u->user_games_sum_playtime ?? 0),
                ];
            });

        $byStatus = UserGame::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'by_user'   => $byUser,
            'by_status' => $byStatus,
        ]);
    }

    public function editLibrary(string $id, Request $request)
    {
        // Mengambil game dari koleksi user yang sedang login
        $userGame = $request->user()
            ->userGames()
            ->with('game')
            ->findOrFail($id);

        // Mengambil daftar platform untuk pilihan
        $platforms = Platform::all();

        return view('games.library-edit', compact('userGame', 'platforms'));
    }

    public function updateLibrary(Request $request, string $id)
    {
        // Memastikan data yang diedit milik user yang sedang login
        $userGame = $request->user()
            ->userGames()
            ->findOrFail($id);

        // Memvalidasi input
        $validated = $request->validate([
            'status'      => 'required|in:Plan to Play,Backlog,Playing,Completed,Dropped',
            'platform_id' => 'nullable|exists:platforms,id',
            'playtime'    => 'required|integer|min:0',
            'priority'    => 'nullable|in:Low,Medium,High',
        ]);

        // Memperbarui data koleksi
        $userGame->update($validated);
        $gameTitle = optional($userGame->game)->title ?? 'Game';
        $this->recordUserActivity($request->user()->id, 'Update Library', 'Memperbarui "' . $gameTitle . '" (Status: ' . $validated['status'] . ', Playtime: ' . $validated['playtime'] . ' jam)');

        return redirect()->route('games.library')
            ->with('success', 'Personal Library berhasil diperbarui!');
    }

    public function removeFromLibrary(string $id)
    {
        // Mencari game di Personal Library milik user yang sedang login
        $userGame = auth()->user()
            ->userGames()
            ->findOrFail($id);
        $gameTitle = optional($userGame->game)->title ?? 'Game';
        $this->recordUserActivity(auth()->id(), 'Remove Library', 'Menghapus "' . $gameTitle . '" dari Personal Library');

        // Menghapus data dari Personal Library
        $userGame->delete();

        // Kembali ke halaman Personal Library
        return redirect()->route('games.library')
            ->with('success', 'Game berhasil dihapus dari Personal Library!');
    }

    /**
- Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $game = Game::with('platforms')->findOrFail($id);

        $genres = Genre::all();
        $platforms = Platform::all();

        return view('games.edit', compact('game', 'genres', 'platforms'));
    }

    /**
- Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Mencari game berdasarkan ID
        $game = Game::findOrFail($id);

        // Validasi data dari form edit
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'genre_id'     => 'required|exists:genres,id',
            'developer'    => 'required|string|max:255',
            'publisher'    => 'required|string|max:255',
            'release_date' => 'required|date',
            'platforms'    => 'required|array|min:1',
            'platforms.*'  => 'exists:platforms,id',
            'description'  => 'nullable|string',
            'cover_image'  => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Mengambil ID platform yang dipilih
        $platformIds = $validated['platforms'];

        // Menghapus platforms dari data utama
        unset($validated['platforms']);

        // Membersihkan HTML pada deskripsi game
        $validated['description'] = $this->sanitizeDescription(
            $validated['description'] ?? null
        );

        // Menyimpan lokasi cover lama
        $oldCover = $game->cover_image;

        // Upload cover baru jika pengguna memilih gambar
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $validated['cover_image'] = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        // Memperbarui data game
        $game->update($validated);

        // Memperbarui relasi platform
        $game->platforms()->sync($platformIds);

        // Menghapus cover lama jika berupa file lokal
        if ($request->hasFile('cover_image') && $oldCover && !\Illuminate\Support\Str::startsWith($oldCover, ['http://', 'https://', 'data:image'])) {
            Storage::disk('public')->delete($oldCover);
        }

        // Kembali ke katalog game
        return redirect()->route('games.index')
            ->with('success', 'Game berhasil diperbarui!');
    }

    /**
- Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // 1. Mengambil data game berdasarkan ID
        $game = Game::findOrFail($id);

        // 2. Menyimpan path cover sebelum data dihapus
        $coverImage = $game->cover_image;

        // 3. Menghapus relasi game dengan platform
        $game->platforms()->detach();

        // 4. Menghapus data game dari database
        $game->delete();

        // 5. Menghapus file cover jika berupa file lokal
        if ($coverImage && !\Illuminate\Support\Str::startsWith($coverImage, ['http://', 'https://', 'data:image'])) {
            Storage::disk('public')->delete($coverImage);
        }

        // 6. Kembali ke halaman daftar game
        return redirect()->route('games.index')
            ->with('success', 'Game berhasil dihapus!');
    }

    public function exportExcel()
    {
        return Excel::download(new GamesExport, 'katalog-game.xls', \Maatwebsite\Excel\Excel::XLS);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        Excel::import(new GamesImport, $request->file('file'));

        return redirect()->route('games.index')
            ->with('success', 'Data katalog game berhasil diimpor dari Excel!');
    }

    public function exportLibrary(Request $request)
    {
        return Excel::download(
            new UserLibraryExport($request->user()->id),
            'my-personal-library.xls',
            \Maatwebsite\Excel\Excel::XLS
        );
    }

    public function importLibrary(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ]);

        Excel::import(
            new UserLibraryImport($request->user()->id),
            $request->file('file')
        );
        $this->recordUserActivity($request->user()->id, 'Import Library', 'Mengimpor daftar koleksi Personal Library via Excel');

        return redirect()->route('games.library')
            ->with('success', 'Koleksi Personal Library berhasil diimpor dari Excel!');
    }
}
