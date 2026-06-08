<?php

use App\Http\Controllers\Admin\AboutController as AdminAboutController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\PhenotypeController;
use App\Http\Controllers\Admin\PredictionResultController;
use App\Http\Controllers\Admin\KnowledgeBaseController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\TrainingDataController;
use App\Http\Controllers\Public\AboutController;
use App\Http\Controllers\Public\ArticleController;
use App\Http\Controllers\Public\PredictionController;
use App\Http\Controllers\Public\ScreeningController;
use App\Http\Controllers\Public\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Alur publik Tahap 1: Skrining Risiko Thalassemia (Req 1).
Route::get('/skrining', [ScreeningController::class, 'show'])->name('skrining.show');
Route::post('/skrining', [ScreeningController::class, 'store'])->name('skrining.store');

// Alur publik Tahap 2: Input Fenotipe (Req 2). Dijaga guard skrining (Req 2.4).
Route::get('/prediksi', [PredictionController::class, 'create'])
    ->middleware('screening.completed')
    ->name('prediksi.create');

// Alur publik Tahap 3–4: Perhitungan Naive Bayes & Hasil (Req 3, 4).
// Dijaga guard skrining (Req 2.4).
Route::post('/prediksi', [PredictionController::class, 'store'])
    ->middleware('screening.completed')
    ->name('prediksi.store');

// Alur publik Tahap 5: Cetak Hasil Prediksi (Req 5). Tanpa guard `screening.completed`:
// memuat sebuah Hasil_Prediksi tersimpan berdasarkan id (implicit binding), sehingga
// hasil dapat dicetak kapan pun tanpa memerlukan sesi skrining aktif.
Route::get('/prediksi/{predictionResult}/cetak', [PredictionController::class, 'print'])
    ->name('prediksi.print');

Route::get('artikel', [ArticleController::class, 'index'])->name('artikel.index');
Route::get('artikel/{slug}', [ArticleController::class, 'show'])->name('artikel.show');

Route::get('tentang', [AboutController::class, 'show'])->name('tentang.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::inertia('/', 'admin/dashboard')->name('dashboard');

    Route::resource('artikel', AdminArticleController::class)->except(['show']);

    // Modul manajemen Data_Fenotipe (Req 13).
    Route::resource('fenotipe', PhenotypeController::class)->except(['show']);

    // Manajemen halaman Tentang: satu record dikelola via GET/PUT (Req 11.1, 11.2).
    Route::get('tentang', [AdminAboutController::class, 'edit'])->name('tentang.edit');
    Route::put('tentang', [AdminAboutController::class, 'update'])->name('tentang.update');

    // Manajemen aset media/ilustrasi terkelola (mis. ilustrasi halaman skrining).
    // POST dipakai untuk unggah berkas multipart; {key} adalah key aset media.
    Route::get('media', [MediaController::class, 'index'])->name('media.index');
    Route::post('media/{key}', [MediaController::class, 'update'])->name('media.update');

    // Modul manajemen Basis_Pengetahuan (Req 12). Nama route: admin.basis-pengetahuan.*
    Route::resource('basis-pengetahuan', KnowledgeBaseController::class)
        ->except(['show'])
        ->parameters(['basis-pengetahuan' => 'basis_pengetahuan']);

    // Modul manajemen Data_Latih (Req 14). Nama route: admin.data-latih.*
    Route::resource('data-latih', TrainingDataController::class)
        ->except(['show'])
        ->parameters(['data-latih' => 'dataLatih']);

    // Modul manajemen Hasil_Prediksi (Req 15). Nama route: admin.hasil-prediksi.*
    Route::resource('hasil-prediksi', PredictionResultController::class)
        ->only(['index', 'show', 'destroy'])
        ->parameters(['hasil-prediksi' => 'hasilPrediksi']);
});

require __DIR__.'/settings.php';
