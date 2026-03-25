import StorefrontLayout from '@/layouts/storefront-layout';
import { Head } from '@inertiajs/react';

interface Product {
    id: number;
    name: string;
    price: number | null;
    description: string | null;
    mainImage: string | null;
}

interface Props {
    product: Product;
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

export default function ProductsShow({ product }: Props) {
    return (
        <StorefrontLayout>
            <Head title={`${product.name} | 商品詳細`} />

            <article className="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-neutral-700 dark:bg-neutral-800">
                    {product.mainImage ? (
                        <img
                            src={product.mainImage}
                            alt={product.name}
                            className="h-full w-full object-cover"
                        />
                    ) : (
                        <div className="flex aspect-square items-center justify-center bg-gray-100 text-gray-400 dark:bg-neutral-700 dark:text-neutral-500">
                            画像がありません
                        </div>
                    )}
                </div>

                <div className="flex flex-col gap-5">
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-white">
                        {product.name}
                    </h1>
                    <p className="text-2xl font-semibold text-gray-900 dark:text-white">
                        {formatPrice(product.price)}
                    </p>

                    <section className="rounded-xl border border-gray-200 bg-white p-5 dark:border-neutral-700 dark:bg-neutral-800">
                        <h2 className="mb-3 text-sm font-semibold tracking-wide text-gray-600 uppercase dark:text-neutral-300">
                            商品説明
                        </h2>
                        {product.description ? (
                            <div
                                className="prose prose-gray max-w-none text-sm leading-7 dark:prose-invert"
                                dangerouslySetInnerHTML={{
                                    __html: product.description,
                                }}
                            />
                        ) : (
                            <p className="text-sm text-gray-500 dark:text-neutral-400">
                                説明文がありません
                            </p>
                        )}
                    </section>
                </div>
            </article>
        </StorefrontLayout>
    );
}
