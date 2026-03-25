import { Link } from '@inertiajs/react';
import { index as productsIndex } from '@/actions/App/Http/Controllers/ProductController';
import type { PropsWithChildren } from 'react';

export default function StorefrontLayout({ children }: PropsWithChildren) {
    return (
        <div className="flex min-h-screen flex-col bg-gray-50 dark:bg-neutral-900">
            <header className="sticky top-0 z-10 border-b border-gray-200 bg-white shadow-sm dark:border-neutral-800 dark:bg-neutral-950">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                    <Link
                        href="/"
                        className="text-xl font-bold text-gray-900 dark:text-white"
                    >
                        ストア
                    </Link>
                    <nav className="flex items-center gap-6">
                        <Link
                            href={productsIndex.url()}
                            className="text-sm font-medium text-gray-700 transition-colors hover:text-gray-900 dark:text-neutral-300 dark:hover:text-white"
                        >
                            商品一覧
                        </Link>
                    </nav>
                </div>
            </header>
            <main className="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-6 lg:px-8">
                {children}
            </main>
            <footer className="border-t border-gray-200 bg-white dark:border-neutral-800 dark:bg-neutral-950">
                <div className="mx-auto max-w-7xl px-4 py-6 text-center text-sm text-gray-500 sm:px-6 lg:px-8">
                    © {new Date().getFullYear()} ストア
                </div>
            </footer>
        </div>
    );
}
