# Implementation Plan: GENETIKAKU Expert System

## Overview

Rencana implementasi ini mengubah desain GENETIKAKU menjadi langkah-langkah kode inkremental di atas scaffold Laravel 13 + Inertia.js + React + Tailwind + Fortify yang sudah ada. Urutan dirancang agar lapisan inti (enum/DTO, migrasi, model) dibangun lebih dulu, lalu dua mesin domain murni (`ScreeningEngine`, `NaiveBayesClassifier`) yang menjadi jantung sistem dan paling banyak diuji properti, kemudian persistensi alur empat tahap, controller/halaman publik, area admin, middleware otorisasi, dan terakhir invarian state UI serta penerapan Design System.

Pengujian property-based (PBT) menggunakan Pest 4 dengan generator `giorgiosironi/eris` untuk backend (Properti 1–18) dan Vitest + `fast-check` untuk invarian state UI frontend (Properti 19). Setiap property test menjalankan minimum 100 iterasi dan diberi tag komentar `// Feature: genetikaku-expert-system, Property {n}: {text}`.

## Tasks

- [x] 1. Siapkan fondasi proyek: dependensi, enum, DTO, dan exception domain
  - [x] 1.1 Pasang dan konfigurasi pustaka PBT backend
    - Tambahkan `giorgiosironi/eris` ke `require-dev` via composer dan pastikan terintegrasi dengan Pest 4
    - Konfigurasi MySQL di `.env`/`phpunit.xml` (testing) sesuai keputusan desain MySQL, dengan kompatibilitas migrasi untuk pengujian lokal
    - _Requirements: 3.3, 3.4_

  - [x] 1.2 Buat enum dan value object domain
    - Buat enum `App\Domain\ScreeningCategory` (`Normal`, `Carrier`, `BerisikoTinggi`)
    - Buat enum `App\Domain\ThalassemiaRisk` (`Rendah`, `Sedang`, `Tinggi`)
    - Buat enum `App\Domain\PhenotypeCategory` (`GolonganDarah`, `WarnaIris`, `TeksturRambut`, `BentukCuping`)
    - Buat DTO `App\Domain\TrainingRow`, `App\Domain\KnowledgeBaseRule`, dan `App\Domain\PredictionOutcome`
    - _Requirements: 1.3, 4.2_

  - [x] 1.3 Buat exception domain
    - Buat `App\Services\Exceptions\EmptyTrainingDataException` dan `App\Services\Exceptions\InvalidAttributeException`
    - _Requirements: 3.1, 3.8_

- [x] 2. Implementasikan skema database dan model Eloquent
  - [x] 2.1 Tulis migrasi untuk seluruh tabel domain
    - Tambah kolom `role` (`admin|user`, default `user`) pada migrasi users
    - Buat migrasi `articles`, `about_pages`, `knowledge_base_rules`, `phenotypes`, `training_data`, `screening_results`, `prediction_results` sesuai ERD desain (termasuk FK `prediction_results.screening_result_id` dan kolom JSON)
    - _Requirements: 1.5, 4.6, 7.1, 8.1, 12.1, 13.1, 14.1, 15.1_

  - [x] 2.2 Buat model Eloquent dan relasi
    - Buat model `Article`, `AboutPage`, `KnowledgeBaseRule`, `Phenotype`, `TrainingData`, `ScreeningResult`, `PredictionResult` dengan cast (`physical_result`, `probabilities` sebagai array/JSON; enum cast untuk hasil)
    - Definisikan relasi `ScreeningResult hasOne PredictionResult` dan `PredictionResult belongsTo ScreeningResult`
    - Tambah kolom/cast `role` pada model `User` dan helper `isAdmin()`
    - _Requirements: 1.5, 4.6, 9.3_

  - [x] 2.3 Buat factory dan seeder data awal
    - Buat factory untuk semua model domain
    - Buat seeder Data_Fenotipe dan Data_Latih awal serta user admin contoh
    - _Requirements: 2.2, 3.2, 13.1, 14.1_

