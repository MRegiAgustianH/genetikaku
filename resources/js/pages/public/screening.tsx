import { Head, useForm } from '@inertiajs/react';
import { motion } from 'motion/react';
import { Dna, Stethoscope } from 'lucide-react';
import type { FormEvent } from 'react';

import InputError from '@/components/input-error';
import PublicLayout from '@/layouts/public-layout';
import { cn } from '@/lib/utils';

interface Indicator {
    key: string;
    label: string;
}

interface Illustration {
    url: string;
    type: 'image' | 'video' | 'gif';
}

interface ScreeningProps {
    /** Daftar Indikator_Skrining {key,label} dari ScreeningController (Req 1.1). */
    indicators: Indicator[];
    /** Ilustrasi IMK opsional yang ditampilkan di samping/atas formulir. */
    illustration: Illustration | null;
}

type ParentKey = 'father' | 'mother';

type IndicatorAnswers = Record<string, boolean>;

interface ScreeningForm {
    father_name: string;
    mother_name: string;
    father: IndicatorAnswers;
    mother: IndicatorAnswers;
    [key: string]: string | IndicatorAnswers;
}

const PARENTS: { key: ParentKey; label: string; nameField: 'father_name' | 'mother_name' }[] = [
    { key: 'father', label: 'Ayah', nameField: 'father_name' },
    { key: 'mother', label: 'Ibu', nameField: 'mother_name' },
];

function buildAnswers(indicators: Indicator[]): IndicatorAnswers {
    return indicators.reduce<IndicatorAnswers>((acc, indicator) => {
        acc[indicator.key] = false;
        return acc;
    }, {});
}

/**
 * Renders the optional IMK illustration. Uses a muted autoplay loop <video> for
 * the 'video' type, otherwise an <img> (covers 'image' and 'gif'). When no
 * illustration is provided, shows a tasteful gradient placeholder (not an error).
 */
function IllustrationPanel({ illustration }: { illustration: Illustration | null }) {
    if (illustration) {
        return (
            <div className="overflow-hidden rounded-2xl border border-rose-100 bg-rose-50 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                {illustration.type === 'video' ? (
                    <video
                        src={illustration.url}
                        autoPlay
                        muted
                        loop
                        playsInline
                        aria-hidden="true"
                        className="h-full w-full object-cover"
                    />
                ) : (
                    <img
                        src={illustration.url}
                        alt=""
                        aria-hidden="true"
                        className="h-full w-full object-cover"
                    />
                )}
            </div>
        );
    }

    return (
        <div className="flex min-h-56 flex-col items-center justify-center rounded-2xl border border-dashed border-rose-200 bg-gradient-to-br from-rose-50 to-violet-50 p-8 text-center dark:border-neutral-800 dark:from-neutral-900 dark:to-neutral-900">
            <span className="flex h-16 w-16 items-center justify-center rounded-full bg-rose-100 text-rose-500 shadow-sm dark:bg-rose-950/40 dark:text-rose-300">
                <Dna className="h-8 w-8" aria-hidden="true" />
            </span>
            <p className="mt-4 text-sm font-medium text-slate-600 dark:text-neutral-300">
                Ilustrasi indikator prediksi
            </p>
            <p className="mt-1 text-xs text-slate-500 dark:text-neutral-500">
                Visualisasi akan tampil di sini saat tersedia.
            </p>
        </div>
    );
}

/**
 * Halaman skrining Tahap 1 (Req 1.1): formulir Indikator_Skrining terpisah
 * untuk ayah dan ibu. Mengirim ke route `skrining.store` (POST /skrining) dan
 * menampilkan kesalahan validasi per field (father_name, father.<key>, dst).
 */
