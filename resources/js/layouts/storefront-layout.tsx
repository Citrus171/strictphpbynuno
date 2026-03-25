import { index as cartIndex } from '@/actions/App/Http/Controllers/CartController';
import { index as productsIndex } from '@/actions/App/Http/Controllers/ProductController';
import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';

export default function StorefrontLayout({ children }: PropsWithChildren) {
    const { cartItemCount } = usePage<{ cartItemCount: number }>().props;

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
                        <Link
                            href={cartIndex.url()}
                            className="relative text-sm font-medium text-gray-700 transition-colors hover:text-gray-900 dark:text-neutral-300 dark:hover:text-white"
                        >
                            カート
                            {cartItemCount > 0 && (
                                <span className="absolute -top-2 -right-3 flex h-5 w-5 items-center justify-center rounded-full bg-gray-900 text-xs font-bold text-white dark:bg-white dark:text-neutral-900">
                                    {cartItemCount}
                                </span>
                            )}
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
