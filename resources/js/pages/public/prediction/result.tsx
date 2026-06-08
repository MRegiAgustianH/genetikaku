import { Head, Link } from '@inertiajs/react';

import PublicLayout from '@/layouts/public-layout';
import { cn } from '@/lib/utils';

/** Hasil_Skrining Tahap 1 terkait (konteks). */
interface ScreeningSummary {
    father_name: string;
    mother_name: string;
    father_result: string;
    mother_result: string;
}

/** Konten edukasi yang menyertai hasil (Req 4.4). */
interface EducationContent {
    result_explanation: string;
    thalassemia_info: string;
    follow_up_advice: string;
}

interface PredictionResultProps {
    predictionId: number;
    /** Karakteristik fisik bayi: label kategori => nilai prediksi (Req 4.1). */
    physical: Record<string, string>;
    /** Risiko_Thalassemia_Bayi: Rendah | Sedang | Tinggi (Req 4.2). */
    thalassemiaRisk: string;
    /** Probabilitas posterior per variabel keluaran => kelas => nilai (Req 4.3). */
    probabilities: Record<string, Record<string, number>>;
    screening: ScreeningSummary;
    education: EducationContent;
    disclaimer: string;
}

/** Label ramah-baca untuk variabel keluaran pada blok probabilitas. */
const VARIABLE_LABELS: Record<string, string> = {
    baby_blood: 'Golongan Darah',
    baby_iris: 'Warna Iris Mata',
    baby_hair: 'Tekstur Rambut',
    baby_ear: 'Bentuk Cuping Telinga',
    baby_thalassemia_risk: 'Risiko Thalassemia',
};

const variableLabel = (key: string): string =>
    VARIABLE_LABELS[key] ?? key.replace(/^baby_/, '').replace(/_/g, ' ');

const toPercent = (value: number): number => Math.round(value * 1000) / 10;

/** Warna badge berdasarkan klasifikasi risiko Thalassemia. */
const riskStyles = (risk: string): string => {
    switch (risk) {
        case 'Tinggi':
            return 'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-200';
        case 'Sedang':
            return 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-200';
        default:
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-200';
    }
};

/**
 * Halaman hasil prediksi Tahap 4 (Req 4.1–4.5, 5.1): karakteristik fisik bayi,
 * Risiko_Thalassemia_Bayi, probabilitas posterior, konten edukasi, disclaimer,
 * dan aksi cetak menuju /prediksi/{id}/cetak (route `prediksi.print`).
 */
