<?php

namespace App\Http\Controllers\Public;

use App\Domain\PhenotypeCategory;
use App\Domain\TrainingRow;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureScreeningCompleted;
use App\Http\Requests\Public\PredictionRequest;
use App\Models\Phenotype;
use App\Models\PredictionResult;
use App\Models\ScreeningResult;
use App\Models\TrainingData;
use App\Services\Exceptions\EmptyTrainingDataException;
use App\Services\Exceptions\InvalidAttributeException;
use App\Services\NaiveBayesClassifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Alur publik Tahap 2–4: Prediksi karakteristik fisik bayi (Req 2, 3, 4).
 *
 * - GET  /prediksi : tampilkan formulir input Fenotipe ayah & ibu (Tahap 2),
 *                    dengan opsi nilai dari Data_Fenotipe dan Hasil_Skrining
 *                    Tahap 1 yang sudah terisi otomatis (read-only).
 * - POST /prediksi : validasi Fenotipe (Req 2.5), jalankan Mesin_Naive_Bayes
 *                    atas masukan (fenotipe + Hasil_Skrining), simpan
 *                    Hasil_Prediksi (Req 4.6), lalu render halaman hasil
 *                    dengan edukasi (Req 4.4) & disclaimer (Req 4.5).
 *
 * Tampilan cetak ditangani pada task 7.4. Halaman React `public/prediction/form`
 * dan `public/prediction/result` dibangun pada task 7.5; di sini kita hanya
 * menyediakan props yang dibutuhkan halaman tersebut.
 *
 * Akses dijaga middleware alias `screening.completed` ({@see EnsureScreeningCompleted}):
 * tanpa `screening_result_id` sesi yang valid, pengguna dialihkan ke /skrining
 * (Req 2.4). Route GET /prediksi mendaftarkan middleware tersebut.
 */
class PredictionController extends Controller
{
    /**
     * Tampilkan formulir Fenotipe Tahap 2 (Req 2.1, 2.2, 2.3).
     *
     * Props:
     * - `phenotypeOptions` : map kategori → daftar nilai valid dari Data_Fenotipe
     *   (Req 2.2). Disediakan sebagai closure prop sehingga selalu dievaluasi
     *   ulang pada setiap render, termasuk Inertia partial reload
     *   (`router.reload({ only: ['phenotypeOptions'] })`). Hal ini membuat
     *   perubahan Data_Fenotipe oleh Admin langsung tercermin tanpa muat ulang
     *   penuh (Req 13.2 / Property 14).
     * - `screening` : Hasil_Skrining Tahap 1 (nama & hasil ayah/ibu) sebagai
     *   nilai read-only yang sudah terisi otomatis (Req 2.3). Bersifat statis
     *   untuk render ini.
     */
    public function create(Request $request): Response
    {
        $screeningResultId = $request->session()->get(EnsureScreeningCompleted::SESSION_KEY);

        /** @var ScreeningResult $screening */
        $screening = ScreeningResult::query()->findOrFail($screeningResultId);

        return Inertia::render('public/prediction/form', [
            // Closure prop: dievaluasi ulang pada initial load DAN partial reload,
            // sehingga opsi selalu mencerminkan Data_Fenotipe terkini (Req 13.2).
            'phenotypeOptions' => fn (): array => $this->phenotypeOptions(),
            'screening' => [
                'father_name' => $screening->father_name,
                'mother_name' => $screening->mother_name,
                'father_result' => $screening->father_result->value,
                'mother_result' => $screening->mother_result->value,
            ],
        ]);
    }

