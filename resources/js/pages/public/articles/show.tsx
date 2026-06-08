import { Head, Link } from '@inertiajs/react';
import { motion } from 'motion/react';
import { ArrowLeft } from 'lucide-react';

import PublicLayout from '@/layouts/public-layout';

interface ArticleDetail {
    title: string;
    content: string;
    image_url: string | null;
}

interface ArticlesShowProps {
    article: ArticleDetail;
}

export default function ArticlesShow({ article }: ArticlesShowProps) {
    return (
        <PublicLayout>
            <Head title={article.title} />

            {article.image_url ? (
                <section className="relative h-72 w-full overflow-hidden sm:h-96">
                    <img
                        src={article.image_url}
                        alt=""
                        aria-hidden="true"
                        className="absolute inset-0 h-full w-full object-cover"
                    />
                    <div
                        aria-hidden="true"
                        className="absolute inset-0 bg-gradient-to-t from-white/90 via-white/55 to-transparent dark:from-neutral-950/90 dark:via-neutral-950/55"
                    />
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.6 }}
                        className="absolute inset-x-0 bottom-0 mx-auto w-full max-w-3xl px-6 pb-8"
                    >
                        <h1 className="text-3xl font-bold tracking-tight text-slate-800 sm:text-4xl dark:text-neutral-50">
                            {article.title}
                        </h1>
                    </motion.div>
                </section>
            ) : null}

            <div className="mx-auto w-full max-w-3xl px-6 py-10">
                <Link
                    href="/artikel"
                    className="mb-6 inline-flex min-h-11 items-center gap-1.5 text-sm font-medium text-rose-600 underline-offset-4 hover:underline dark:text-rose-300"
                >
                    <ArrowLeft className="h-4 w-4" aria-hidden="true" />
                    Kembali ke daftar artikel
                </Link>

                <motion.article
                    initial={{ opacity: 0, y: 16 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.5, delay: article.image_url ? 0.15 : 0 }}
                >
                    {!article.image_url ? (
                        <h1 className="mb-6 text-4xl font-bold tracking-tight text-slate-800 dark:text-neutral-50">
                            {article.title}
                        </h1>
                    ) : null}
                    <div className="leading-relaxed whitespace-pre-wrap text-slate-600 dark:text-neutral-300">
                        {article.content}
                    </div>
                </motion.article>
            </div>
        </PublicLayout>
    );
}
