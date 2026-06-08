import { Head } from '@inertiajs/react';

export default function AdminDashboard() {
    return (
        <>
            <Head title="Dasbor Admin" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1 className="text-2xl font-semibold">Dasbor Admin</h1>
                <p className="text-muted-foreground">
                    Area administrasi GENETIKAKU. Modul manajemen konten dan data akan tersedia di sini.
                </p>
            </div>
        </>
    );
}
