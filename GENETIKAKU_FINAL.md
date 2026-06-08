# GENETIKAKU
## Sistem Pakar Skrining Risiko Thalassemia dan Prediksi Karakteristik Fisik Bayi Menggunakan Metode Naive Bayes

## Konsep Sistem

GENETIKAKU merupakan sistem pakar berbasis web yang membantu calon orang tua melakukan skrining risiko Thalassemia serta memprediksi karakteristik fisik bayi berdasarkan data fenotipe orang tua menggunakan metode Naive Bayes.

Sistem tidak digunakan sebagai alat diagnosis medis, melainkan sebagai media skrining dan edukasi awal.

---

# Alur Sistem

## Tahap 1 – Skrining Risiko Thalassemia Orang Tua

### Data Ayah
- Riwayat keluarga Thalassemia
- Pernah didiagnosis Thalassemia
- Riwayat anemia
- Kadar Hb rendah
- Riwayat transfusi darah
- Gejala pendukung lainnya

### Data Ibu
- Riwayat keluarga Thalassemia
- Pernah didiagnosis Thalassemia
- Riwayat anemia
- Kadar Hb rendah
- Riwayat transfusi darah
- Gejala pendukung lainnya

### Output Tahap 1
- Normal
- Carrier Thalassemia
- Berisiko Tinggi Thalassemia

Hasil skrining digunakan sebagai atribut pendukung pada tahap berikutnya.

---

## Tahap 2 – Prediksi Karakteristik Fisik Bayi

### Fenotipe Ayah
- Golongan Darah
- Warna Iris Mata
- Tekstur Rambut
- Bentuk Cuping Telinga

### Fenotipe Ibu
- Golongan Darah
- Warna Iris Mata
- Tekstur Rambut
- Bentuk Cuping Telinga

### Status Thalassemia
- Diambil otomatis dari hasil Tahap 1

---

## Tahap 3 – Perhitungan Naive Bayes

Sistem melakukan:

1. Validasi data
2. Mengambil data training
3. Menghitung probabilitas prior
4. Menghitung probabilitas likelihood
5. Menghitung probabilitas posterior
6. Menentukan probabilitas terbesar
7. Menampilkan hasil prediksi

---

## Tahap 4 – Hasil Prediksi

### Karakteristik Fisik Bayi

- Golongan Darah
- Warna Iris Mata
- Tekstur Rambut
- Bentuk Cuping Telinga

### Risiko Thalassemia

- Rendah
- Sedang
- Tinggi

### Edukasi

- Penjelasan hasil
- Informasi Thalassemia
- Saran pemeriksaan lanjutan

---

# Aktor Sistem

## Pengguna (Orang Tua)

- Melihat halaman utama
- Melihat artikel
- Melihat tentang
- Melakukan skrining Thalassemia
- Melakukan prediksi
- Melihat hasil prediksi
- Mencetak hasil prediksi

## Admin

- Login
- Kelola Artikel
- Kelola Tentang
- Kelola Basis Pengetahuan
- Kelola Data Fenotipe
- Kelola Data Latih
- Kelola Hasil Prediksi

---

# Modul Sistem

## Frontend

- Landing Page
- Artikel
- Tentang
- Skrining Thalassemia
- Prediksi Bayi
- Hasil Prediksi

## Backend

- Manajemen Artikel
- Manajemen Tentang
- Manajemen Basis Pengetahuan
- Manajemen Data Fenotipe
- Manajemen Data Latih Naive Bayes
- Manajemen Hasil Prediksi

---

# Teknologi

## Backend
- Laravel 12

## Frontend
- React
- Inertia.js
- Tailwind CSS

## Database
- MySQL

## Metode
- Naive Bayes

---

# Struktur Database Awal

## users
- id
- name
- email
- password
- role

## articles
- id
- title
- content

## abouts
- id
- title
- content

## phenotypes
- id
- kategori
- nilai

## training_data
- id
- atribut_ayah
- atribut_ibu
- status_thal_ayah
- status_thal_ibu
- hasil_prediksi

## screening_results
- id
- nama_ayah
- nama_ibu
- hasil_skrining_ayah
- hasil_skrining_ibu

## prediction_results
- id
- screening_result_id
- hasil_fisik
- hasil_thalassemia
- probabilitas

---

# Catatan Akademik

GENETIKAKU berfungsi sebagai sistem skrining dan prediksi awal. Hasil yang diberikan tidak dapat digunakan sebagai diagnosis medis dan tidak menggantikan pemeriksaan laboratorium maupun konsultasi dengan tenaga kesehatan.