    /**
     * Proses pengiriman formulir Fenotipe: jalankan Mesin_Naive_Bayes, simpan
     * Hasil_Prediksi, dan render halaman hasil (Req 2.5, 3.1, 3.8, 4.1–4.6).
     *
     * Alur:
     *  1. Validasi Fenotipe wajib via {@see PredictionRequest} (Req 2.5).
     *  2. Bangun masukan klasifier: fenotipe terkirim + Hasil_Skrining_Orang_Tua
     *     (father_thalassemia/mother_thalassemia) dari Hasil_Skrining Tahap 1.
     *  3. Muat seluruh Data_Latih sebagai {@see TrainingRow} dan panggil
     *     {@see NaiveBayesClassifier::predict()}.
     *  4. Tangani kegagalan domain:
     *     - {@see EmptyTrainingDataException} (Req 3.8): redirect kembali dengan
     *       pesan error dan TIDAK menyimpan Hasil_Prediksi.
     *     - {@see InvalidAttributeException} (Req 3.1): redirect kembali dengan
     *       kesalahan validasi.
     *  5. Sukses: simpan Hasil_Prediksi (Req 4.6) lalu render halaman hasil
     *     dengan prediksi, probabilitas (Req 4.3), edukasi (Req 4.4), dan
     *     disclaimer (Req 4.5).
     */
    public function store(PredictionRequest $request, NaiveBayesClassifier $classifier): RedirectResponse|Response
    {
        $validated = $request->validated();

        $screeningResultId = $request->session()->get(EnsureScreeningCompleted::SESSION_KEY);

        /** @var ScreeningResult $screening */
        $screening = ScreeningResult::query()->findOrFail($screeningResultId);

        $input = $this->buildClassifierInput($validated, $screening);

        try {
            $outcome = $classifier->predict($input, $this->loadTrainingRows());
        } catch (EmptyTrainingDataException $exception) {
            // Req 3.8: data latih kosong — batalkan tanpa menyimpan apa pun.
            return back()->with('error', 'Prediksi belum dapat dilakukan karena data latih belum tersedia.');
        } catch (InvalidAttributeException $exception) {
            // Req 3.1: ada nilai atribut tak terdaftar — sajikan sebagai kesalahan validasi.
            return back()
                ->withErrors(['prediksi' => $exception->getMessage()])
                ->withInput();
        }

        // Req 4.6: simpan Hasil_Prediksi (referensi Hasil_Skrining, hasil fisik,
        // hasil thalassemia, probabilitas).
        $prediction = PredictionResult::query()->create([
            'screening_result_id' => $screening->id,
            'physical_result' => $outcome->physical,
            'thalassemia_risk' => $outcome->thalassemiaRisk,
            'probabilities' => $outcome->probabilities,
        ]);

        return Inertia::render('public/prediction/result', [
            'predictionId' => $prediction->id,
            // Req 4.1: karakteristik fisik bayi (map kategori => nilai).
            'physical' => $outcome->physical,
            // Req 4.2: Risiko_Thalassemia_Bayi.
            'thalassemiaRisk' => $outcome->thalassemiaRisk->value,
            // Req 4.3: probabilitas posterior per variabel keluaran.
            'probabilities' => $outcome->probabilities,
            // Hasil_Skrining terkait untuk konteks (nama & hasil ayah/ibu).
            'screening' => [
                'father_name' => $screening->father_name,
                'mother_name' => $screening->mother_name,
                'father_result' => $screening->father_result->value,
                'mother_result' => $screening->mother_result->value,
            ],
            // Req 4.4: konten edukasi (penjelasan hasil, info Thalassemia, saran lanjutan).
            'education' => $this->educationalContent(),
            // Req 4.5: pernyataan penyangkalan (disclaimer).
            'disclaimer' => $this->disclaimer(),
        ]);
    }

    /**
     * Render tampilan cetak untuk sebuah Hasil_Prediksi tersimpan (Req 5.1, 5.2).
     *
     * Route: GET /prediksi/{predictionResult}/cetak (name: `prediksi.print`).
     * Memuat Hasil_Prediksi melalui implicit route-model binding beserta
     * Hasil_Skrining terkait, lalu merender halaman cetak Inertia
     * `public/prediction/print` dengan kontrak props yang sama persis dengan
     * halaman hasil (`store()`), ditambah pemicu cetak pada sisi klien.
     *
     * Tidak dijaga middleware `screening.completed`: tampilan ini memuat sebuah
     * Hasil_Prediksi spesifik berdasarkan id-nya, sehingga sebuah hasil yang
     * sudah tersimpan tetap dapat dicetak tanpa memerlukan sesi skrining aktif.
     *
     * Props (Property 17 — seluruh bagian wajib tersedia):
     * - `physical`       : map kategori => nilai karakteristik fisik bayi (Req 5.2).
     * - `thalassemiaRisk`: Risiko_Thalassemia_Bayi (backing value enum) (Req 5.2).
     * - `probabilities`  : nilai probabilitas posterior per variabel keluaran (Req 5.2).
     * - `screening`      : konteks Hasil_Skrining (nama & hasil ayah/ibu).
     * - `education`      : konten edukasi, berbagi sumber dengan `store()` (Req 5.2).
     * - `disclaimer`     : pernyataan penyangkalan, berbagi sumber dengan `store()` (Req 5.2).
     */
    public function print(PredictionResult $predictionResult): Response
    {
        $predictionResult->load('screeningResult');

        /** @var ScreeningResult $screening */
        $screening = $predictionResult->screeningResult;

        return Inertia::render('public/prediction/print', [
            // Req 5.2: karakteristik fisik bayi (map kategori => nilai).
            'physical' => $predictionResult->physical_result,
            // Req 5.2: Risiko_Thalassemia_Bayi.
            'thalassemiaRisk' => $predictionResult->thalassemia_risk->value,
            // Req 5.2: probabilitas posterior per variabel keluaran.
            'probabilities' => $predictionResult->probabilities,
            // Konteks Hasil_Skrining terkait (nama & hasil ayah/ibu).
            'screening' => [
                'father_name' => $screening->father_name,
                'mother_name' => $screening->mother_name,
                'father_result' => $screening->father_result->value,
                'mother_result' => $screening->mother_result->value,
            ],
            // Req 5.2: konten edukasi — berbagi sumber dengan halaman hasil (store()).
            'education' => $this->educationalContent(),
            // Req 5.2: pernyataan penyangkalan — berbagi sumber dengan halaman hasil (store()).
            'disclaimer' => $this->disclaimer(),
        ]);
    }