- [x] 3. Implementasikan Mesin_Skrining (ScreeningEngine)
  - [x] 3.1 Implementasikan `ScreeningEngine.classify`
    - Buat `App\Services\ScreeningEngine` murni yang menerima jawaban indikator + array `KnowledgeBaseRule`, menjumlahkan bobot, dan memetakan skor ke `ScreeningCategory` via threshold dari Basis_Pengetahuan
    - Pastikan selalu mengembalikan tepat satu kategori dan deterministik untuk input lengkap
    - _Requirements: 1.2, 1.3, 12.2_

  - [x] 3.2 Tulis property test: klasifikasi total dan deterministik
    - **Property 1: Klasifikasi skrining bersifat total dan deterministik**
    - **Validates: Requirements 1.2, 1.3, 12.2**
    - Generator Eris untuk jawaban indikator lengkap + Basis_Pengetahuan; verifikasi hasil ∈ {Normal, Carrier, Berisiko Tinggi} dan idempoten pada pemanggilan berulang

- [x] 4. Implementasikan Mesin_Naive_Bayes (NaiveBayesClassifier)
  - [x] 4.1 Implementasikan validasi input dan guard data latih
    - Buat `App\Services\NaiveBayesClassifier` yang melempar `InvalidAttributeException` bila ada nilai atribut tak terdaftar, dan `EmptyTrainingDataException` bila Data_Latih kosong
    - _Requirements: 3.1, 3.8_

  - [x] 4.2 Implementasikan perhitungan prior, likelihood (Laplace), posterior, dan pemilihan kelas
    - Hitung prior `P(c)`, likelihood `P(x_i|c)` dengan Laplace smoothing, skor posterior tak ternormalisasi, dan pilih kelas dengan skor maksimum per variabel keluaran
    - Hasilkan `PredictionOutcome` lengkap (4 kategori fisik + Risiko_Thalassemia_Bayi) plus probabilitas posterior ternormalisasi untuk ditampilkan
    - _Requirements: 3.3, 3.4, 3.5, 3.6, 3.7, 4.1, 4.2, 4.3_

  - [x] 4.3 Tulis property test: distribusi prior valid
    - **Property 3: Probabilitas prior membentuk distribusi yang valid**
    - **Validates: Requirements 3.3**

  - [x] 4.4 Tulis property test: Laplace smoothing menjamin likelihood positif
    - **Property 4: Laplace smoothing menjamin likelihood positif**
    - **Validates: Requirements 3.4, 3.7**

  - [x] 4.5 Tulis property test: posterior = prior × hasil kali likelihood
    - **Property 5: Posterior sama dengan prior dikali hasil kali likelihood**
    - **Validates: Requirements 3.5**

  - [x] 4.6 Tulis property test: prediksi memilih posterior maksimum
    - **Property 6: Prediksi memilih kelas dengan posterior maksimum**
    - **Validates: Requirements 3.6**

  - [x] 4.7 Tulis property test: probabilitas posterior ditampilkan ternormalisasi
    - **Property 7: Probabilitas posterior yang ditampilkan ternormalisasi**
    - **Validates: Requirements 4.3**

  - [x] 4.8 Tulis property test: keluaran lengkap dan klasifikasi risiko total
    - **Property 8: Keluaran prediksi lengkap dan klasifikasi risiko bersifat total**
    - **Validates: Requirements 4.1, 4.2**

  - [x] 4.9 Tulis property test: menolak nilai atribut tak terdaftar
    - **Property 9: Naive Bayes menolak nilai atribut tak terdaftar**
    - **Validates: Requirements 3.1**

  - [x] 4.10 Tulis property test: data latih kosong membatalkan perhitungan
    - **Property 10: Data latih kosong membatalkan perhitungan**
    - **Validates: Requirements 3.8**

- [x] 5. Checkpoint - Pastikan mesin domain lulus uji
  - Ensure all tests pass, ask the user if questions arise.