export default function Screening({ indicators, illustration }: ScreeningProps) {
    const { data, setData, post, processing, errors } = useForm<ScreeningForm>({
        father_name: '',
        mother_name: '',
        father: buildAnswers(indicators),
        mother: buildAnswers(indicators),
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post('/skrining');
    };

    const setIndicator = (parent: ParentKey, key: string, value: boolean) => {
        setData(parent, { ...data[parent], [key]: value });
    };

    return (
        <PublicLayout>
            <Head title="Prediksi Thalassemia" />

            <div className="mx-auto w-full max-w-5xl px-6 py-10">
                <motion.header
                    initial={{ opacity: 0, y: 16 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.5 }}
                >
                    <span className="inline-flex items-center gap-2 rounded-full bg-rose-50 px-3 py-1 text-sm font-medium text-rose-600 dark:bg-rose-950/40 dark:text-rose-300">
                        <Stethoscope className="h-4 w-4" aria-hidden="true" /> Tahap 1 — Skrining
                    </span>
                    <h1 className="mt-3 text-4xl font-bold tracking-tight text-slate-800 dark:text-neutral-50">
                        Prediksi Risiko Thalassemia
                    </h1>
                    <p className="mt-2 max-w-2xl text-base text-slate-600 dark:text-neutral-400">
                        Isi indikator skrining untuk ayah dan ibu. Seluruh indikator wajib dijawab
                        sebelum melanjutkan ke tahap prediksi.
                    </p>
                </motion.header>

                <div className="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
                    <form onSubmit={submit} className="order-2 space-y-8 lg:order-1" noValidate>
                        {PARENTS.map((parent, parentIndex) => (
                            <motion.fieldset
                                key={parent.key}
                                initial={{ opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.45, delay: 0.1 + parentIndex * 0.1 }}
                                className="rounded-2xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900"
                            >
                                <legend className="px-1 text-lg font-semibold text-rose-600 dark:text-rose-300">
                                    Data {parent.label}
                                </legend>

                                <div className="mt-2">
                                    <label
                                        htmlFor={parent.nameField}
                                        className="block text-sm font-medium text-slate-700 dark:text-neutral-200"
                                    >
                                        Nama {parent.label}
                                    </label>
                                    <input
                                        id={parent.nameField}
                                        name={parent.nameField}
                                        type="text"
                                        value={data[parent.nameField]}
                                        onChange={(event) => setData(parent.nameField, event.target.value)}
                                        autoComplete="name"
                                        aria-invalid={errors[parent.nameField] ? true : undefined}
                                        aria-describedby={
                                            errors[parent.nameField] ? `${parent.nameField}-error` : undefined
                                        }
                                        className={cn(
                                            'mt-1 block min-h-11 w-full rounded-md border px-3 py-2 text-sm',
                                            'text-neutral-900 dark:bg-neutral-900 dark:text-neutral-100',
                                            'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-400',
                                            errors[parent.nameField]
                                                ? 'border-red-500'
                                                : 'border-neutral-300 dark:border-neutral-700',
                                        )}
                                    />
                                    <InputError id={`${parent.nameField}-error`} message={errors[parent.nameField]} />
                                </div>

                                <fieldset className="mt-6">
                                    <legend className="text-sm font-medium text-neutral-800 dark:text-neutral-200">
                                        Indikator skrining {parent.label.toLowerCase()}
                                    </legend>
                                    <ul className="mt-3 space-y-3">
                                        {indicators.map((indicator) => {
                                            const fieldName = `${parent.key}.${indicator.key}`;
                                            const inputId = `${parent.key}-${indicator.key}`;
                                            const checked = Boolean(data[parent.key][indicator.key]);

                                            return (
                                                <li key={indicator.key}>
                                                    <label
                                                        htmlFor={inputId}
                                                        className="flex min-h-11 items-center gap-3 rounded-md px-2 text-sm text-slate-700 transition-colors hover:bg-rose-50 dark:text-neutral-200 dark:hover:bg-neutral-800"
                                                    >
                                                        <input
                                                            id={inputId}
                                                            name={fieldName}
                                                            type="checkbox"
                                                            checked={checked}
                                                            onChange={(event) =>
                                                                setIndicator(
                                                                    parent.key,
                                                                    indicator.key,
                                                                    event.target.checked,
                                                                )
                                                            }
                                                            aria-invalid={errors[fieldName] ? true : undefined}
                                                            aria-describedby={
                                                                errors[fieldName] ? `${inputId}-error` : undefined
                                                            }
                                                            className="h-5 w-5 rounded border-neutral-400 text-rose-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500 dark:border-neutral-600"
                                                        />
                                                        <span>{indicator.label}</span>
                                                    </label>
                                                    <InputError
                                                        id={`${inputId}-error`}
                                                        className="ml-8"
                                                        message={errors[fieldName]}
                                                    />
                                                </li>
                                            );
                                        })}
                                    </ul>
                                </fieldset>
                            </motion.fieldset>
                        ))}

                        <div className="flex justify-end">
                            <button
                                type="submit"
                                disabled={processing}
                                className={cn(
                                    'inline-flex min-h-11 items-center justify-center rounded-full bg-rose-500 px-8 py-3',
                                    'text-base font-semibold text-white shadow-sm transition-colors hover:bg-rose-600',
                                    'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-500',
                                    'disabled:cursor-not-allowed disabled:opacity-60 disabled:hover:bg-rose-500',
                                )}
                            >
                                {processing ? 'Memproses…' : 'Lanjut ke Prediksi'}
                            </button>
                        </div>
                    </form>

                    {/* Area ilustrasi IMK */}
                    <motion.aside
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.5, delay: 0.15 }}
                        className="order-1 lg:order-2 lg:sticky lg:top-24 lg:self-start"
                    >
                        <IllustrationPanel illustration={illustration} />
                    </motion.aside>
                </div>
            </div>
        </PublicLayout>
    );
}
