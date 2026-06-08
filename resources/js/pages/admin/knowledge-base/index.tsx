import { Head, Link, router } from '@inertiajs/react';

interface KnowledgeBaseRule {
    id: number;
    indicator: string;
    weight: number;
    classification_mapping: string;
}

interface KnowledgeBaseIndexProps {
    rules: KnowledgeBaseRule[];
}

export default function KnowledgeBaseIndex({ rules }: KnowledgeBaseIndexProps) {
    const handleDelete = (rule: KnowledgeBaseRule) => {
        if (
            window.confirm(
                `Hapus aturan "${rule.indicator}"? Tindakan ini tidak dapat dibatalkan.`,
            )
        ) {
            router.delete(`/admin/basis-pengetahuan/${rule.id}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <>
            <Head title="Basis Pengetahuan" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-semibold">Basis Pengetahuan</h1>
                        <p className="text-muted-foreground">
                            Aturan indikator skrining Thalassemia beserta bobot dan pemetaan klasifikasinya.
                        </p>
                    </div>
                    <Link
                        href="/admin/basis-pengetahuan/create"
                        className="inline-flex min-h-11 items-center rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-purple-700 focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:ring-offset-2 focus-visible:outline-none"
                    >
                        Tambah Aturan
                    </Link>
                </div>

                {rules.length === 0 ? (
                    <div className="rounded-lg border border-dashed p-10 text-center text-muted-foreground">
                        Belum ada aturan basis pengetahuan. Tambahkan aturan pertama Anda.
                    </div>
                ) : (
                    <div className="overflow-x-auto rounded-lg border border-sidebar-border/70 dark:border-sidebar-border">
                        <table className="w-full text-left text-sm">
                            <caption className="sr-only">
                                Daftar aturan basis pengetahuan skrining Thalassemia
                            </caption>
                            <thead className="border-b border-sidebar-border/70 bg-muted/40 dark:border-sidebar-border">
                                <tr>
                                    <th scope="col" className="px-4 py-3 font-medium">Indikator</th>
                                    <th scope="col" className="px-4 py-3 font-medium">Bobot</th>
                                    <th scope="col" className="px-4 py-3 font-medium">Pemetaan Klasifikasi</th>
                                    <th scope="col" className="px-4 py-3 text-right font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rules.map((rule) => (
                                    <tr
                                        key={rule.id}
                                        className="border-b border-sidebar-border/40 last:border-0"
                                    >
                                        <td className="px-4 py-3">{rule.indicator}</td>
                                        <td className="px-4 py-3 font-mono">{rule.weight}</td>
                                        <td className="px-4 py-3">{rule.classification_mapping}</td>
                                        <td className="px-4 py-3">
                                            <div className="flex justify-end gap-2">
                                                <Link
                                                    href={`/admin/basis-pengetahuan/${rule.id}/edit`}
                                                    className="inline-flex min-h-11 items-center rounded-md border px-3 py-1.5 text-xs font-medium transition-colors hover:bg-muted focus-visible:ring-2 focus-visible:ring-purple-500 focus-visible:outline-none"
                                                >
                                                    Ubah
                                                </Link>
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(rule)}
                                                    className="inline-flex min-h-11 items-center rounded-md border border-red-300 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50 focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:outline-none dark:border-red-800 dark:text-red-400 dark:hover:bg-red-950"
                                                >
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </>
    );
}
