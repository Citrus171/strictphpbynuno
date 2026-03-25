import {
    index as productsIndex,
    show,
} from '@/actions/App/Http/Controllers/ProductController';
import StorefrontLayout from '@/layouts/storefront-layout';
import { formatPrice } from '@/lib/format-price';
import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

interface VariantOption {
    name: string;
    value: string;
}

interface Variant {
    id: number;
    sku: string | null;
    price: number | null;
    stock: number;
    inStock: boolean;
    options: VariantOption[];
}

interface ProductImage {
    url: string | null;
    thumbnail: string | null;
}

interface RelatedProduct {
    id: number;
    name: string;
    price: number | null;
    thumbnail: string | null;
    slug: string | null;
}

interface Product {
    id: number;
    name: string;
    price: number | null;
    description: string | null;
    mainImage: string | null;
    images: ProductImage[];
    variants: Variant[];
    relatedProducts: RelatedProduct[];
}

interface Props {
    product: Product;
}

export default function ProductsShow({ product }: Props) {
    const firstVariant = product.variants[0] ?? null;
    const [selectedVariantId, setSelectedVariantId] = useState<number | null>(
        firstVariant?.id ?? null,
    );
    const [activeImageUrl, setActiveImageUrl] = useState<string | null>(
        product.mainImage,
    );

    const selectedVariant =
        product.variants.find((v) => v.id === selectedVariantId) ?? null;
    const displayPrice = selectedVariant?.price ?? product.price;
    const inStock = selectedVariant?.inStock ?? false;
    const stockCount = selectedVariant?.stock ?? 0;

    const hasOptions = product.variants.some((v) => v.options.length > 0);
    const hasMultipleVariants = product.variants.length > 1;

    return (
        <StorefrontLayout>
            <Head title={`${product.name} | 商品詳細`} />

            <div className="mb-6">
                <Link
                    href={productsIndex.url()}
                    className="text-sm text-gray-500 transition-colors hover:text-gray-900 dark:text-neutral-400 dark:hover:text-white"
                >
                    ← 商品一覧に戻る
                </Link>
            </div>

            <article className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                {/* 画像ギャラリー */}
                <div className="flex flex-col gap-3">
                    <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                        {activeImageUrl ? (
                            <img
                                src={activeImageUrl}
                                alt={product.name}
                                className="aspect-square h-full w-full object-cover"
                            />
                        ) : (
                            <div className="flex aspect-square items-center justify-center bg-gray-100 text-gray-400 dark:bg-neutral-700 dark:text-neutral-500">
                                画像がありません
                            </div>
                        )}
                    </div>

                    {product.images.length > 1 && (
                        <div className="flex flex-wrap gap-2">
                            {product.images.map((image, index) => (
                                <button
                                    key={index}
                                    onClick={() => setActiveImageUrl(image.url)}
                                    className={[
                                        'h-16 w-16 overflow-hidden rounded-lg border-2 transition-all',
                                        activeImageUrl === image.url
                                            ? 'border-gray-900 dark:border-white'
                                            : 'border-gray-200 hover:border-gray-400 dark:border-neutral-700 dark:hover:border-neutral-500',
                                    ].join(' ')}
                                >
                                    {image.thumbnail ? (
                                        <img
                                            src={image.thumbnail}
                                            alt={`${product.name} - 画像${index + 1}`}
                                            className="h-full w-full object-cover"
                                        />
                                    ) : (
                                        <div className="flex h-full w-full items-center justify-center bg-gray-100 dark:bg-neutral-700" />
                                    )}
                                </button>
                            ))}
                        </div>
                    )}
                </div>

                {/* 商品情報 */}
                <div className="flex flex-col gap-5">
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                        {product.name}
                    </h1>

                    {/* 価格 */}
                    <p className="text-2xl font-semibold text-gray-900 dark:text-white">
                        {formatPrice(displayPrice)}
                    </p>

                    {/* 在庫バッジ */}
                    {selectedVariant && (
                        <div className="flex items-center gap-2">
                            {inStock ? (
                                <>
                                    <span className="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                        <span className="h-1.5 w-1.5 rounded-full bg-green-500" />
                                        在庫あり
                                    </span>
                                    {stockCount > 0 && stockCount <= 5 && (
                                        <span className="text-xs text-amber-600 dark:text-amber-400">
                                            残り{stockCount}点
                                        </span>
                                    )}
                                </>
                            ) : (
                                <span className="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                    <span className="h-1.5 w-1.5 rounded-full bg-red-500" />
                                    在庫切れ
                                </span>
                            )}
                        </div>
                    )}

                    {/* バリアント選択 */}
                    {(hasMultipleVariants || hasOptions) && (
                        <section className="rounded-xl border border-gray-200 bg-white p-5 dark:border-neutral-700 dark:bg-neutral-800">
                            <h2 className="mb-3 text-sm font-semibold tracking-wide text-gray-600 uppercase dark:text-neutral-300">
                                オプション選択
                            </h2>
                            <div className="flex flex-wrap gap-2">
                                {product.variants.map((variant) => {
                                    const label =
                                        variant.options.length > 0
                                            ? variant.options
                                                  .map((o) => o.value)
                                                  .join(' / ')
                                            : (variant.sku ??
                                              `バリアント${variant.id}`);
                                    const isSelected =
                                        selectedVariantId === variant.id;
                                    return (
                                        <button
                                            key={variant.id}
                                            onClick={() =>
                                                setSelectedVariantId(variant.id)
                                            }
                                            disabled={!variant.inStock}
                                            className={[
                                                'rounded-lg border px-4 py-2 text-sm font-medium transition-all',
                                                isSelected
                                                    ? 'border-gray-900 bg-gray-900 text-white dark:border-white dark:bg-white dark:text-gray-900'
                                                    : variant.inStock
                                                      ? 'border-gray-300 text-gray-700 hover:border-gray-500 dark:border-neutral-600 dark:text-neutral-300 dark:hover:border-neutral-400'
                                                      : 'cursor-not-allowed border-gray-200 text-gray-300 line-through dark:border-neutral-700 dark:text-neutral-600',
                                            ].join(' ')}
                                        >
                                            {label}
                                        </button>
                                    );
                                })}
                            </div>
                        </section>
                    )}

                    {/* カートに追加ボタン */}
                    <button
                        disabled={!inStock || selectedVariant === null}
                        className="w-full rounded-xl bg-gray-900 px-6 py-3 text-base font-semibold text-white transition-colors hover:bg-gray-700 disabled:cursor-not-allowed disabled:bg-gray-300 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100 dark:disabled:bg-neutral-700 dark:disabled:text-neutral-500"
                    >
                        {inStock ? 'カートに追加' : '在庫切れ'}
                    </button>

                    {/* 商品説明 */}
                    <section className="rounded-xl border border-gray-200 bg-white p-5 dark:border-neutral-700 dark:bg-neutral-800">
                        <h2 className="mb-3 text-sm font-semibold tracking-wide text-gray-600 uppercase dark:text-neutral-300">
                            商品説明
                        </h2>
                        {product.description ? (
                            <p className="text-sm leading-7 whitespace-pre-line text-gray-700 dark:text-neutral-300">
                                {product.description}
                            </p>
                        ) : (
                            <p className="text-sm text-gray-500 dark:text-neutral-400">
                                説明文がありません
                            </p>
                        )}
                    </section>
                </div>
            </article>

            {/* 関連商品 */}
            {product.relatedProducts.length > 0 && (
                <section className="mt-12">
                    <h2 className="mb-6 text-xl font-bold text-gray-900 dark:text-white">
                        関連商品
                    </h2>
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        {product.relatedProducts.map((related) => (
                            <Link
                                key={related.id}
                                href={
                                    related.slug ? show.url(related.slug) : '#'
                                }
                                className="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-shadow hover:shadow-md dark:border-neutral-700 dark:bg-neutral-800"
                            >
                                <div className="aspect-square overflow-hidden bg-gray-100 dark:bg-neutral-700">
                                    {related.thumbnail ? (
                                        <img
                                            src={related.thumbnail}
                                            alt={related.name}
                                            className="h-full w-full object-cover transition-transform group-hover:scale-105"
                                        />
                                    ) : (
                                        <div className="flex h-full w-full items-center justify-center text-gray-400 dark:text-neutral-500">
                                            <svg
                                                className="h-10 w-10"
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
                                <div className="flex flex-1 flex-col gap-1 p-3">
                                    <h3 className="line-clamp-2 text-sm font-semibold text-gray-900 dark:text-white">
                                        {related.name}
                                    </h3>
                                    <p className="mt-auto text-sm font-bold text-gray-900 dark:text-white">
                                        {formatPrice(related.price)}
                                    </p>
                                </div>
                            </Link>
                        ))}
                    </div>
                </section>
            )}
        </StorefrontLayout>
    );
}
