import { Form, Head } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface AboutContent {
    title: string;
    content: string;
    image_url: string | null;
}

interface AboutEditProps {
    about: AboutContent;
}

export default function AboutEdit({ about }: AboutEditProps) {
    return (
        <>
            <Head title="Kelola Halaman Tentang" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                <Heading
                    title="Halaman Tentang"
                    description="Perbarui judul dan konten yang tampil pada halaman Tentang publik."
                />

                <Form
                    action="/admin/tentang"
                    method="put"
                    options={{ preserveScroll: true }}
                    className="max-w-2xl space-y-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="title">Judul</Label>

                                <Input
                                    id="title"
                                    name="title"
                                    className="mt-1 block w-full"
                                    defaultValue={about.title}
                                    required
                                    placeholder="Judul halaman Tentang"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.title}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="content">Konten</Label>

                                <textarea
                                    id="content"
                                    name="content"
                                    rows={12}
                                    className="mt-1 block w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:cursor-not-allowed disabled:opacity-50"
                                    defaultValue={about.content}
                                    required
                                    placeholder="Tuliskan konten halaman Tentang di sini."
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.content}
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="image">Gambar (opsional)</Label>

                                {about.image_url && (
                                    <img
                                        src={about.image_url}
                                        alt={about.title}
                                        className="mb-2 max-h-48 w-auto rounded-md border"
                                    />
                                )}

                                <Input
                                    id="image"
                                    name="image"
                                    type="file"
                                    accept="image/*"
                                    className="mt-1 block w-full"
                                />

                                <InputError
                                    className="mt-2"
                                    message={errors.image}
                                />
                            </div>

                            <div className="flex items-center gap-4">
                                <Button
                                    disabled={processing}
                                    data-test="save-about-button"
                                >
                                    Simpan
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

AboutEdit.layout = {
    breadcrumbs: [
        {
            title: 'Halaman Tentang',
            href: '/admin/tentang',
        },
    ],
};