- [x] 6. Implementasikan alur publik Tahap 1 (Skrining) dan persistensinya
  - [x] 6.1 Buat Form Request dan ScreeningController (GET/POST `/skrining`)
    - Tampilkan formulir indikator ayah+ibu (Req 1.1), validasi indikator wajib via Form Request, panggil `ScreeningEngine`, simpan `screening_result`, dan simpan id ke sesi lalu redirect ke `/prediksi`
    - _Requirements: 1.1, 1.4, 1.5, 1.6_

  - [x] 6.2 Buat halaman React `public/screening` dan `public-layout.tsx`
    - Layout publik (header nav: Home, Artikel, Tentang, Skrining; footer disclaimer) dan form skrining ayah/ibu
    - _Requirements: 1.1, 6.2_

  - [x] 6.3 Tulis property test: skrining menolak indikator tidak lengkap
    - **Property 2: Skrining menolak indikator yang tidak lengkap**
    - **Validates: Requirements 1.4**

  - [x] 6.4 Tulis property test: penyimpanan Hasil_Skrining round trip
    - **Property 11: Penyimpanan Hasil_Skrining round trip**
    - **Validates: Requirements 1.5**

- [x] 7. Implementasikan alur publik Tahap 2–4 (Prediksi, Hasil, Cetak)
  - [x] 7.1 Buat middleware `EnsureScreeningCompleted`
    - Redirect ke `/skrining` bila `screening_result_id` sesi tidak valid
    - _Requirements: 2.4_

  - [x] 7.2 Implementasikan PredictionController GET `/prediksi` (form Tahap 2)
    - Sediakan opsi fenotipe dari Data_Fenotipe sebagai props, tampilkan Hasil_Skrining read-only (pre-filled), dukung Inertia partial reload untuk refleksi perubahan Data_Fenotipe
    - _Requirements: 2.1, 2.2, 2.3_

  - [x] 7.3 Implementasikan PredictionController POST `/prediksi` (Tahap 3–4)
    - Form Request memvalidasi kategori fenotipe wajib; panggil `NaiveBayesClassifier`; tangani `EmptyTrainingDataException`/`InvalidAttributeException`; simpan `prediction_result`; render halaman hasil dengan edukasi + disclaimer
    - _Requirements: 2.5, 3.1, 3.8, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6_

  - [x] 7.4 Implementasikan tampilan cetak `GET /prediksi/{id}/cetak`
    - Render tampilan cetak memuat fisik bayi, Risiko_Thalassemia_Bayi, probabilitas, edukasi, dan disclaimer
    - _Requirements: 5.1, 5.2_

  - [x] 7.5 Buat halaman React `public/prediction/form` dan `public/prediction/result`
    - Form fenotipe dengan opsi dari props server + hasil skrining read-only; halaman hasil dengan probabilitas, edukasi, disclaimer, dan aksi cetak
    - _Requirements: 2.1, 2.3, 4.1, 4.4, 4.5, 5.1_

  - [x] 7.6 Tulis property test: opsi form prediksi = Data_Fenotipe terkini
    - **Property 14: Opsi form prediksi sama dengan Data_Fenotipe terkini**
    - **Validates: Requirements 2.2, 13.2**

  - [x] 7.7 Tulis property test: prediksi menolak kategori fenotipe belum dipilih
    - **Property 15: Prediksi menolak kategori fenotipe yang belum dipilih**
    - **Validates: Requirements 2.5**

  - [x] 7.8 Tulis property test: penyimpanan Hasil_Prediksi round trip
    - **Property 12: Penyimpanan Hasil_Prediksi round trip**
    - **Validates: Requirements 4.6**

  - [x] 7.9 Tulis property test: tampilan cetak memuat seluruh bagian wajib
    - **Property 17: Tampilan cetak memuat seluruh bagian wajib**
    - **Validates: Requirements 5.2**

- [x] 8. Checkpoint - Pastikan alur empat tahap lulus uji
  - Ensure all tests pass, ask the user if questions arise.

