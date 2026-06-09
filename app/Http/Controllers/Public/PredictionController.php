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


class PredictionController extends Controller
{
   
    public function create(Request $request): Response
    {
        $screeningResultId = $request->session()->get(EnsureScreeningCompleted::SESSION_KEY);

        /** @var ScreeningResult $screening */
        $screening = ScreeningResult::query()->findOrFail($screeningResultId);

        return Inertia::render('public/prediction/form', [
            
            'phenotypeOptions' => fn (): array => $this->phenotypeOptions(),
            'phenotypeIllustrations' => fn (): array => $this->phenotypeIllustrations(),
            'screening' => [
                'father_name' => $screening->father_name,
                'mother_name' => $screening->mother_name,
                'father_result' => $screening->father_result->value,
                'mother_result' => $screening->mother_result->value,
            ],
        ]);
    }

    
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
            
            return back()->with('error', 'Prediksi belum dapat dilakukan karena data latih belum tersedia.');
        } catch (InvalidAttributeException $exception) {
            
            return back()
                ->withErrors(['prediksi' => $exception->getMessage()])
                ->withInput();
        }

        
        $prediction = PredictionResult::query()->create([
            'screening_result_id' => $screening->id,
            'physical_result' => $outcome->physical,
            'thalassemia_risk' => $outcome->thalassemiaRisk,
            'probabilities' => $outcome->probabilities,
        ]);

        return Inertia::render('public/prediction/result', [
            'predictionId' => $prediction->id,
            
            'physical' => $outcome->physical,
            
            'thalassemiaRisk' => $outcome->thalassemiaRisk->value,
            
            'probabilities' => $outcome->probabilities,
            
            'screening' => [
                'father_name' => $screening->father_name,
                'mother_name' => $screening->mother_name,
                'father_result' => $screening->father_result->value,
                'mother_result' => $screening->mother_result->value,
            ],
            
            'education' => $this->educationalContent(),
            
            'disclaimer' => $this->disclaimer(),
        ]);
    }

    public function print(PredictionResult $predictionResult): Response
    {
        $predictionResult->load('screeningResult');

        $screening = $predictionResult->screeningResult;

        return Inertia::render('public/prediction/print', [
            
            'physical' => $predictionResult->physical_result,
            
            'thalassemiaRisk' => $predictionResult->thalassemia_risk->value,
            
            'probabilities' => $predictionResult->probabilities,
           
            'screening' => [
                'father_name' => $screening->father_name,
                'mother_name' => $screening->mother_name,
                'father_result' => $screening->father_result->value,
                'mother_result' => $screening->mother_result->value,
            ],
            
            'education' => $this->educationalContent(),
           
            'disclaimer' => $this->disclaimer(),
        ]);
    }

  
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

    
    private function educationalContent(): array
    {
        return [
            'result_explanation' => 'Hasil prediksi diperoleh dengan metode Naive Bayes berdasarkan data latih yang tersedia. Nilai probabilitas menunjukkan tingkat keyakinan model terhadap setiap kemungkinan karakteristik, bukan kepastian.',
            'thalassemia_info' => 'Thalassemia adalah kelainan darah genetik yang diturunkan dari orang tua kepada anak. Orang tua yang berstatus pembawa sifat (carrier) umumnya tidak bergejala, namun tetap dapat menurunkan risiko kepada bayi. Memahami status risiko membantu perencanaan kehamilan yang lebih siap.',
            'follow_up_advice' => 'Untuk memastikan kondisi sebenarnya, lakukan pemeriksaan laboratorium seperti hitung darah lengkap dan analisis hemoglobin, serta konsultasikan hasil ini dengan dokter atau konselor genetik sebelum mengambil keputusan.',
        ];
    }

   
    private function disclaimer(): string
    {
        return 'Hasil ini bersifat skrining dan edukasi awal, bukan diagnosis medis, dan tidak menggantikan pemeriksaan laboratorium maupun konsultasi tenaga kesehatan.';
    }

  
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

    /**
     * Ilustrasi IMK per nilai fenotipe: map kategori => nilai => {url, type}.
     * Hanya memuat nilai yang memiliki ilustrasi.
     *
     * @return array<string, array<string, array{url:string, type:?string}>>
     */
    private function phenotypeIllustrations(): array
    {
        $illustrations = [];

        foreach (Phenotype::query()->get(['category', 'value', 'illustration_path']) as $phenotype) {
            if (! $phenotype->illustration_url) {
                continue;
            }

            $category = $phenotype->category instanceof PhenotypeCategory
                ? $phenotype->category->value
                : (string) $phenotype->category;

            $illustrations[$category][$phenotype->value] = [
                'url' => $phenotype->illustration_url,
                'type' => $phenotype->illustration_type,
            ];
        }

        return $illustrations;
    }
}
