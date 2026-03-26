import {
    shipping as checkoutShipping,
    storeConfirm,
} from '@/actions/App/Http/Controllers/CheckoutController';
import StorefrontLayout from '@/layouts/storefront-layout';
import { formatPrice } from '@/lib/format-price';
import { Form, Head, Link } from '@inertiajs/react';

interface OrderLine {
    id: number;
    name: string;
    quantity: number;
    unitPrice: number | null;
    subTotal: number | null;
}

interface Props {
    lines: OrderLine[];
    subTotal: number | null;
    shippingTotal: number | null;
    discountTotal: number | null;
    total: number | null;
}

export default function CheckoutConfirm({
    lines,
    subTotal,
    shippingTotal,
    discountTotal,
    total,
}: Props) {
    return (
        <StorefrontLayout>
            <Head title="注文確認" />
            <div className="mx-auto max-w-lg">
                <nav className="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-neutral-400">
                    <span>住所入力</span>
                    <span>›</span>
                    <span>配送方法</span>
                    <span>›</span>
                    <span className="font-medium text-blue-600 dark:text-blue-400">
                        注文確認
                    </span>
                </nav>

                <h1 className="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                    注文内容の確認
                </h1>

                <div className="space-y-4">
                    <div className="rounded-lg border border-gray-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                        <h2 className="mb-4 text-sm font-semibold text-gray-700 dark:text-neutral-300">
                            商品一覧
                        </h2>
                        <div className="space-y-3">
                            {lines.map((line) => (
                                <div
                                    key={line.id}
                                    className="flex items-center justify-between gap-4"
                                >
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate text-sm text-gray-900 dark:text-white">
                                            {line.name}
                                        </p>
                                        <p className="text-xs text-gray-500 dark:text-neutral-400">
                                            {formatPrice(line.unitPrice)} ×{' '}
                                            {line.quantity}
                                        </p>
                                    </div>
                                    <span className="text-sm font-medium text-gray-900 dark:text-white">
                                        {formatPrice(line.subTotal)}
                                    </span>
                                </div>
                            ))}
                        </div>

                        <div className="mt-4 space-y-2 border-t border-gray-100 pt-4 dark:border-neutral-800">
                            <div className="flex justify-between text-sm text-gray-600 dark:text-neutral-400">
                                <span>小計</span>
                                <span>{formatPrice(subTotal)}</span>
                            </div>
                            {shippingTotal !== null && (
                                <div className="flex justify-between text-sm text-gray-600 dark:text-neutral-400">
                                    <span>送料</span>
                                    <span>{formatPrice(shippingTotal)}</span>
                                </div>
                            )}
                            {discountTotal !== null && discountTotal > 0 && (
                                <div className="flex justify-between text-sm text-green-600 dark:text-green-400">
                                    <span>割引</span>
                                    <span>-{formatPrice(discountTotal)}</span>
                                </div>
                            )}
                            <div className="flex justify-between border-t border-gray-100 pt-2 font-semibold text-gray-900 dark:border-neutral-800 dark:text-white">
                                <span>合計</span>
                                <span>{formatPrice(total)}</span>
                            </div>
                        </div>
                    </div>

                    <Form {...storeConfirm.form()}>
                        {({ processing }) => (
                            <div className="flex items-center justify-between gap-4">
                                <Link
                                    href={checkoutShipping.url()}
                                    className="text-sm text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-neutral-200"
                                >
                                    ← 配送方法を変更する
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
                                >
                                    {processing
                                        ? '処理中...'
                                        : '注文を確定する'}
                                </button>
                            </div>
                        )}
                    </Form>
                </div>
            </div>
        </StorefrontLayout>
    );
}