- [x] 9. Implementasikan halaman publik konten (Home, Artikel, Tentang)
  - [x] 9.1 Implementasikan HomeController dan halaman `public/home`
    - Tampilkan penjelasan GENETIKAKU, tautan mulai skrining, navigasi, dan disclaimer; tahan akses alur skrining bila disclaimer gagal dirender
    - _Requirements: 6.1, 6.2, 6.3, 6.4_

  - [x] 9.2 Implementasikan ArticleController publik (`/artikel`, `/artikel/{slug}`) dan halaman terkait
    - Daftar hanya artikel `published`; detail artikel; halaman not-found untuk draft/slug tak ada (`public/articles/index`, `show`, `not-found`)
    - _Requirements: 7.1, 7.2, 7.3_

  - [x] 9.3 Implementasikan AboutController publik (`/tentang`) dan halaman `public/about`
    - Tampilkan judul+konten Tentang; placeholder bila belum ada konten
    - _Requirements: 8.1, 8.2_

  - [x] 9.4 Tulis property test: tampilan publik artikel hanya memuat artikel terpublikasi
    - **Property 16: Tampilan publik artikel hanya memuat artikel terpublikasi**
    - **Validates: Requirements 7.1, 7.3**

- [x] 10. Implementasikan otorisasi admin dan middleware
  - [x] 10.1 Buat middleware `EnsureUserIsAdmin` dan grup route `/admin`
    - Tolak pengguna non-`admin` (termasuk tamu) dan redirect ke login; integrasikan dengan auth Fortify yang ada
    - _Requirements: 9.1, 9.3, 9.4, 9.5_

  - [x] 10.2 Tulis property test: route admin menolak akses non-admin
    - **Property 18: Route admin menolak akses non-admin**
    - **Validates: Requirements 9.3**

- [x] 11. Implementasikan modul CRUD admin
  - [x] 11.1 Implementasikan `Admin\ArticleController` (resource) dan halaman admin artikel
    - CRUD artikel dengan validasi judul/konten wajib (status draft/published)
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

  - [x] 11.2 Tulis feature test untuk CRUD artikel admin
    - Uji create/update/delete dan validasi field kosong
    - _Requirements: 10.1, 10.2, 10.3, 10.4_

  - [x] 11.3 Implementasikan `Admin\AboutController` (GET/PUT) dan halaman admin tentang
    - Simpan judul/konten dengan validasi wajib
    - _Requirements: 11.1, 11.2_

  - [x] 11.4 Implementasikan `Admin\KnowledgeBaseController` (resource) dengan transaksi DB
    - CRUD aturan Basis_Pengetahuan dibungkus transaksi (rollback + flash gagal), validasi field wajib
    - _Requirements: 12.1, 12.2, 12.3, 12.4_

  - [x] 11.5 Tulis feature test untuk Basis_Pengetahuan
    - Uji CRUD, validasi field wajib, dan rollback transaksi saat gagal
    - _Requirements: 12.1, 12.3, 12.4_

  - [x] 11.6 Implementasikan `Admin\PhenotypeController` (resource) dan halaman admin fenotipe
    - CRUD Data_Fenotipe dengan validasi kategori/nilai wajib
    - _Requirements: 13.1, 13.2, 13.3_

  - [x] 11.7 Implementasikan `Admin\TrainingDataController` (resource) dengan validasi nilai
    - CRUD Data_Latih; tolak baris dengan nilai di luar Data_Fenotipe/kategori Hasil_Skrining_Orang_Tua
    - _Requirements: 14.1, 14.2, 14.3_

  - [x] 11.8 Tulis property test: Data Latih menolak nilai di luar Data_Fenotipe/kategori skrining
    - **Property 13: Data Latih menolak nilai di luar Data_Fenotipe/kategori skrining**
    - **Validates: Requirements 14.3**

  - [x] 11.9 Implementasikan `Admin\PredictionResultController` (index/show/destroy) dan halaman terkait
    - Daftar Hasil_Prediksi, detail termasuk Hasil_Skrining terkait, hapus record
    - _Requirements: 15.1, 15.2, 15.3_

- [x] 12. Checkpoint - Pastikan modul admin lulus uji
  - Ensure all tests pass, ask the user if questions arise.

