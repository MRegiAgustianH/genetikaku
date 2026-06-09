import { Head, Link, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

interface CreateProps {
    indicatorSuggestions: string[];
    classificationOptions: string[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Basis Pengetahuan', href: '/admin/basis-pengetahuan' },
    { title: 'Tambah', href: '/admin/basis-pengetahuan/create' },
];

const selectClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-colors focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm';

export default function KnowledgeBaseCreate({
    indicatorSuggestions,
    classificationOptions,
}: CreateProps) {
    const { data, setData, post, processing, errors } = useForm<{
        indicator: string;
        weight: number;
        classification_mapping: string;
        illustration: File | null;
    }>({
        indicator: '',
        weight: 1,
        classification_mapping: '',
        illustration: null,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        post('/admin/basis-pengetahuan', { forceFormData: true });
    };

    return (
        <>
            <Head title="Tambah Aturan" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Tambah Aturan</h1>
                    <p className="text-sm text-muted-foreground">
                        Tentukan bobot dan kategori untuk sebuah indikator skrining. Anda dapat
                        menambahkan indikator/ciri baru di luar daftar bawaan.
                    </p>
                </div>

                <form onSubmit={submit} className="max-w-xl space-y-6">
                    <div className="grid gap-2">
                        <Label htmlFor="indicator">Indikator / Ciri</Label>
                        <Input
                            id="indicator"
                            list="indicator-suggestions"
                            value={data.indicator}
                            onChange={(e) => setData('indicator', e.target.value)}
                            placeholder="contoh: Riwayat anemia"
                        />
                        <datalist id="indicator-suggestions">
                            {indicatorSuggestions.map((indicator) => (
                                <option key={indicator} value={indicator} />
                            ))}
                        </datalist>
                        <InputError message={errors.indicator} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="weight">Bobot</Label>
                        <Input
                            id="weight"
                            type="number"
                            min={0}
                            value={data.weight}
                            onChange={(e) => setData('weight', Number(e.target.value))}
                        />
                        <InputError message={errors.weight} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="classification_mapping">Pemetaan Kategori</Label>
                        <select
                            id="classification_mapping"
                            value={data.classification_mapping}
                            onChange={(e) => setData('classification_mapping', e.target.value)}
                            className={selectClass}
                        >
                            <option value="">Pilih kategori</option>
                            {classificationOptions.map((option) => (
                                <option key={option} value={option}>
                                    {option}
                                </option>
                            ))}
                        </select>
                        <InputError message={errors.classification_mapping} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="illustration">Ilustrasi (gambar/video, opsional)</Label>
                        <Input
                            id="illustration"
                            type="file"
                            accept="image/*,video/mp4,video/webm"
                            onChange={(e) => setData('illustration', e.target.files?.[0] ?? null)}
                        />
                        <p className="text-xs text-muted-foreground">
                            Ditampilkan di samping indikator pada form skrining. Maks. 20 MB.
                        </p>
                        <InputError message={errors.illustration} />
                    </div>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            Simpan
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href="/admin/basis-pengetahuan">Batal</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

KnowledgeBaseCreate.layout = {
    breadcrumbs,
};
