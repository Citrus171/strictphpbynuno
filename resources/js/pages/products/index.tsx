import { show } from '@/actions/App/Http/Controllers/ProductController';
import StorefrontLayout from '@/layouts/storefront-layout';
import { formatPrice } from '@/lib/format-price';
import { Head, Link, router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

interface Product {
    id: number;
    slug: string | null;
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

interface BrandOption {
    id: number;
    name: string;
}

interface CollectionOption {
    id: number;
    name: string;
}

interface Filters {
    brand: number | null;
    collection: number | null;
    sort: string | null;
    search: string | null;
}

interface Props {
    products: PaginatedProducts;
    filters: Filters;
    brands: BrandOption[];
    collections: CollectionOption[];
}

const SORT_OPTIONS = [
    { value: '', label: '新着順' },
    { value: 'price_asc', label: '価格：安い順' },
    { value: 'price_desc', label: '価格：高い順' },
    { value: 'name_asc', label: '名前順' },
];

export default function ProductsIndex({ products, filters, brands, collections }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');

    const applyFilter = useCallback((params: Partial<Filters & { perPage?: number }>) => {
        router.get(
            window.location.pathname,
            Object.fromEntries(
                Object.entries({ ...filters, search, page: undefined, ...params }).filter(
                    ([, v]) => v !== null && v !== undefined && v !== '',
                ),
            ),
            { preserveScroll: true },
        );
    }, [filters, search]);

    const handleSearch = useCallback((e: React.FormEvent) => {
        e.preventDefault();
        applyFilter({ search: search || null, brand: filters.brand, collection: filters.collection, sort: filters.sort });
    }, [applyFilter, search, filters]);

    return (
        <StorefrontLayout>
            <Head title="商品一覧" />

            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-900 dark:text-white">商品一覧</h1>
                <p className="mt-1 text-sm text-gray-500 dark:text-neutral-400">{products.total}件の商品</p>
            </div>

            {/* フィルタ・ソート・検索 */}
            <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:flex-wrap">
                {/* キーワード検索 */}
                <form onSubmit={handleSearch} className="flex gap-2">
                    <input
                        type="search"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="商品名で検索..."
                        className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white dark:placeholder:text-neutral-500 dark:focus:ring-white"
                    />
                    <button
                        type="submit"
                        className="h-9 rounded-md bg-gray-900 px-4 text-sm font-medium text-white transition-colors hover:bg-gray-700 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
                    >
                        検索
                    </button>
                </form>

                {/* ブランドフィルタ */}
                {brands.length > 0 && (
                    <select
                        value={filters.brand ?? ''}
                        onChange={(e) => applyFilter({ brand: e.target.value ? Number(e.target.value) : null })}
                        className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white dark:focus:ring-white"
                    >
                        <option value="">すべてのブランド</option>
                        {brands.map((b) => (
                            <option key={b.id} value={b.id}>{b.name}</option>
                        ))}
                    </select>
                )}

                {/* コレクションフィルタ */}
                {collections.length > 0 && (
                    <select
                        value={filters.collection ?? ''}
                        onChange={(e) => applyFilter({ collection: e.target.value ? Number(e.target.value) : null })}
                        className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white dark:focus:ring-white"
                    >
                        <option value="">すべてのカテゴリ</option>
                        {collections.map((c) => (
                            <option key={c.id} value={c.id}>{c.name}</option>
                        ))}
                    </select>
                )}

                {/* ソート */}
                <select
                    value={filters.sort ?? ''}
                    onChange={(e) => applyFilter({ sort: e.target.value || null })}
                    className="h-9 rounded-md border border-gray-300 bg-white px-3 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-900 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white dark:focus:ring-white"
                >
                    {SORT_OPTIONS.map((o) => (
                        <option key={o.value} value={o.value}>{o.label}</option>
                    ))}
                </select>

                {/* フィルタリセット */}
                {(filters.brand || filters.collection || filters.sort || filters.search) && (
                    <Link
                        href={window.location.pathname}
                        className="h-9 inline-flex items-center rounded-md border border-gray-300 px-3 text-sm text-gray-600 transition-colors hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-800"
                    >
                        リセット
                    </Link>
                )}
            </div>

            {products.data.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-24 text-center">
                    <svg className="mb-4 h-16 w-16 text-gray-300 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <p className="text-lg font-medium text-gray-500 dark:text-neutral-400">
                        {filters.search || filters.brand || filters.collection ? '条件に一致する商品が見つかりませんでした' : '商品がありません'}
                    </p>
                    {(filters.search || filters.brand || filters.collection) && (
                        <Link
                            href={window.location.pathname}
                            className="mt-4 text-sm text-gray-600 underline hover:text-gray-900 dark:text-neutral-400 dark:hover:text-white"
                        >
                            すべての商品を見る
                        </Link>
                    )}
                </div>
            ) : (
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {products.data.map((product) => (
                        <Link
                            key={product.id}
                            href={product.slug ? show.url(product.slug) : '#'}
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
                                        <svg className="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                )}
                            </div>
                            <div className="flex flex-1 flex-col gap-2 p-4">
                                {product.brand && (
                                    <p className="text-xs font-medium tracking-wide text-gray-400 uppercase dark:text-neutral-500">
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
                        </Link>
                    ))}
                </div>
            )}

            {products.last_page > 1 && (
                <nav className="mt-10 flex items-center justify-center gap-1" aria-label="ページネーション">
                    {products.links.map((link, i) => {
                        if (link.url === null) {
                            return (
                                <span
                                    key={i}
                                    className="inline-flex h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm text-gray-400 dark:text-neutral-600"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
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
