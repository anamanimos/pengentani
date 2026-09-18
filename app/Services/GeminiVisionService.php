<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiVisionService
{
    /**
     * Get configured Gemini API Key.
     */
    public static function getApiKey(): ?string
    {
        $key = Setting::get('gemini_api_key');
        if (!empty($key)) {
            return $key;
        }

        return config('services.gemini.api_key', env('GEMINI_API_KEY'));
    }

    /**
     * Get configured Gemini Model.
     */
    public static function getModel(): string
    {
        return Setting::get('gemini_model', 'gemini-2.0-flash');
    }

    /**
     * Test connection to Gemini API.
     */
    public static function testConnection(?string $apiKey = null, ?string $model = null): array
    {
        $apiKey = $apiKey ?: self::getApiKey();
        $model = $model ?: self::getModel();

        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'API Key Gemini belum diisi. Silakan masukkan API Key Anda.',
            ];
        }

        try {
            // Test call using generateContent with short prompt
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $response = Http::timeout(15)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Ping. Balas dengan kata PONG saja jika aktif.']
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 10,
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'OK';
                return [
                    'success' => true,
                    'message' => "Koneksi ke Google Gemini API berhasil! Model: {$model}. Respon: " . trim($reply),
                ];
            }

            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? ('HTTP Status: ' . $response->status());
            return [
                'success' => false,
                'message' => "Gagal terhubung ke Gemini API: {$errorMessage}",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungi server Gemini: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Extract handwritten notebook records from an image file.
     *
     * @param string $filePath Absolute path or relative to storage
     * @param string|null $mimeType
     * @return array Extracted structured array
     * @throws \Exception
     */
    public static function extractNotebookPhoto(string $filePath, ?string $mimeType = null): array
    {
        $apiKey = self::getApiKey();
        $model = self::getModel();

        if (empty($apiKey)) {
            throw new \Exception('Gemini API Key belum dikonfigurasi di Pengaturan.');
        }

        if (!file_exists($filePath)) {
            throw new \Exception("File gambar tidak ditemukan pada path: {$filePath}");
        }

        if (!$mimeType) {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeType = match ($ext) {
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
                default => 'image/jpeg',
            };
        }

        $base64Data = base64_encode(file_get_contents($filePath));

        $prompt = self::getExtractionPrompt();

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Data,
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'temperature' => 0.1,
            ]
        ];

        $response = Http::timeout(60)->post($url, $payload);

        if (!$response->successful()) {
            $errorJson = $response->json();
            $msg = $errorJson['error']['message'] ?? ('HTTP ' . $response->status() . ': ' . $response->body());
            Log::error("Gemini Vision API Error: {$msg}");
            throw new \Exception("Gagal membaca foto dengan Gemini AI: {$msg}");
        }

        $resData = $response->json();
        $textOutput = $resData['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (empty($textOutput)) {
            throw new \Exception('Gemini AI tidak mengembalikan teks ekstraksi.');
        }

        // Clean json if wrapped in markdown block
        $cleaned = trim($textOutput);
        if (str_starts_with($cleaned, '```json')) {
            $cleaned = substr($cleaned, 7);
        } elseif (str_starts_with($cleaned, '```')) {
            $cleaned = substr($cleaned, 3);
        }
        if (str_ends_with($cleaned, '```')) {
            $cleaned = substr($cleaned, 0, -3);
        }
        $cleaned = trim($cleaned);

        $parsed = json_decode($cleaned, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsed)) {
            Log::error('Gemini JSON Parse Error: ' . json_last_error_msg() . ' Raw: ' . $cleaned);
            throw new \Exception('Format respon dari Gemini AI tidak valid sebagai JSON: ' . json_last_error_msg());
        }

        return $parsed;
    }

    /**
     * Get prompt tailored for handwritten field notebooks.
     */
    public static function getExtractionPrompt(): string
    {
        return <<<'PROMPT'
Anda adalah asisten AI spesialis pembacaan dan digitalisasi buku catatan kerja harian buruh tani (handwritten field notebook).
Analisis foto buku catatan ini dengan sangat teliti dan patuhi aturan domain berikut:

### 1. NAMA PEKERJA:
- Biasanya tertulis di bagian HEADER paling atas halaman buku (misal: "Rina", "Cak Kusnul", "Kusnul", "Cak Anas").
- Catat nama pekerja ini di root `detected_worker_name`.

### 2. TANGGAL DAN TAHUN:
- Baris pertama biasanya menuliskan tanggal, bulan, dan tahun lengkap (contoh: "21/8/2026", "31/7/2026") disertai nama hari (SENIN, SELASA, RABU, KAMIS, JUM'AT, SABTU, MINGGU).
- Baris-baris berikutnya seringkali hanya menuliskan singkatan tanggal seperti "22/8/..", "23/8/..", "24/8/..".
- Konversikan semua tanggal ke format standar YYYY-MM-DD (contoh: "2026-08-21").
- Jika tahun tidak tertulis jelas, gunakan tahun yang tertera di baris atas atau tahun 2026.

### 3. KEHADIRAN & STATUS:
- Angka "1" di kolom status kehadiran berarti masuk kerja.
- Jika tertulis "0" atau "Libur", buruh tidak bekerja pada tanggal tersebut. JANGAN masukkan ke dalam daftar `rows` (atau tandai status libur dan lewati).

### 4. SHIFT / WAKTU KERJA:
- Terdapat penanda waktu shift: "PG" / "PAGI" dan "SORE".
- PENTING: Jika dalam satu tanggal yang sama terdapat dua aktivitas berbeda (misal pagi "PG NGOBAT" dan sore "SORE NGOCOR"), BUAT MENJADI 2 BARIS TERPISAH di dalam array `rows` dengan tanggal yang sama.

### 5. TANDA PENGULANGAN (DITTO `"` / `""`):
- Simbol petik dua `"` atau `""` berarti MENGULANG kata atau pekerjaan dari baris tepat di atasnya.
  Contoh: Jika baris atas "NGOCOR AIR SPL" dan baris bawah tertulis `" MES SPL`, maka pekerjaan baris bawah adalah "NGOCOR MES SPL".
  Contoh: Jika baris atas "SPL" dan baris bawah `"`, maka kebun baris bawah adalah "SPL".

### 6. KODE LAHAN / KEBUN:
- Ekstrak kode lahan/kebun ke dalam field `raw_kebun_code`.
- Contoh yang sering muncul: "SPL" (Simpang Lima), "M.JUM" / "Ma'jum", "JJ6", "J.10", "Wonorejo", "Jajang", "Poncokusumo".

### 7. NAMA PEKERJAAN:
- Ekstrak nama pekerjaan mentah ke dalam `raw_job_name` dan teks deskripsi lengkap ke `description`.
- Istilah lokal:
  * "Ngocor" / "Ngocor air" / "Ngocor desel" -> Penyiraman
  * "Ngocor mes" -> Pemupukan
  * "Ngobat" / "Ngobat cabai" -> Spraying / Penyemprotan
  * "Petik" / "Petik cabai" -> Panen
  * "Pasang lanjaran" -> Pasang Lanjaran
  * "Babat" -> Penyiangan / Pembersihan Lahan

### 8. NOMINAL UPAH:
- Ditulis di kolom sebelah kanan (contoh: 30.000, 35.000, 40.000, 45.000, 50.000).
- Konversikan ke angka integer murni tanpa titik (contoh: 35000).

### 9. KONSUMSI:
- Seringkali tercatat di ringkasan paling bawah halaman (contoh: "Konsumsi 22 = 220.000", artinya Rp 10.000 per hari kerja).
- Nilai default konsumsi per hari kerja adalah 10000 (atau 0 jika tidak ada). Masukkan ke field `konsumsi`.

### 10. TINGKAT KEYAKINAN (CONFIDENCE):
- Set `confidence_rendah: true` jika tulisan tangan kabur, kotor, sobek, coretan tidak jelas, atau tanggal/angka diragukan.
- Berikan alasan di `review_reason` jika `confidence_rendah` bernilai true.

---

KEMBALIKAN HANYA JSON DENGAN STRUKTUR BERIKUT (TANPA TEKS LAIN):
{
  "detected_worker_name": "Nama Pekerja Terdeteksi",
  "period_summary": "Bulan/Periode Buku (misal: Agustus 2026)",
  "total_working_days": 22,
  "notes": "Catatan tambahan atau ringkasan di buku",
  "rows": [
    {
      "date": "2026-08-21",
      "raw_date_text": "21/8/2026 (JUM'AT)",
      "raw_kebun_code": "SPL",
      "raw_job_name": "Ngocor air",
      "description": "PG Ngocor air SPL",
      "shift": "PG",
      "wage": 35000,
      "konsumsi": 10000,
      "confidence_rendah": false,
      "review_reason": null
    }
  ]
}
PROMPT;
    }
}
