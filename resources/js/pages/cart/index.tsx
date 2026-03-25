import {
    applyCoupon as cartApplyCoupon,
    destroy as cartDestroy,
    removeCoupon as cartRemoveCoupon,
    update as cartUpdate,
} from '@/actions/App/Http/Controllers/CartController';
import StorefrontLayout from '@/layouts/storefront-layout';
import { formatPrice } from '@/lib/format-price';
import { Form, Head, router } from '@inertiajs/react';

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

interface ShippingOption {
    identifier: string;
    name: string;
    description: string | null;
    price: number;
}

interface Props {
    items: CartItem[];
    subTotal: number | null;
    total: number | null;
    couponCode: string | null;
    discountTotal: number | null;
    shippingOptions: ShippingOption[];
}

export default function CartIndex({
    items,
    subTotal,
    total,
    couponCode,
    discountTotal,
    shippingOptions,
}: Props) {
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

    function removeCoupon() {
        router.delete(cartRemoveCoupon.url(), { preserveScroll: true });
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

                        {/* クーポン入力 */}
                        <div className="rounded-lg border border-gray-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                            <h2 className="mb-3 text-sm font-medium text-gray-900 dark:text-white">
                                クーポンコード
                            </h2>
                            {couponCode ? (
                                <div className="flex items-center justify-between">
                                    <span className="text-sm text-green-600 dark:text-green-400">
                                        ✓ 適用中:{' '}
                                        <span className="font-mono font-semibold">
                                            {couponCode}
                                        </span>
                                    </span>
                                    <button
                                        onClick={removeCoupon}
                                        className="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                    >
                                        削除
                                    </button>
                                </div>
                            ) : (
                                <Form
                                    {...cartApplyCoupon.form()}
                                    preserveScroll
                                >
                                    {({ errors, processing }) => (
                                        <div className="flex gap-2">
                                            <input
                                                type="text"
                                                name="couponCode"
                                                placeholder="クーポンコードを入力"
                                                className="flex-1 rounded border border-gray-300 px-3 py-1.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                                                aria-label="クーポンコード"
                                            />
                                            <button
                                                type="submit"
                                                disabled={processing}
                                                className="rounded bg-gray-900 px-3 py-1.5 text-sm text-white transition-colors hover:bg-gray-700 disabled:opacity-50 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200"
                                            >
                                                {processing
                                                    ? '適用中...'
                                                    : '適用'}
                                            </button>
                                            {errors.couponCode && (
                                                <p className="mt-1 text-xs text-red-500">
                                                    {errors.couponCode}
                                                </p>
                                            )}
                                        </div>
                                    )}
                                </Form>
                            )}
                        </div>

                        {/* 送料オプション */}
                        {shippingOptions.length > 0 && (
                            <div className="rounded-lg border border-gray-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950">
                                <h2 className="mb-3 text-sm font-medium text-gray-900 dark:text-white">
                                    送料
                                </h2>
                                <ul className="space-y-2">
                                    {shippingOptions.map((option) => (
                                        <li
                                            key={option.identifier}
                                            className="flex items-center justify-between"
                                        >
                                            <div>
                                                <span className="text-sm text-gray-900 dark:text-white">
                                                    {option.name}
                                                </span>
                                                {option.description && (
                                                    <span className="ml-2 text-xs text-gray-400 dark:text-neutral-500">
                                                        {option.description}
                                                    </span>
                                                )}
                                            </div>
                                            <span className="text-sm font-medium text-gray-900 dark:text-white">
                                                {formatPrice(option.price)}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}

                        {/* 合計内訳 */}
                        {(() => {
                            const cheapestShipping =
                                shippingOptions.length > 0
                                    ? shippingOptions.reduce((a, b) =>
                                          a.price <= b.price ? a : b,
                                      )
                                    : null;
                            const grandTotal =
                                total != null && cheapestShipping != null
                                    ? total + cheapestShipping.price
                                    : total;
                            return (
                                <div className="space-y-2 border-t border-gray-200 pt-4 dark:border-neutral-800">
                                    {subTotal != null && (
                                        <div className="flex justify-between text-sm text-gray-600 dark:text-neutral-400">
                                            <span>小計</span>
                                            <span>{formatPrice(subTotal)}</span>
                                        </div>
                                    )}
                                    {discountTotal != null &&
                                        discountTotal > 0 && (
                                            <div className="flex justify-between text-sm text-green-600 dark:text-green-400">
                                                <span>割引</span>
                                                <span>
                                                    −
                                                    {formatPrice(discountTotal)}
                                                </span>
                                            </div>
                                        )}
                                    {cheapestShipping != null && (
                                        <div className="flex justify-between text-sm text-gray-600 dark:text-neutral-400">
                                            <span>
                                                送料（{cheapestShipping.name}）
                                            </span>
                                            <span>
                                                {formatPrice(
                                                    cheapestShipping.price,
                                                )}
                                            </span>
                                        </div>
                                    )}
                                    <div className="flex justify-between border-t border-gray-200 pt-2 dark:border-neutral-800">
                                        <p className="text-lg font-bold text-gray-900 dark:text-white">
                                            合計
                                        </p>
                                        <p className="text-lg font-bold text-gray-900 dark:text-white">
                                            {formatPrice(grandTotal)}
                                        </p>
                                    </div>
                                    {cheapestShipping == null &&
                                        shippingOptions.length === 0 &&
                                        items.length > 0 && (
                                            <p className="text-xs text-gray-400 dark:text-neutral-500">
                                                ※ 送料は別途かかります
                                            </p>
                                        )}
                                </div>
                            );
                        })()}
                    </div>
                )}
            </div>
        </StorefrontLayout>
    );
}
