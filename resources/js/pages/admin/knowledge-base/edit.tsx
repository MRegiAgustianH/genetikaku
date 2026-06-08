import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface KnowledgeBaseRule {
    id: number;
    indicator: string;
    weight: number;
    classification_mapping: string;
}

interface KnowledgeBaseEditProps {
    rule: KnowledgeBaseRule;
    classificationOptions: string[];
}

export default function KnowledgeBaseEdit({ rule, classificationOptions }: KnowledgeBaseEditProps) {
    const { data, setData, put, processing, errors } = useForm({
        indicator: rule.indicator,
        weight: rule.weight,
        classification_mapping: rule.classification_mapping,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(`/admin/basis-pengetahuan/${rule.id}`);
    };

    return (
        <>
            <Head title="Ubah Aturan Basis Pengetahuan" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div>
                    <h1 className="text-2xl font-semibold">Ubah Aturan</h1>
                    <p className="text-muted-foreground">
                        Perbarui aturan indikator skrining pada basis pengetahuan.
                    </p>
                </div>

                <form onSubmit={submit} className="max-w-xl space-y-6">
                    <div className="space-y-2">
                        <label htmlFor="indicator" className="block text-sm font-medium">
                            Indikator
                        </label>
                        <input
                            id="indicator"
                            type="text"
                            value={data.indicator}
                            onChange={(e) => setData('indicator', e.target.value)}
                            className="w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:outline-none"
                            autoFocus
                        />
                        {errors.indicator && (
                            <p className="text-sm text-red-600 dark:text-red-400">{errors.indicator}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <label htmlFor="weight" className="block text-sm font-medium">
                            Bobot
                        </label>
                        <input
                            id="weight"
                            type="number"
                            min={0}
                            value={data.weight}
                            onChange={(e) => setData('weight', Number(e.target.value))}
                            className="w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:outline-none"
                        />
                        {errors.weight && (
                            <p className="text-sm text-red-600 dark:text-red-400">{errors.weight}</p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <label htmlFor="classification_mapping" className="block text-sm font-medium">
                            Pemetaan Klasifikasi
                        </label>
                        <select
                            id="classification_mapping"
                            value={data.classification_mapping}
                            onChange={(e) => setData('classification_mapping', e.target.value)}
                            className="w-full rounded-md border px-3 py-2 text-sm focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:outline-none"
                        >
                            {classificationOptions.map((option) => (
                                <option key={option} value={option}>
                                    {option}
                                </option>
                            ))}
                        </select>
                        {errors.classification_mapping && (
                            <p className="text-sm text-red-600 dark:text-red-400">
                                {errors.classification_mapping}
                            </p>
                        )}
                    </div>

                    <div className="flex items-center gap-3">
                        <button
                            type="submit"
                            disabled={processing}
                            className="inline-flex min-h-11 items-center rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-purple-700 focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2 focus-visible:outline-none disabled:opacity-50"
                        >
                            Perbarui
                        </button>
                        <Link
                            href="/admin/basis-pengetahuan"
                            className="inline-flex min-h-11 items-center rounded-md border px-4 py-2 text-sm font-medium transition-colors hover:bg-muted focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:outline-none"
                        >
                            Batal
                        </Link>
                    </div>
                </form>
            </div>
        </>
    );
}
