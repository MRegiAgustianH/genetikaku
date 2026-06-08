# Requirements Document

## Introduction

GENETIKAKU adalah sistem pakar berbasis web yang membantu calon orang tua melakukan skrining risiko Thalassemia serta memprediksi karakteristik fisik bayi berdasarkan data fenotipe orang tua menggunakan metode Naive Bayes. Sistem ini bersifat skrining dan edukasi awal, **bukan** alat diagnosis medis, dan tidak menggantikan pemeriksaan laboratorium maupun konsultasi tenaga kesehatan.

Sistem memiliki dua jenis aktor: Pengguna Publik (calon orang tua) dan Admin. Alur utama untuk Pengguna Publik terdiri dari empat tahap: (1) skrining risiko Thalassemia ayah dan ibu, (2) input fenotipe orang tua, (3) perhitungan Naive Bayes, dan (4) penyajian hasil prediksi beserta edukasi. Admin mengelola konten dan data pendukung sistem: artikel, halaman tentang, basis pengetahuan skrining, data fenotipe, data latih Naive Bayes, dan hasil prediksi.

Sistem dibangun di atas scaffold yang sudah ada (Laravel 12, Inertia.js, React, Tailwind CSS, autentikasi Laravel Fortify) dengan database MySQL. Antarmuka mengikuti design system "Publication" (gaya editorial/modern, font Nunito/Oswald/JetBrains Mono, warna utama #A855F7, aksesibilitas WCAG 2.2 AA).

## Glossary

- **Sistem**: Aplikasi web GENETIKAKU secara keseluruhan.
- **Pengguna_Publik**: Calon orang tua yang menggunakan sistem tanpa autentikasi untuk melakukan skrining dan prediksi.
- **Admin**: Pengguna terautentikasi dengan peran `admin` yang mengelola konten dan data sistem.
- **Mesin_Skrining**: Komponen yang menghitung hasil skrining risiko Thalassemia satu orang tua berdasarkan jawaban indikator skrining dan basis pengetahuan.
- **Mesin_Naive_Bayes**: Komponen yang menghitung probabilitas prior, likelihood, dan posterior untuk memprediksi karakteristik fisik bayi dan risiko Thalassemia bayi berdasarkan data latih.
- **Hasil_Skrining_Orang_Tua**: Hasil skrining Tahap 1 untuk seorang orang tua, salah satu dari: `Normal`, `Carrier`, `Berisiko Tinggi`.
- **Risiko_Thalassemia_Bayi**: Klasifikasi risiko Thalassemia pada bayi hasil prediksi, salah satu dari: `Rendah`, `Sedang`, `Tinggi`.
- **Fenotipe**: Karakteristik fisik yang diamati, mencakup kategori: Golongan Darah, Warna Iris Mata, Tekstur Rambut, dan Bentuk Cuping Telinga.
- **Data_Fenotipe**: Daftar kategori fenotipe beserta nilai yang valid, dikelola Admin (tabel `phenotypes`).
- **Data_Latih**: Kumpulan baris data historis (tabel `training_data`) yang digunakan Mesin_Naive_Bayes untuk menghitung probabilitas.
- **Basis_Pengetahuan**: Kumpulan aturan/bobot indikator skrining Thalassemia yang dikelola Admin dan digunakan Mesin_Skrining.
- **Hasil_Skrining**: Record tersimpan dari proses Tahap 1 (tabel `screening_results`).
- **Hasil_Prediksi**: Record tersimpan dari proses Tahap 3–4 (tabel `prediction_results`).
- **Indikator_Skrining**: Pertanyaan skrining Thalassemia (riwayat keluarga, diagnosis sebelumnya, riwayat anemia, kadar Hb rendah, riwayat transfusi, gejala pendukung lainnya).
- **Design_System**: Aturan visual "Publication" yang diterapkan pada seluruh antarmuka.

## Requirements

### Requirement 1: Skrining Risiko Thalassemia Orang Tua (Tahap 1)

**User Story:** Sebagai Pengguna_Publik, saya ingin mengisi indikator skrining Thalassemia untuk ayah dan ibu, sehingga saya memperoleh klasifikasi risiko Thalassemia masing-masing orang tua.

#### Acceptance Criteria

1. WHEN Pengguna_Publik membuka halaman skrining, THE Sistem SHALL menampilkan formulir Indikator_Skrining terpisah untuk ayah dan ibu yang mencakup riwayat keluarga Thalassemia, riwayat diagnosis Thalassemia, riwayat anemia, kadar Hb rendah, riwayat transfusi darah, dan gejala pendukung lainnya.
2. WHEN Pengguna_Publik mengirim formulir skrining dengan seluruh Indikator_Skrining wajib terisi, THE Mesin_Skrining SHALL menghitung Hasil_Skrining_Orang_Tua untuk ayah dan ibu berdasarkan Basis_Pengetahuan.
3. THE Mesin_Skrining SHALL mengklasifikasikan setiap Hasil_Skrining_Orang_Tua ke dalam tepat satu kategori dari `Normal`, `Carrier`, atau `Berisiko Tinggi`.
4. IF satu atau lebih Indikator_Skrining wajib belum terisi saat formulir dikirim, THEN THE Sistem SHALL menolak pengiriman dan menampilkan pesan kesalahan yang menyebutkan indikator yang belum lengkap.
5. WHEN Mesin_Skrining selesai menghitung, THE Sistem SHALL menyimpan Hasil_Skrining yang berisi nama ayah, nama ibu, hasil skrining ayah, dan hasil skrining ibu.
6. WHEN Hasil_Skrining tersimpan, THE Sistem SHALL meneruskan kedua nilai Hasil_Skrining_Orang_Tua sebagai atribut masukan untuk Tahap 2.

### Requirement 2: Input Fenotipe Orang Tua (Tahap 2)

**User Story:** Sebagai Pengguna_Publik, saya ingin memasukkan fenotipe ayah dan ibu, sehingga sistem dapat memprediksi karakteristik fisik bayi.

#### Acceptance Criteria

1. WHEN Pengguna_Publik membuka halaman prediksi setelah menyelesaikan Tahap 1, THE Sistem SHALL menampilkan formulir input Fenotipe untuk ayah dan ibu mencakup Golongan Darah, Warna Iris Mata, Tekstur Rambut, dan Bentuk Cuping Telinga.
2. THE Sistem SHALL membatasi pilihan nilai setiap kategori Fenotipe sesuai Data_Fenotipe yang dikelola Admin.
3. WHEN halaman prediksi ditampilkan, THE Sistem SHALL menampilkan Hasil_Skrining_Orang_Tua ayah dan ibu dari Tahap 1 sebagai nilai yang sudah terisi otomatis dan tidak dapat diubah oleh Pengguna_Publik.
4. IF Pengguna_Publik mengakses halaman prediksi tanpa menyelesaikan Tahap 1, THEN THE Sistem SHALL mengarahkan Pengguna_Publik ke halaman skrining Tahap 1.
5. IF satu atau lebih kategori Fenotipe belum dipilih saat formulir dikirim, THEN THE Sistem SHALL menolak pengiriman dan menampilkan pesan kesalahan yang menyebutkan kategori yang belum lengkap.

### Requirement 3: Perhitungan Naive Bayes (Tahap 3)

**User Story:** Sebagai Pengguna_Publik, saya ingin sistem menghitung prediksi menggunakan metode Naive Bayes, sehingga hasil prediksi didasarkan pada data latih yang ada.

#### Acceptance Criteria

1. WHEN data Fenotipe dan Hasil_Skrining_Orang_Tua dari Tahap 2 dikirim, THE Mesin_Naive_Bayes SHALL memvalidasi bahwa setiap atribut masukan memiliki nilai yang terdaftar pada Data_Fenotipe atau kategori Hasil_Skrining_Orang_Tua yang valid.
2. WHEN data masukan valid, THE Mesin_Naive_Bayes SHALL mengambil Data_Latih sebagai dasar perhitungan.
3. THE Mesin_Naive_Bayes SHALL menghitung probabilitas prior untuk setiap kelas keluaran berdasarkan frekuensi kelas pada Data_Latih.
4. THE Mesin_Naive_Bayes SHALL menghitung probabilitas likelihood setiap atribut masukan terhadap setiap kelas keluaran berdasarkan Data_Latih.
5. THE Mesin_Naive_Bayes SHALL menghitung probabilitas posterior setiap kelas sebagai hasil perkalian probabilitas prior dengan seluruh probabilitas likelihood atribut masukan.
6. WHEN seluruh probabilitas posterior telah dihitung, THE Mesin_Naive_Bayes SHALL memilih kelas dengan probabilitas posterior terbesar sebagai hasil prediksi.
7. IF sebuah probabilitas likelihood bernilai nol, THEN THE Mesin_Naive_Bayes SHALL menerapkan penghalusan Laplace (Laplace smoothing) agar probabilitas likelihood tidak bernilai nol.
8. IF Data_Latih kosong saat perhitungan diminta, THEN THE Sistem SHALL membatalkan perhitungan dan menampilkan pesan bahwa prediksi belum dapat dilakukan karena data latih belum tersedia.

### Requirement 4: Hasil Prediksi dan Edukasi (Tahap 4)

**User Story:** Sebagai Pengguna_Publik, saya ingin melihat hasil prediksi karakteristik fisik bayi dan risiko Thalassemia beserta penjelasan edukatif, sehingga saya memahami arti hasil tersebut.

#### Acceptance Criteria

1. WHEN Mesin_Naive_Bayes selesai memilih hasil prediksi, THE Sistem SHALL menampilkan prediksi karakteristik fisik bayi untuk Golongan Darah, Warna Iris Mata, Tekstur Rambut, dan Bentuk Cuping Telinga.
2. WHEN hasil prediksi ditampilkan, THE Sistem SHALL menampilkan Risiko_Thalassemia_Bayi dengan tepat satu klasifikasi dari `Rendah`, `Sedang`, atau `Tinggi`.
3. WHEN hasil prediksi ditampilkan, THE Sistem SHALL menampilkan nilai probabilitas posterior yang menjadi dasar setiap prediksi.
4. WHEN hasil prediksi ditampilkan, THE Sistem SHALL menampilkan konten edukasi yang mencakup penjelasan hasil, informasi Thalassemia, dan saran pemeriksaan lanjutan.
5. THE Sistem SHALL menampilkan pernyataan penyangkalan (disclaimer) bahwa hasil bersifat skrining dan edukasi awal serta bukan diagnosis medis pada halaman hasil prediksi.
6. WHEN hasil prediksi selesai dihitung, THE Sistem SHALL menyimpan Hasil_Prediksi yang berisi referensi ke Hasil_Skrining, hasil fisik, hasil Thalassemia, dan probabilitas.

### Requirement 5: Cetak Hasil Prediksi

**User Story:** Sebagai Pengguna_Publik, saya ingin mencetak hasil prediksi, sehingga saya dapat menyimpan atau membawa hasil tersebut secara fisik.

#### Acceptance Criteria

1. WHEN Pengguna_Publik berada pada halaman hasil prediksi, THE Sistem SHALL menyediakan aksi untuk mencetak hasil prediksi.
2. WHEN Pengguna_Publik memilih aksi cetak, THE Sistem SHALL menghasilkan tampilan cetak yang memuat karakteristik fisik bayi, Risiko_Thalassemia_Bayi, probabilitas, konten edukasi, dan pernyataan penyangkalan.

### Requirement 6: Halaman Utama dan Navigasi Publik

**User Story:** Sebagai Pengguna_Publik, saya ingin halaman utama yang memperkenalkan sistem dan menyediakan navigasi, sehingga saya dapat memulai skrining atau menjelajahi konten.

#### Acceptance Criteria

1. WHEN Pengguna_Publik membuka halaman utama, THE Sistem SHALL menampilkan penjelasan ringkas tentang GENETIKAKU dan tautan untuk memulai skrining Thalassemia.
2. THE Sistem SHALL menyediakan navigasi ke halaman Artikel, halaman Tentang, dan alur Skrining Thalassemia dari halaman utama.
3. WHEN Pengguna_Publik membuka halaman utama, THE Sistem SHALL menampilkan pernyataan penyangkalan bahwa sistem bersifat skrining dan edukasi, bukan diagnosis medis.
4. IF pernyataan penyangkalan gagal ditampilkan karena kesalahan teknis, THEN THE Sistem SHALL menahan akses ke alur skrining sampai pernyataan penyangkalan berhasil ditampilkan.

### Requirement 7: Tampilan Artikel Publik

**User Story:** Sebagai Pengguna_Publik, saya ingin membaca artikel edukasi, sehingga saya memperoleh informasi tentang Thalassemia dan genetika.

#### Acceptance Criteria

1. WHEN Pengguna_Publik membuka halaman daftar artikel, THE Sistem SHALL menampilkan daftar artikel yang dipublikasikan beserta judul.
2. WHEN Pengguna_Publik memilih satu artikel, THE Sistem SHALL menampilkan judul dan konten lengkap artikel tersebut.
3. IF artikel yang diminta tidak ada, atau artikel yang diminta belum dipublikasikan (berstatus draft), THEN THE Sistem SHALL menampilkan halaman pemberitahuan bahwa artikel tidak ditemukan.

### Requirement 8: Tampilan Tentang Publik

**User Story:** Sebagai Pengguna_Publik, saya ingin melihat halaman tentang, sehingga saya memahami tujuan dan latar belakang sistem.

#### Acceptance Criteria

1. WHEN Pengguna_Publik membuka halaman Tentang, THE Sistem SHALL menampilkan judul dan konten halaman Tentang yang dikelola Admin.
2. IF belum ada konten Tentang yang dibuat Admin, THEN THE Sistem SHALL menampilkan pesan placeholder yang menyatakan konten belum tersedia.

### Requirement 9: Autentikasi dan Otorisasi Admin

**User Story:** Sebagai Admin, saya ingin masuk ke area administrasi yang terlindungi, sehingga hanya pengguna berwenang yang dapat mengelola konten dan data.

#### Acceptance Criteria

1. WHEN Admin mengirim kredensial yang valid pada halaman login, THE Sistem SHALL membuat sesi terautentikasi dan mengarahkan Admin ke dasbor administrasi.
2. IF kredensial yang dikirim tidak valid, THEN THE Sistem SHALL menolak login dan menampilkan pesan kesalahan autentikasi.
3. IF pengguna tanpa peran `admin` mencoba mengakses halaman administrasi, THEN THE Sistem SHALL menolak akses dan mengarahkan pengguna ke halaman login.
4. WHILE sebuah sesi Admin aktif, THE Sistem SHALL mengizinkan akses ke seluruh modul manajemen konten dan data.
5. WHEN Admin memilih keluar (logout), THE Sistem SHALL mengakhiri sesi terautentikasi dan mengarahkan Admin ke halaman login.

### Requirement 10: Manajemen Artikel

**User Story:** Sebagai Admin, saya ingin mengelola artikel, sehingga konten edukasi pada sistem selalu mutakhir.

#### Acceptance Criteria

1. WHEN Admin mengirim formulir pembuatan artikel dengan judul dan konten terisi, THE Sistem SHALL menyimpan artikel baru dan menampilkannya pada daftar artikel administrasi.
2. WHEN Admin mengirim perubahan pada artikel yang ada, THE Sistem SHALL memperbarui judul dan konten artikel tersebut.
3. WHEN Admin menghapus artikel, THE Sistem SHALL menghapus artikel tersebut dari daftar.
4. IF Admin mengirim formulir artikel dengan judul atau konten kosong, THEN THE Sistem SHALL menolak penyimpanan dan menampilkan pesan kesalahan validasi.

### Requirement 11: Manajemen Halaman Tentang

**User Story:** Sebagai Admin, saya ingin mengelola konten halaman Tentang, sehingga informasi sistem dapat diperbarui.

#### Acceptance Criteria

1. WHEN Admin mengirim perubahan pada halaman Tentang dengan judul dan konten terisi, THE Sistem SHALL menyimpan dan menampilkan konten terbaru pada halaman Tentang publik.
2. IF Admin mengirim formulir Tentang dengan judul atau konten kosong, THEN THE Sistem SHALL menolak penyimpanan dan menampilkan pesan kesalahan validasi.

### Requirement 12: Manajemen Basis Pengetahuan

**User Story:** Sebagai Admin, saya ingin mengelola Basis_Pengetahuan skrining Thalassemia, sehingga Mesin_Skrining mengklasifikasikan risiko sesuai aturan yang ditetapkan.

#### Acceptance Criteria

1. WHEN Admin membuka modul Basis_Pengetahuan, THE Sistem SHALL menampilkan daftar aturan Indikator_Skrining beserta bobot atau pemetaan klasifikasinya.
2. WHEN Admin menambah, mengubah, atau menghapus aturan Basis_Pengetahuan, THE Sistem SHALL menyimpan perubahan tersebut dan menggunakannya pada perhitungan Mesin_Skrining berikutnya.
3. IF operasi penyimpanan aturan Basis_Pengetahuan gagal karena kesalahan teknis, THEN THE Sistem SHALL mempertahankan data sebelumnya dan menampilkan notifikasi kegagalan kepada Admin.
4. IF Admin mengirim aturan Basis_Pengetahuan dengan field wajib kosong, THEN THE Sistem SHALL menolak penyimpanan dan menampilkan pesan kesalahan validasi.

### Requirement 13: Manajemen Data Fenotipe

**User Story:** Sebagai Admin, saya ingin mengelola Data_Fenotipe, sehingga pilihan nilai fenotipe pada formulir prediksi konsisten dan terkontrol.

#### Acceptance Criteria

1. WHEN Admin membuka modul Data_Fenotipe, THE Sistem SHALL menampilkan daftar entri Data_Fenotipe beserta kategori dan nilainya.
2. WHEN Admin menambah, mengubah, atau menghapus entri Data_Fenotipe, THE Sistem SHALL menyimpan perubahan tersebut dan segera menampilkan nilai terbaru pada formulir prediksi Tahap 2 tanpa memerlukan muat ulang manual.
3. IF Admin mengirim entri Data_Fenotipe dengan kategori atau nilai kosong, THEN THE Sistem SHALL menolak penyimpanan dan menampilkan pesan kesalahan validasi.

### Requirement 14: Manajemen Data Latih Naive Bayes

**User Story:** Sebagai Admin, saya ingin mengelola Data_Latih, sehingga Mesin_Naive_Bayes menghasilkan prediksi berdasarkan data yang terkini dan benar.

#### Acceptance Criteria

1. WHEN Admin membuka modul Data_Latih, THE Sistem SHALL menampilkan daftar baris Data_Latih beserta atribut ayah, atribut ibu, status Thalassemia ayah, status Thalassemia ibu, dan hasil prediksi.
2. WHEN Admin menambah, mengubah, atau menghapus baris Data_Latih, THE Sistem SHALL menyimpan perubahan tersebut dan menggunakannya pada perhitungan Mesin_Naive_Bayes berikutnya.
3. IF Admin mengirim baris Data_Latih dengan nilai atribut yang tidak terdaftar pada Data_Fenotipe maupun pada kategori Hasil_Skrining_Orang_Tua, THEN THE Sistem SHALL menolak penyimpanan dan menampilkan pesan kesalahan validasi.

### Requirement 15: Manajemen Hasil Prediksi

**User Story:** Sebagai Admin, saya ingin melihat dan mengelola Hasil_Prediksi yang tersimpan, sehingga saya dapat meninjau riwayat penggunaan sistem.

#### Acceptance Criteria

1. WHEN Admin membuka modul Hasil_Prediksi, THE Sistem SHALL menampilkan daftar Hasil_Prediksi tersimpan beserta hasil fisik, hasil Thalassemia, dan probabilitas.
2. WHEN Admin memilih satu Hasil_Prediksi, THE Sistem SHALL menampilkan detail lengkap termasuk Hasil_Skrining yang terkait.
3. WHEN Admin menghapus satu Hasil_Prediksi, THE Sistem SHALL menghapus record tersebut dari daftar.

### Requirement 16: Penerapan Design System dan Aksesibilitas

**User Story:** Sebagai Pengguna_Publik maupun Admin, saya ingin antarmuka yang konsisten dan dapat diakses, sehingga sistem mudah dan nyaman digunakan oleh semua orang.

#### Acceptance Criteria

1. THE Sistem SHALL menerapkan token Design_System "Publication" untuk tipografi (Nunito, Oswald, JetBrains Mono) dan warna (warna utama #A855F7) pada seluruh halaman.
2. THE Sistem SHALL menyediakan status fokus yang terlihat pada seluruh elemen interaktif sesuai standar WCAG 2.2 AA.
3. THE Sistem SHALL menyediakan label yang dapat dibaca pembaca layar (screen reader) untuk seluruh kontrol formulir.
4. THE Sistem SHALL memastikan rasio kontras teks terhadap latar memenuhi minimum WCAG 2.2 AA (4.5:1 untuk teks normal).
5. WHERE pengguna mengaktifkan preferensi gerak tereduksi (reduced motion), THE Sistem SHALL segera menonaktifkan seluruh animasi non-esensial saat preferensi terdeteksi.
6. THE Sistem SHALL menyediakan area sentuh interaktif berukuran minimal 44x44 piksel.
7. WHEN sebuah halaman memuat data, mengalami kondisi kosong, atau mengalami kesalahan, THE Sistem SHALL menampilkan tepat satu status (loading, empty, atau error) berdasarkan satu sumber kebenaran (single source of truth) status halaman sehingga tidak terjadi indikator status yang saling bertentangan.
