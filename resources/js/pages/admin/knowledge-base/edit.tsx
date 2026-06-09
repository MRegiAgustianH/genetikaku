import { Head, Link, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

interface Rule {
    id: number;
    indicator: string;
    weight: number;
    classification_mapping: string;
    illustration_url: string | null;
    illustration_type: 'image' | 'gif' | 'video' | null;
}

interface EditProps {
    rule: Rule;
    indicatorSuggestions: string[];
    classificationOptions: string[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Basis Pengetahuan', href: '/admin/basis-pengetahuan' },
    { title: 'Ubah', href: '#' },
];

const selectClass =
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-colors focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm';

export default function KnowledgeBaseEdit({
    rule,
    indicatorSuggestions,
    classificationOptions,
}: EditProps) {
    const { data, setData, transform, post, processing, errors } = useForm<{
        indicator: string;
        weight: number;
        classification_mapping: string;
        illustration: File | null;
        remove_illustration: boolean;
    }>({
        indicator: rule.indicator,
        weight: rule.weight,
        classification_mapping: rule.classification_mapping,
        illustration: null,
        remove_illustration: false,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        transform((current) => ({ ...current, _method: 'put' }));
        post(`/admin/basis-pengetahuan/${rule.id}`, { forceFormData: true });
    };

    return (
        <>
            <Head title="Ubah Aturan" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Ubah Aturan</h1>
                    <p className="text-sm text-muted-foreground">
                        Perbarui indikator, bobot, dan kategori.
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
                        {rule.illustration_url ? (
                            <div className="flex items-center gap-3">
                                {rule.illustration_type === 'video' ? (
                                    <video
                                        src={rule.illustration_url}
                                        muted
                                        loop
                                        autoPlay
                                        playsInline
                                        className="h-16 w-16 rounded-md border object-cover"
                                    />
                                ) : (
                                    <img
                                        src={rule.illustration_url}
                                        alt=""
                                        className="h-16 w-16 rounded-md border object-cover"
                                    />
                                )}
                                <label className="flex items-center gap-2 text-sm text-muted-foreground">
                                    <input
                                        type="checkbox"
                                        checked={data.remove_illustration}
                                        onChange={(e) => setData('remove_illustration', e.target.checked)}
                                    />
                                    Hapus ilustrasi
                                </label>
                            </div>
                        ) : null}
                        <Input
                            id="illustration"
                            type="file"
                            accept="image/*,video/mp4,video/webm"
                            onChange={(e) => setData('illustration', e.target.files?.[0] ?? null)}
                        />
                        <p className="text-xs text-muted-foreground">
                            Unggah untuk mengganti ilustrasi. Maks. 20 MB.
                        </p>
                        <InputError message={errors.illustration} />
                    </div>

                    <div className="flex items-center gap-4">
                        <Button type="submit" disabled={processing}>
                            Simpan Perubahan
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

KnowledgeBaseEdit.layout = {
    breadcrumbs,
};