    /**
     * Bangun map atribut masukan Mesin_Naive_Bayes dari Fenotipe terkirim
     * (father_/mother_ blood/iris/hair/ear) ditambah Hasil_Skrining_Orang_Tua
     * (father_thalassemia/mother_thalassemia) dari Hasil_Skrining Tahap 1.
     *
     * Kunci hasil sama persis dengan {@see TrainingRow::inputAttributes()} agar
     * dikenali klasifier.
     *
     * @param  array<string,string>  $validated
     * @return array<string,string>
     */
    private function buildClassifierInput(array $validated, ScreeningResult $screening): array
    {
        return [
            'father_blood' => $validated['father_blood'],
            'father_iris' => $validated['father_iris'],
            'father_hair' => $validated['father_hair'],
            'father_ear' => $validated['father_ear'],
            'father_thalassemia' => $screening->father_result->value,
            'mother_blood' => $validated['mother_blood'],
            'mother_iris' => $validated['mother_iris'],
            'mother_hair' => $validated['mother_hair'],
            'mother_ear' => $validated['mother_ear'],
            'mother_thalassemia' => $screening->mother_result->value,
        ];
    }

    /**
     * Muat seluruh Data_Latih dari DB dan petakan ke DTO {@see TrainingRow}.
     *
     * @return list<TrainingRow>
     */
    private function loadTrainingRows(): array
    {
        return TrainingData::query()
            ->get()
            ->map(fn (TrainingData $row): TrainingRow => TrainingRow::fromArray($row->toArray()))
            ->all();
    }

    /**
     * Konten edukasi yang menyertai hasil prediksi (Req 4.4): penjelasan hasil,
     * informasi Thalassemia, dan saran pemeriksaan lanjutan.
     *
     * @return array<string,string>
     */
    private function educationalContent(): array
    {
        return [
            'result_explanation' => 'Hasil prediksi diperoleh dengan metode Naive Bayes berdasarkan data latih yang tersedia. Nilai probabilitas menunjukkan tingkat keyakinan model terhadap setiap kemungkinan karakteristik, bukan kepastian.',
            'thalassemia_info' => 'Thalassemia adalah kelainan darah genetik yang diturunkan dari orang tua kepada anak. Orang tua yang berstatus pembawa sifat (carrier) umumnya tidak bergejala, namun tetap dapat menurunkan risiko kepada bayi. Memahami status risiko membantu perencanaan kehamilan yang lebih siap.',
            'follow_up_advice' => 'Untuk memastikan kondisi sebenarnya, lakukan pemeriksaan laboratorium seperti hitung darah lengkap dan analisis hemoglobin, serta konsultasikan hasil ini dengan dokter atau konselor genetik sebelum mengambil keputusan.',
        ];
    }

    /**
     * Pernyataan penyangkalan (disclaimer) untuk halaman hasil (Req 4.5):
     * hasil bersifat skrining & edukasi awal, bukan diagnosis medis.
     */
    private function disclaimer(): string
    {
        return 'Hasil ini bersifat skrining dan edukasi awal, bukan diagnosis medis, dan tidak menggantikan pemeriksaan laboratorium maupun konsultasi tenaga kesehatan.';
    }

    /**
     *
     * Setiap kategori Fenotipe yang dikenal sistem selalu hadir sebagai kunci
     * (dengan daftar nilai kosong bila Admin belum mengisi), dan nilai pada
     * tiap kategori sama persis dengan himpunan nilai yang terdaftar di tabel
     * `phenotypes` untuk kategori tersebut (Req 2.2, Property 14).
     *
     * @return array<string, list<string>>
     */
    private function phenotypeOptions(): array
    {
        $valuesByCategory = Phenotype::query()
            ->orderBy('value')
            ->get(['category', 'value'])
            ->groupBy(fn (Phenotype $phenotype): string => $phenotype->category->value)
            ->map(fn ($group): array => $group
                ->pluck('value')
                ->all());

        $options = [];

        foreach (PhenotypeCategory::cases() as $category) {
            $options[$category->value] = $valuesByCategory->get($category->value, []);
        }

        return $options;
    }
}
