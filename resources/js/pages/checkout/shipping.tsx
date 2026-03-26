import {
    address as checkoutAddress,
    storeShipping,
} from '@/actions/App/Http/Controllers/CheckoutController';
import StorefrontLayout from '@/layouts/storefront-layout';
import { formatPrice } from '@/lib/format-price';
import { Form, Head, Link } from '@inertiajs/react';

interface ShippingOption {
    identifier: string;
    name: string;
    description: string | null;
    price: number;
}

interface Props {
    shippingOptions: ShippingOption[];
}

export default function CheckoutShipping({ shippingOptions }: Props) {
    return (
        <StorefrontLayout>
            <Head title="配送方法" />
            <div className="mx-auto max-w-lg">
                <nav className="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-neutral-400">
                    <Link
                        href={checkoutAddress.url()}
                        className="hover:text-gray-700 dark:hover:text-neutral-200"
                    >
                        住所入力
                    </Link>
                    <span>›</span>
                    <span className="font-medium text-blue-600 dark:text-blue-400">
                        配送方法
                    </span>
                </nav>

                <h1 className="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                    配送方法を選択
                </h1>

                <Form {...storeShipping.form()}>
                    {({ errors, processing }) => (
                        <div className="space-y-4">
                            <div className="space-y-3 rounded-lg border border-gray-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                                {shippingOptions.length === 0 ? (
                                    <p className="text-sm text-gray-500 dark:text-neutral-400">
                                        利用可能な配送方法がありません。
                                    </p>
                                ) : (
                                    shippingOptions.map((option) => (
                                        <label
                                            key={option.identifier}
                                            className="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-4 hover:border-blue-400 dark:border-neutral-700 dark:hover:border-blue-500"
                                        >
                                            <input
                                                type="radio"
                                                name="identifier"
                                                value={option.identifier}
                                                className="text-blue-600"
                                            />
                                            <div className="flex-1">
                                                <p className="text-sm font-medium text-gray-900 dark:text-white">
                                                    {option.name}
                                                </p>
                                                {option.description && (
                                                    <p className="text-xs text-gray-500 dark:text-neutral-400">
                                                        {option.description}
                                                    </p>
                                                )}
                                            </div>
                                            <span className="text-sm font-medium text-gray-900 dark:text-white">
                                                {formatPrice(option.price)}
                                            </span>
                                        </label>
                                    ))
                                )}
                                {errors.identifier && (
                                    <p className="text-xs text-red-500">
                                        {errors.identifier}
                                    </p>
                                )}
                            </div>

                            <div className="flex items-center justify-between gap-4">
                                <Link
                                    href={checkoutAddress.url()}
                                    className="text-sm text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-neutral-200"
                                >
                                    ← 住所を変更する
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
                                >
                                    {processing ? '処理中...' : '注文を確定する'}
                                </button>
                            </div>
                        </div>
                    )}
                </Form>
            </div>
        </StorefrontLayout>
    );
}
