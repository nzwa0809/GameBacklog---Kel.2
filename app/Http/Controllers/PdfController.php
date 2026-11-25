<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use FPDF;

class PdfController extends Controller
{
    public function exportLibrary(Request $request)
    {
        // Mengambil data Personal Library milik user yang sedang login
        $userGames = $request->user()
            ->userGames()
            ->with(['game', 'platform'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Membuat dokumen PDF berorientasi landscape
        $pdf = new FPDF('L', 'mm', 'A4');

        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 15);

        // Judul dokumen
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(0, 10, 'GAMEBACKLOG - PERSONAL LIBRARY', 0, 1, 'C');

        // Informasi pemilik dokumen
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 7, 'Nama: ' . $this->toPdfText($request->user()->name), 0, 1);
        $pdf->Cell(0, 7, 'Tanggal Export: ' . now()->format('d-m-Y H:i'), 0, 1);
        $pdf->Cell(0, 7, 'Total Games: ' . $userGames->count(), 0, 1);

        $pdf->Ln(5);

        // Header tabel
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(35, 105, 145);
        $pdf->SetTextColor(255, 255, 255);

        $pdf->Cell(10, 10, 'No', 1, 0, 'C', true);
        $pdf->Cell(82, 10, 'Game Title', 1, 0, 'C', true);
        $pdf->Cell(40, 10, 'Platform', 1, 0, 'C', true);
        $pdf->Cell(48, 10, 'Status', 1, 0, 'C', true);
        $pdf->Cell(35, 10, 'Playtime (Hours)', 1, 0, 'C', true);
        $pdf->Cell(35, 10, 'Priority', 1, 1, 'C', true);

        // Isi tabel
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0, 0, 0);

        if ($userGames->isEmpty()) {
            $pdf->Cell(250, 12, 'Personal Library masih kosong.', 1, 1, 'C');
        } else {
            foreach ($userGames as $index => $userGame) {
                $title = $this->toPdfText($userGame->game->title ?? '-');
                $platform = $this->toPdfText($userGame->platform->name ?? 'Belum dipilih');
                $status = $this->toPdfText($userGame->status ?? '-');
                $priority = $this->toPdfText($userGame->priority ?? '-');

                // Batasi teks agar tidak melewati lebar kolom
                $title = $this->shortenText($title, 48);
                $platform = $this->shortenText($platform, 22);

                $pdf->Cell(10, 9, $index + 1, 1, 0, 'C');
                $pdf->Cell(82, 9, $title, 1, 0);
                $pdf->Cell(40, 9, $platform, 1, 0);
                $pdf->Cell(48, 9, $status, 1, 0);
                $pdf->Cell(35, 9, (string) ($userGame->playtime ?? 0), 1, 0, 'C');
                $pdf->Cell(35, 9, $priority, 1, 1, 'C');
            }
        }

        // Menghasilkan isi PDF sebagai string
        $pdfContent = $pdf->Output('S');

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="personal-library.pdf"',
        ]);
    }

    public function exportCatalog()
    {
        // Mengambil seluruh katalog game beserta relasinya
        $games = \App\Models\Game::with([
            'genre',
            'platforms'
        ])
        ->orderBy('title', 'asc')
        ->get();

        // Membuat PDF landscape
        $pdf = new FPDF('L', 'mm', 'A4');

        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 15);

        // Judul dokumen
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(0, 10, 'GAMEBACKLOG - GAME CATALOG', 0, 1, 'C');

        // Informasi katalog
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 7, 'Tanggal Export: ' . now()->format('d-m-Y H:i'), 0, 1);
        $pdf->Cell(0, 7, 'Total Games: ' . $games->count(), 0, 1);

        $pdf->Ln(5);

        // Fungsi untuk membuat header tabel
        $drawHeader = function () use ($pdf) {
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetFillColor(35, 105, 145);
            $pdf->SetTextColor(255, 255, 255);

            $pdf->Cell(8, 10, 'No', 1, 0, 'C', true);
            $pdf->Cell(58, 10, 'Game Title', 1, 0, 'C', true);
            $pdf->Cell(25, 10, 'Genre', 1, 0, 'C', true);
            $pdf->Cell(38, 10, 'Developer', 1, 0, 'C', true);
            $pdf->Cell(38, 10, 'Publisher', 1, 0, 'C', true);
            $pdf->Cell(28, 10, 'Release Date', 1, 0, 'C', true);
            $pdf->Cell(82, 10, 'Platforms', 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(0, 0, 0);
        };

        // Tampilkan header tabel
        $drawHeader();

        if ($games->isEmpty()) {
            $pdf->Cell(277, 12, 'Belum ada game dalam katalog.', 1, 1, 'C');
        } else {
            foreach ($games as $index => $game) {

                // Pindah halaman jika ruang tidak mencukupi
                if ($pdf->GetY() > 185) {
                    $pdf->AddPage();
                    $drawHeader();
                }

                // Mengambil nama seluruh platform game
                $platformNames = $game->platforms
                    ->pluck('name')
                    ->implode(', ');

                // Mengubah data menjadi teks kompatibel dengan FPDF
                $title = $this->shortenText(
                    $this->toPdfText($game->title ?? '-'),
                    38
                );

                $genre = $this->shortenText(
                    $this->toPdfText($game->genre->name ?? '-'),
                    15
                );

                $developer = $this->shortenText(
                    $this->toPdfText($game->developer ?? '-'),
                    23
                );

                $publisher = $this->shortenText(
                    $this->toPdfText($game->publisher ?? '-'),
                    23
                );

                $releaseDate = $game->release_date
                    ? $game->release_date->format('d-m-Y')
                    : '-';

                $platformNames = $this->shortenText(
                    $this->toPdfText($platformNames ?: '-'),
                    50
                );

                // Menampilkan data ke dalam tabel
                $pdf->Cell(8, 9, $index + 1, 1, 0, 'C');
                $pdf->Cell(58, 9, $title, 1, 0);
                $pdf->Cell(25, 9, $genre, 1, 0);
                $pdf->Cell(38, 9, $developer, 1, 0);
                $pdf->Cell(38, 9, $publisher, 1, 0);
                $pdf->Cell(28, 9, $releaseDate, 1, 0, 'C');
                $pdf->Cell(82, 9, $platformNames, 1, 1);
            }
        }

        // Menghasilkan dokumen PDF
        $pdfContent = $pdf->Output('S');

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="game-catalog.pdf"',
        ]);
    }

    /**
     * Mengubah teks UTF-8 agar kompatibel dengan font bawaan FPDF.
     */
    private function toPdfText($text): string
    {
        return mb_convert_encoding((string) $text, 'ISO-8859-1', 'UTF-8');
    }

    /**
     * Memotong teks panjang agar sesuai dengan kolom tabel.
     */
    private function shortenText(string $text, int $limit): string
    {
        if (strlen($text) > $limit) {
            return substr($text, 0, $limit - 3) . '...';
        }

        return $text;
    }
}