- [x] 13. Implementasikan invarian state UI dan penerapan Design System
  - [x] 13.1 Implementasikan resolver status halaman dan komponen `PageState`
    - Buat `resolvePageState` (single source of truth) yang memetakan props ke tepat satu dari `loading | empty | error | ready`, dan komponen `PageState` yang merender tepat satu indikator
    - _Requirements: 16.7_

  - [x] 13.2 Tulis property test frontend: halaman merender tepat satu status
    - **Property 19: Halaman merender tepat satu status**
    - **Validates: Requirements 16.7**
    - Vitest + fast-check: untuk sembarang kombinasi loading/empty/error, hanya satu indikator dirender

  - [x] 13.3 Terapkan token Design System "Publication" dan aksesibilitas
    - Konfigurasi Tailwind/token: tipografi (Nunito, Oswald, JetBrains Mono), warna utama #A855F7, fokus terlihat, label screen-reader, kontras, reduced-motion, dan target sentuh 44x44px pada layout publik dan admin
    - _Requirements: 16.1, 16.2, 16.3, 16.4, 16.5, 16.6_

  - [x] 13.4 Tulis audit aksesibilitas otomatis (axe/jest-axe)
    - Audit halaman publik dan admin untuk kontras, label, dan fokus (verifikasi manual dicatat sebagai tindak lanjut di luar kode)
    - _Requirements: 16.2, 16.3, 16.4_

- [x] 14. Integrasi dan wiring akhir
  - [x] 14.1 Daftarkan seluruh route, middleware, dan navigasi
    - Wire route publik dan admin di `routes/web.php`, daftarkan middleware alias, dan hubungkan navigasi `public-layout`/`app-layout`
    - _Requirements: 2.4, 6.2, 9.3, 9.4_

  - [x] 14.2 Tulis feature test integrasi alur end-to-end empat tahap
    - Uji jalur skrining → prediksi → hasil → cetak via HTTP/Inertia sebagai satu contoh representatif
    - _Requirements: 1.6, 2.3, 4.6, 5.2_

- [x] 15. Checkpoint akhir - Pastikan semua test lulus
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tugas bertanda `*` bersifat opsional (pengujian) dan dapat dilewati untuk MVP lebih cepat; tugas inti tidak ditandai.
- Setiap tugas merujuk requirement spesifik untuk keterlacakan.
- Property test mengimplementasikan Properti 1–19 dari desain; setiap properti = satu PBT, minimum 100 iterasi, dengan tag komentar `// Feature: genetikaku-expert-system, Property {n}`.
- Properti 1–18 memakai Pest + Eris (backend); Properti 19 memakai Vitest + fast-check (frontend).
- CRUD sederhana (artikel, tentang, basis pengetahuan, fenotipe) diuji dengan feature/contoh, bukan PBT, sesuai Testing Strategy.
- Checkpoint memastikan validasi inkremental pada batas yang wajar.

## Task Dependency Graph

```json
{
  "waves": [
    { "id": 0, "tasks": ["1.1", "1.2", "1.3"] },
    { "id": 1, "tasks": ["2.1"] },
    { "id": 2, "tasks": ["2.2"] },
    { "id": 3, "tasks": ["2.3", "3.1", "4.1"] },
    { "id": 4, "tasks": ["3.2", "4.2", "10.1"] },
    { "id": 5, "tasks": ["4.3", "4.4", "4.5", "4.6", "4.7", "4.8", "4.9", "4.10", "10.2"] },
    { "id": 6, "tasks": ["6.1", "7.1", "9.1", "9.2", "9.3", "11.1", "11.3", "11.4", "11.6", "11.7", "11.9"] },
    { "id": 7, "tasks": ["6.2", "7.2", "11.2", "11.5", "11.8", "9.4"] },
    { "id": 8, "tasks": ["7.3", "6.3", "6.4"] },
    { "id": 9, "tasks": ["7.4", "7.5", "7.6", "7.7", "7.8"] },
    { "id": 10, "tasks": ["7.9", "13.1", "13.3"] },
    { "id": 11, "tasks": ["13.2", "13.4", "14.1"] },
    { "id": 12, "tasks": ["14.2"] }
  ]
}
```
