import StorefrontLayout from '@/layouts/storefront-layout';
import { index as productsIndex } from '@/actions/App/Http/Controllers/ProductController';
import { Head, Link } from '@inertiajs/react';

interface Product {
    id: number;
    name: string;
    brand: string | null;
    price: number | null;
    thumbnail: string | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

interface Props {
    products: PaginatedProducts;
}

function formatPrice(price: number | null): string {
    if (price === null) {
        return '価格未設定';
    }
    return new Intl.NumberFormat('ja-JP', {
        style: 'currency',
        currency: 'JPY',
    }).format(price);
}

export default function ProductsIndex({ products }: Props) {
    return (
        <StorefrontLayout>
            <Head title="商品一覧" />

            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">
                    商品一覧
                </h1>
                <p className="mt-1 text-sm text-gray-500 dark:text-neutral-400">
                    {products.total}件の商品
                </p>
            </div>

            {products.data.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-24 text-center">
                    <p className="text-lg text-gray-500 dark:text-neutral-400">
                        商品がありません
                    </p>
                </div>
            ) : (
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {products.data.map((product) => (
                        <div
                            key={product.id}
                            className="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-neutral-700 dark:bg-neutral-800"
                        >
                            <div className="aspect-square overflow-hidden bg-gray-100 dark:bg-neutral-700">
                                {product.thumbnail ? (
                                    <img
                                        src={product.thumbnail}
                                        alt={product.name}
                                        className="h-full w-full object-cover transition-transform group-hover:scale-105"
                                    />
                                ) : (
                                    <div className="flex h-full w-full items-center justify-center text-gray-400 dark:text-neutral-500">
                                        <svg
                                            className="h-16 w-16"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                strokeLinecap="round"
                                                strokeLinejoin="round"
                                                strokeWidth={1}
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                            />
                                        </svg>
                                    </div>
                                )}
                            </div>
                            <div className="flex flex-1 flex-col gap-2 p-4">
                                {product.brand && (
                                    <p className="text-xs font-medium uppercase tracking-wide text-gray-400 dark:text-neutral-500">
                                        {product.brand}
                                    </p>
                                )}
                                <h2 className="line-clamp-2 text-sm font-semibold text-gray-900 dark:text-white">
                                    {product.name}
                                </h2>
                                <p className="mt-auto text-base font-bold text-gray-900 dark:text-white">
                                    {formatPrice(product.price)}
                                </p>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {products.last_page > 1 && (
                <nav
                    className="mt-10 flex items-center justify-center gap-1"
                    aria-label="ページネーション"
                >
                    {products.links.map((link, i) => {
                        if (link.url === null) {
                            return (
                                <span
                                    key={i}
                                    className="inline-flex h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm text-gray-400 dark:text-neutral-600"
                                    dangerouslySetInnerHTML={{
                                        __html: link.label,
                                    }}
                                />
                            );
                        }
                        return (
                            <Link
                                key={i}
                                href={link.url}
                                className={[
                                    'inline-flex h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm transition-colors',
                                    link.active
                                        ? 'bg-gray-900 font-semibold text-white dark:bg-white dark:text-gray-900'
                                        : 'text-gray-700 hover:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-800',
                                ].join(' ')}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                preserveScroll
                            />
                        );
                    })}
                </nav>
            )}
        </StorefrontLayout>
    );
}
