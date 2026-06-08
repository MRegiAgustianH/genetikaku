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
    const { data, setData, put, processing, errors } = useForm({
        category: phenotype.category,
        value: phenotype.value,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        put(`/admin/fenotipe/${phenotype.id}`);
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