export default function PredictionResultPage({
    predictionId,
    physical,
    thalassemiaRisk,
    probabilities,
    screening,
    education,
    disclaimer,
}: PredictionResultProps) {
    return (
        <PublicLayout footer={<p>{disclaimer}</p>}>
            <Head title="Hasil Prediksi" />

            <section className="mx-auto max-w-3xl">
                <header className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-slate-800 dark:text-neutral-50">
                            Hasil Prediksi Karakteristik Bayi
                        </h1>
                        <p className="mt-2 text-base text-slate-600 dark:text-neutral-400">
                            Berdasarkan fenotipe {screening.father_name} dan {screening.mother_name}.
                        </p>
                    </div>
                    {/* Aksi cetak (Req 5.1) menuju tampilan cetak route `prediksi.print`. */}
                    <Link
                        href={`/prediksi/${predictionId}/cetak`}
                        className={cn(
                            'inline-flex min-h-11 items-center justify-center rounded-full border border-rose-300 px-5 py-2.5',
                            'text-sm font-semibold text-rose-600 transition-colors hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300 dark:hover:bg-rose-950/40',
                            'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500',
                        )}
                    >
                        Cetak Hasil
                    </Link>
                </header>

                {/* Karakteristik fisik bayi (Req 4.1). */}
                <section aria-labelledby="physical-heading" className="mt-8">
                    <h2
                        id="physical-heading"
                        className="text-lg font-semibold text-rose-600 dark:text-rose-300"
                    >
                        Karakteristik Fisik Bayi
                    </h2>
                    <dl className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {Object.entries(physical).map(([category, value]) => (
                            <div
                                key={category}
                                className="rounded-lg border border-neutral-200 p-4 dark:border-neutral-800"
                            >
                                <dt className="text-sm text-neutral-500 dark:text-neutral-400">{category}</dt>
                                <dd className="mt-1 text-base font-semibold text-neutral-900 dark:text-neutral-100">
                                    {value}
                                </dd>
                            </div>
                        ))}
                    </dl>
                </section>

                {/* Risiko Thalassemia bayi (Req 4.2). */}
                <section aria-labelledby="risk-heading" className="mt-8">
                    <h2 id="risk-heading" className="text-lg font-semibold text-rose-600 dark:text-rose-300">
                        Risiko Thalassemia Bayi
                    </h2>
                    <p className="mt-3">
                        <span
                            className={cn(
                                'inline-flex items-center rounded-full px-4 py-1.5 text-base font-semibold',
                                riskStyles(thalassemiaRisk),
                            )}
                        >
                            {thalassemiaRisk}
                        </span>
                    </p>
                </section>

                {/* Probabilitas posterior per variabel keluaran (Req 4.3). */}
                <section aria-labelledby="prob-heading" className="mt-8">
                    <h2 id="prob-heading" className="text-lg font-semibold text-rose-600 dark:text-rose-300">
                        Probabilitas Prediksi
                    </h2>
                    <p className="mt-1 text-sm text-slate-600 dark:text-neutral-400">
                        Nilai berikut menunjukkan tingkat keyakinan model terhadap setiap kemungkinan.
                    </p>
                    <div className="mt-4 space-y-6">
                        {Object.entries(probabilities).map(([variable, classes]) => (
                            <div key={variable}>
                                <h3 className="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                                    {variableLabel(variable)}
                                </h3>
                                <ul className="mt-2 space-y-2">
                                    {Object.entries(classes)
                                        .sort(([, a], [, b]) => b - a)
                                        .map(([className, probability]) => {
                                            const percent = toPercent(probability);

                                            return (
                                                <li key={className}>
                                                    <div className="flex items-center justify-between text-sm">
                                                        <span className="text-neutral-700 dark:text-neutral-300">
                                                            {className}
                                                        </span>
                                                        <span className="font-medium tabular-nums text-neutral-900 dark:text-neutral-100">
                                                            {percent}%
                                                        </span>
                                                    </div>
                                                    <div
                                                        role="meter"
                                                        aria-valuenow={percent}
                                                        aria-valuemin={0}
                                                        aria-valuemax={100}
                                                        aria-label={`${variableLabel(variable)}: ${className} ${percent} persen`}
                                                        className="mt-1 h-2 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-800"
                                                    >
                                                        <div
                                                            className="h-full rounded-full bg-rose-400"
                                                            style={{ width: `${percent}%` }}
                                                        />
                                                    </div>
                                                </li>
                                            );
                                        })}
                                </ul>
                            </div>
                        ))}
                    </div>
                </section>

                {/* Konten edukasi (Req 4.4). */}
                <section aria-labelledby="education-heading" className="mt-8">
                    <h2 id="education-heading" className="text-lg font-semibold text-rose-600 dark:text-rose-300">
                        Edukasi
                    </h2>
                    <div className="mt-4 space-y-4 text-sm leading-relaxed text-neutral-700 dark:text-neutral-300">
                        <div>
                            <h3 className="font-semibold text-neutral-900 dark:text-neutral-100">
                                Penjelasan Hasil
                            </h3>
                            <p className="mt-1">{education.result_explanation}</p>
                        </div>
                        <div>
                            <h3 className="font-semibold text-neutral-900 dark:text-neutral-100">
                                Tentang Thalassemia
                            </h3>
                            <p className="mt-1">{education.thalassemia_info}</p>
                        </div>
                        <div>
                            <h3 className="font-semibold text-neutral-900 dark:text-neutral-100">
                                Saran Pemeriksaan Lanjutan
                            </h3>
                            <p className="mt-1">{education.follow_up_advice}</p>
                        </div>
                    </div>
                </section>

                {/* Pernyataan penyangkalan (Req 4.5). */}
                <section
                    aria-label="Pernyataan penyangkalan"
                    className="mt-8 rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200"
                >
                    <p>{disclaimer}</p>
                </section>
            </section>
        </PublicLayout>
    );
}
