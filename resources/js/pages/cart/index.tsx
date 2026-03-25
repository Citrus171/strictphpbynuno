import {
    destroy as cartDestroy,
    update as cartUpdate,
} from '@/actions/App/Http/Controllers/CartController';
import StorefrontLayout from '@/layouts/storefront-layout';
import { formatPrice } from '@/lib/format-price';
import { Head, router } from '@inertiajs/react';

interface CartItem {
    cartLineId: number;
    variantId: number;
    productName: string;
    variantOptions: string;
    sku: string | null;
    quantity: number;
    unitPrice: number | null;
    subTotal: number | null;
}

interface Props {
    items: CartItem[];
    total: number | null;
}

export default function CartIndex({ items, total }: Props) {
    function updateQuantity(cartLineId: number, quantity: number) {
        router.patch(
            cartUpdate.url(cartLineId),
            { quantity },
            { preserveScroll: true },
        );
    }

    function removeItem(cartLineId: number) {
        router.delete(cartDestroy.url(cartLineId), { preserveScroll: true });
    }

    return (
        <StorefrontLayout>
            <Head title="カート" />
            <div className="mx-auto max-w-3xl">
                <h1 className="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                    カート
                </h1>
                {items.length === 0 ? (
                    <p className="text-gray-500 dark:text-neutral-400">
                        カートは空です。
                    </p>
                ) : (
                    <div className="space-y-4">
                        {items.map((item) => (
                            <div
                                key={item.cartLineId}
                                className="flex items-start gap-4 rounded-lg border border-gray-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950"
                            >
                                <div className="min-w-0 flex-1">
                                    <p className="font-medium text-gray-900 dark:text-white">
                                        {item.productName}
                                    </p>
                                    {item.variantOptions && (
                                        <p className="text-sm text-gray-500 dark:text-neutral-400">
                                            {item.variantOptions}
                                        </p>
                                    )}
                                    {item.sku && (
                                        <p className="text-xs text-gray-400 dark:text-neutral-500">
                                            SKU: {item.sku}
                                        </p>
                                    )}
                                    <div className="mt-3 flex items-center gap-2">
                                        <button
                                            onClick={() =>
                                                updateQuantity(
                                                    item.cartLineId,
                                                    item.quantity - 1,
                                                )
                                            }
                                            disabled={item.quantity <= 1}
                                            className="flex h-7 w-7 items-center justify-center rounded border border-gray-300 text-gray-600 transition-colors hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-900"
                                            aria-label="数量を減らす"
                                        >
                                            −
                                        </button>
                                        <span className="w-8 text-center text-sm font-medium text-gray-900 dark:text-white">
                                            {item.quantity}
                                        </span>
                                        <button
                                            onClick={() =>
                                                updateQuantity(
                                                    item.cartLineId,
                                                    item.quantity + 1,
                                                )
                                            }
                                            className="flex h-7 w-7 items-center justify-center rounded border border-gray-300 text-gray-600 transition-colors hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-900"
                                            aria-label="数量を増やす"
                                        >
                                            ＋
                                        </button>
                                    </div>
                                </div>
                                <div className="flex flex-col items-end gap-2">
                                    {item.subTotal != null && (
                                        <p className="font-medium text-gray-900 dark:text-white">
                                            {formatPrice(item.subTotal)}
                                        </p>
                                    )}
                                    {item.unitPrice != null &&
                                        item.quantity > 1 && (
                                            <p className="text-xs text-gray-400 dark:text-neutral-500">
                                                {formatPrice(item.unitPrice)} ×{' '}
                                                {item.quantity}
                                            </p>
                                        )}
                                    <button
                                        onClick={() =>
                                            removeItem(item.cartLineId)
                                        }
                                        className="text-xs text-red-500 transition-colors hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        削除
                                    </button>
                                </div>
                            </div>
                        ))}
                        <div className="flex justify-end border-t border-gray-200 pt-4 dark:border-neutral-800">
                            <p className="text-lg font-bold text-gray-900 dark:text-white">
                                合計: {formatPrice(total)}
                            </p>
                        </div>
                    </div>
                )}
            </div>
        </StorefrontLayout>
    );
}
