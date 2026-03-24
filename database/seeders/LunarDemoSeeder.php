<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Lunar\Base\ValueObjects\Cart\TaxBreakdown;
use Lunar\Base\ValueObjects\Cart\TaxBreakdownAmount;
use Lunar\Models\Brand;
use Lunar\Models\Channel;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Order;
use Lunar\Models\OrderAddress;
use Lunar\Models\OrderLine;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;
use Lunar\FieldTypes\Text;

class LunarDemoSeeder extends Seeder
{
    public function run(): void
    {
        $channel = Channel::whereDefault(true)->first();
        $currency = Currency::whereDefault(true)->first();
        $customerGroup = CustomerGroup::whereDefault(true)->first();
        $taxClass = TaxClass::whereDefault(true)->first();
        $productType = ProductType::first();
        $country = Country::first();

        // ブランド作成
        $brands = collect([
            'Nike', 'Apple', 'Sony', 'Adidas', 'Samsung',
        ])->map(fn (string $name) => Brand::create(['name' => $name]));

        // 商品カタログ
        $catalog = [
            ['name' => 'Running Shoes Pro', 'brand' => 'Nike', 'price' => 12800, 'sku' => 'NK-RS-001'],
            ['name' => 'Training Shoes Air', 'brand' => 'Nike', 'price' => 9800, 'sku' => 'NK-TS-002'],
            ['name' => 'iPhone Case Premium', 'brand' => 'Apple', 'price' => 3500, 'sku' => 'AP-IC-001'],
            ['name' => 'Wireless Charger', 'brand' => 'Apple', 'price' => 6800, 'sku' => 'AP-WC-002'],
            ['name' => 'Noise Cancelling Headphones', 'brand' => 'Sony', 'price' => 32000, 'sku' => 'SN-NC-001'],
            ['name' => 'Bluetooth Speaker', 'brand' => 'Sony', 'price' => 15000, 'sku' => 'SN-BS-002'],
            ['name' => 'Sport T-Shirt', 'brand' => 'Adidas', 'price' => 4500, 'sku' => 'AD-ST-001'],
            ['name' => 'Track Pants', 'brand' => 'Adidas', 'price' => 7800, 'sku' => 'AD-TP-002'],
            ['name' => '4K Smart TV 55inch', 'brand' => 'Samsung', 'price' => 89000, 'sku' => 'SS-TV-001'],
            ['name' => 'Galaxy Tablet', 'brand' => 'Samsung', 'price' => 54000, 'sku' => 'SS-GT-002'],
        ];

        $variants = collect();

        foreach ($catalog as $item) {
            $brand = $brands->firstWhere('name', $item['brand']);

            $product = Product::create([
                'product_type_id' => $productType->id,
                'status' => 'published',
                'brand_id' => $brand->id,
                'attribute_data' => collect([
                    'name' => new Text($item['name']),
                    'description' => new Text(fake()->sentence(10)),
                ]),
            ]);

            $product->channels()->attach($channel->id, [
                'enabled' => true,
                'starts_at' => now()->subDays(30),
            ]);

            $product->customerGroups()->attach($customerGroup->id, [
                'enabled' => true,
                'purchasable' => true,
                'visible' => true,
            ]);

            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'tax_class_id' => $taxClass->id,
                'sku' => $item['sku'],
                'unit_quantity' => 1,
                'stock' => fake()->numberBetween(10, 200),
                'shippable' => true,
            ]);

            Price::create([
                'priceable_type' => ProductVariant::morphName(),
                'priceable_id' => $variant->id,
                'currency_id' => $currency->id,
                'price' => $item['price'],
                'min_quantity' => 1,
            ]);

            $variants->push(['variant' => $variant, 'price' => $item['price'], 'name' => $item['name']]);
        }

        // 顧客作成
        $customerData = [
            ['first_name' => '太郎', 'last_name' => '田中'],
            ['first_name' => '花子', 'last_name' => '鈴木'],
            ['first_name' => '次郎', 'last_name' => '佐藤'],
            ['first_name' => '美咲', 'last_name' => '山田'],
            ['first_name' => '健一', 'last_name' => '伊藤'],
        ];

        $customers = collect();
        foreach ($customerData as $data) {
            $customer = Customer::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'company_name' => null,
            ]);
            $customer->customerGroups()->attach($customerGroup->id);
            $customers->push($customer);
        }

        // 注文作成（過去30日分 20件）
        $statuses = ['awaiting-payment', 'payment-received', 'processing', 'dispatched', 'cancelled'];

        for ($i = 0; $i < 20; $i++) {
            $customer = $customers->random();
            $item = $variants->random();
            $qty = fake()->numberBetween(1, 3);
            $unitPrice = $item['price'];
            $subTotal = $unitPrice * $qty;
            $taxTotal = (int) ($subTotal * 0.1);
            $shipping = 800;
            $total = $subTotal + $taxTotal + $shipping;

            $taxBreakdown = new TaxBreakdown(collect([
                new TaxBreakdownAmount(
                    price: new \Lunar\DataTypes\Price($taxTotal, $currency, 1),
                    identifier: 'tax',
                    description: '消費税 10%',
                    percentage: 10,
                ),
            ]));

            $order = Order::create([
                'channel_id' => $channel->id,
                'new_customer' => $i < 10,
                'user_id' => null,
                'customer_id' => $customer->id,
                'status' => fake()->randomElement($statuses),
                'reference' => strtoupper(fake()->unique()->bothify('??-######')),
                'sub_total' => $subTotal,
                'discount_total' => 0,
                'shipping_total' => $shipping,
                'tax_breakdown' => $taxBreakdown,
                'tax_total' => $taxTotal,
                'total' => $total,
                'notes' => null,
                'currency_code' => $currency->code,
                'compare_currency_code' => $currency->code,
                'exchange_rate' => 1,
                'placed_at' => now()->subDays(fake()->numberBetween(0, 30)),
                'meta' => [],
            ]);

            OrderLine::create([
                'order_id' => $order->id,
                'purchasable_type' => ProductVariant::morphName(),
                'purchasable_id' => $item['variant']->id,
                'type' => 'physical',
                'description' => $item['name'],
                'option' => null,
                'identifier' => $item['variant']->sku,
                'unit_price' => $unitPrice,
                'unit_quantity' => 1,
                'quantity' => $qty,
                'sub_total' => $subTotal,
                'discount_total' => 0,
                'tax_breakdown' => $taxBreakdown,
                'tax_total' => $taxTotal,
                'total' => $total,
                'notes' => null,
                'meta' => [],
            ]);

            OrderAddress::create([
                'order_id' => $order->id,
                'type' => 'shipping',
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'line_one' => fake()->streetAddress(),
                'city' => fake()->city(),
                'postcode' => fake()->postcode(),
                'country_id' => $country->id,
            ]);
        }
    }
}
