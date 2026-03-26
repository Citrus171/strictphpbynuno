import {
    shipping as checkoutShipping,
    storeAddress,
} from '@/actions/App/Http/Controllers/CheckoutController';
import StorefrontLayout from '@/layouts/storefront-layout';
import { Form, Head, Link } from '@inertiajs/react';

export default function CheckoutAddress() {
    return (
        <StorefrontLayout>
            <Head title="配送先住所" />
            <div className="mx-auto max-w-lg">
                <nav className="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-neutral-400">
                    <span className="font-medium text-blue-600 dark:text-blue-400">
                        住所入力
                    </span>
                    <span>›</span>
                    <span>配送方法</span>
                </nav>

                <h1 className="mb-6 text-2xl font-bold text-gray-900 dark:text-white">
                    配送先住所
                </h1>

                <Form {...storeAddress.form()}>
                    {({ errors, processing }) => (
                        <div className="space-y-4 rounded-lg border border-gray-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                            <div>
                                <label
                                    htmlFor="first_name"
                                    className="mb-1 block text-sm font-medium text-gray-700 dark:text-neutral-300"
                                >
                                    氏名
                                </label>
                                <input
                                    id="first_name"
                                    name="first_name"
                                    type="text"
                                    autoComplete="name"
                                    className="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                                    placeholder="山田 太郎"
                                />
                                {errors.first_name && (
                                    <p className="mt-1 text-xs text-red-500">
                                        {errors.first_name}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="postcode"
                                    className="mb-1 block text-sm font-medium text-gray-700 dark:text-neutral-300"
                                >
                                    郵便番号
                                </label>
                                <input
                                    id="postcode"
                                    name="postcode"
                                    type="text"
                                    autoComplete="postal-code"
                                    className="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                                    placeholder="100-0001"
                                />
                                {errors.postcode && (
                                    <p className="mt-1 text-xs text-red-500">
                                        {errors.postcode}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="state"
                                    className="mb-1 block text-sm font-medium text-gray-700 dark:text-neutral-300"
                                >
                                    都道府県
                                </label>
                                <input
                                    id="state"
                                    name="state"
                                    type="text"
                                    autoComplete="address-level1"
                                    className="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                                    placeholder="東京都"
                                />
                                {errors.state && (
                                    <p className="mt-1 text-xs text-red-500">
                                        {errors.state}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="city"
                                    className="mb-1 block text-sm font-medium text-gray-700 dark:text-neutral-300"
                                >
                                    市区町村
                                </label>
                                <input
                                    id="city"
                                    name="city"
                                    type="text"
                                    autoComplete="address-level2"
                                    className="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                                    placeholder="千代田区"
                                />
                                {errors.city && (
                                    <p className="mt-1 text-xs text-red-500">
                                        {errors.city}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="line_one"
                                    className="mb-1 block text-sm font-medium text-gray-700 dark:text-neutral-300"
                                >
                                    番地・建物名
                                </label>
                                <input
                                    id="line_one"
                                    name="line_one"
                                    type="text"
                                    autoComplete="address-line1"
                                    className="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                                    placeholder="千代田1-1-1"
                                />
                                {errors.line_one && (
                                    <p className="mt-1 text-xs text-red-500">
                                        {errors.line_one}
                                    </p>
                                )}
                            </div>

                            <div>
                                <label
                                    htmlFor="contact_phone"
                                    className="mb-1 block text-sm font-medium text-gray-700 dark:text-neutral-300"
                                >
                                    電話番号
                                </label>
                                <input
                                    id="contact_phone"
                                    name="contact_phone"
                                    type="tel"
                                    autoComplete="tel"
                                    className="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-neutral-700 dark:bg-neutral-900 dark:text-white"
                                    placeholder="03-1234-5678"
                                />
                                {errors.contact_phone && (
                                    <p className="mt-1 text-xs text-red-500">
                                        {errors.contact_phone}
                                    </p>
                                )}
                            </div>

                            <div className="flex items-center justify-between gap-4 pt-2">
                                <Link
                                    href={checkoutShipping.url()}
                                    className="text-sm text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-neutral-200"
                                >
                                    ← カートに戻る
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="rounded-lg bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50 dark:bg-blue-500 dark:hover:bg-blue-600"
                                >
                                    {processing ? '処理中...' : '配送方法を選ぶ'}
                                </button>
                            </div>
                        </div>
                    )}
                </Form>
            </div>
        </StorefrontLayout>
    );
}
