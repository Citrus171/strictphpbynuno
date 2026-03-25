export function formatPrice(price: number | null): string {
    if (price === null) {
        return '価格未設定';
    }

    return new Intl.NumberFormat('ja-JP', {
        style: 'currency',
        currency: 'JPY',
    }).format(price);
}
