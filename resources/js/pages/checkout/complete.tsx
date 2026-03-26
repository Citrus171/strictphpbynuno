import StorefrontLayout from '@/layouts/storefront-layout';
import { formatPrice } from '@/lib/format-price';
import { Head, Link } from '@inertiajs/react';

interface OrderLine {
    name: string;
    quantity: number;
    subTotal: number | null;
}

interface Props {
    orderReference: string;
    total: number | null;
    lines: OrderLine[];
}

export default function CheckoutComplete({
    orderReference,
    total,
    lines,
}: Props) {
    return (
        <StorefrontLayout>
            <Head title="注文完了" />
            <div className="mx-auto max-w-lg">
                <div className="rounded-lg border border-gray-200 bg-white p-8 text-center dark:border-neutral-800 dark:bg-neutral-950">
                    <div className="mb-4 flex justify-center">
                        <div className="flex size-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                            <svg
                                className="size-8 text-green-600 dark:text-green-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={2}
                                    d="M5 13l4 4L19 7"
                                />
                            </svg>
                        </div>
                    </div>

                    <h1 className="mb-2 text-2xl font-bold text-gray-900 dark:text-white">
                        ご注文ありがとうございます
                    </h1>
                    <p className="mb-6 text-sm text-gray-500 dark:text-neutral-400">
                        注文番号:{' '}
                        <span className="font-mono font-semibold text-gray-900 dark:text-white">
                            {orderReference}
                        </span>
                    </p>
                </div>

                <div className="mt-4 rounded-lg border border-gray-200 bg-white p-6 dark:border-neutral-800 dark:bg-neutral-950">
                    <h2 className="mb-4 text-sm font-semibold text-gray-700 dark:text-neutral-300">
                        注文内容
                    </h2>
                    <div className="space-y-3">
                        {lines.map((line, index) => (
                            <div
                                key={index}
                                className="flex items-center justify-between gap-4"
                            >
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm text-gray-900 dark:text-white">
                                        {line.name}
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-neutral-400">
                                        × {line.quantity}
                                    </p>
                                </div>
                                <span className="text-sm font-medium text-gray-900 dark:text-white">
                                    {formatPrice(line.subTotal)}
                                </span>
                            </div>
                        ))}
                    </div>
                    <div className="mt-4 flex justify-between border-t border-gray-100 pt-4 font-semibold text-gray-900 dark:border-neutral-800 dark:text-white">
                        <span>合計</span>
                        <span>{formatPrice(total)}</span>
                    </div>
                </div>

                <div className="mt-6 text-center">
                    <Link
                        href="/"
                        className="text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        トップページに戻る
                    </Link>
                </div>
            </div>
        </StorefrontLayout>
    );
}
