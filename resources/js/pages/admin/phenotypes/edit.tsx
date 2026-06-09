import { Head, Link, useForm } from '@inertiajs/react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { BreadcrumbItem } from '@/types';

interface PhenotypeEntry {
    id: number;
    category: string;
    value: string;
    illustration_url: string | null;
    illustration_type: 'image' | 'gif' | 'video' | null;
}

interface PhenotypeEditProps {
    phenotype: PhenotypeEntry;
    categories: string[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Data Fenotipe', href: '/admin/fenotipe' },
    { title: 'Ubah', href: '#' },
];

export default function PhenotypeEdit({
    phenotype,
    categories,
}: PhenotypeEditProps) {
    const { data, setData, transform, post, processing, errors } = useForm<{
        category: string;
        value: string;
        illustration: File | null;
        remove_illustration: boolean;
    }>({
        category: phenotype.category,
        value: phenotype.value,
        illustration: null,
        remove_illustration: false,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        transform((current) => ({ ...current, _method: 'put' }));
        post(`/admin/fenotipe/${phenotype.id}`, { forceFormData: true });
    };

    return (
        <>
            <Head title="Ubah Fenotipe" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">
                        Ubah Fenotipe
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Perbarui kategori dan nilai fenotipe.
                    </p>
                </div>

                <form onSubmit={submit} className="max-w-xl space-y-6">
                    <div className="grid gap-2">
                        <Label htmlFor="category">Kategori</Label>
                        <select
                            id="category"
                            name="category"
                            value={data.category}
                            onChange={(e) => setData('category', e.target.value)}
                            className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-base shadow-xs transition-colors focus-visible:border-ring focus-visible:ring-2 focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm"
                        >
                            <option value="">Pilih kategori</option>
                            {categories.map((category) => (
                                <option key={category} value={category}>
                                    {category}
                                </option>
                            ))}
                        </select>
                        <InputError message={errors.category} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="value">Nilai</Label>
                        <Input
                            id="value"
                            name="value"
                            value={data.value}
                            onChange={(e) => setData('value', e.target.value)}
                            placeholder="contoh: A, B, AB, O"
                        />
                        <InputError message={errors.value} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="illustration">Ilustrasi (gambar/video, opsional)</Label>
                        {phenotype.illustration_url ? (
                            <div className="flex items-center gap-3">
                                {phenotype.illustration_type === 'video' ? (
                                    <video
                                        src={phenotype.illustration_url}
                                        muted
                                        loop
                                        autoPlay
                                        playsInline
                                        className="h-16 w-16 rounded-md border object-cover"
                                    />
                                ) : (
                                    <img
                                        src={phenotype.illustration_url}
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
                            <Link href="/admin/fenotipe">Batal</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

PhenotypeEdit.layout = {
    breadcrumbs,
};
