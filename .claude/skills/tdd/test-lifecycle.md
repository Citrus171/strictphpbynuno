# テストデータのライフサイクル管理

<!-- 2026-03-24 追加 -->

このプロジェクトのテストは **Pest** を使用する。PHPUnitのクラスベース記法（`class FooTest extends TestCase`）は使わない。
テストは `php artisan make:test --pest {name}` で作成する。

---

## Pest（Laravel）: `uses(RefreshDatabase::class)`

各テスト後にトランザクションがロールバックされ、DB がクリーンな状態に戻る。
Factory を使ってテストデータを作成する。

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('有効なカートの時、注文が確定すること', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);

    // Act
    $response = $this->actingAs($user)
        ->postJson('/orders', ['product_id' => $product->id]);

    // Assert
    $response->assertCreated();
    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'status' => 'confirmed',
    ]);
});
```

`uses()` はファイル単位で宣言するか、`tests/Pest.php` でグローバルに適用できる：

```php
// tests/Pest.php
uses(RefreshDatabase::class)->in('Feature');
```

### 異常系での不変条件の検証

エラー発生時は DB の状態が変化していないことも検証する。

```php
it('在庫不足の時、注文が失敗しDBが変化しないこと', function () {
    // Arrange
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 0]);
    $orderCountBefore = Order::count();

    // Act
    $response = $this->actingAs($user)
        ->postJson('/orders', ['product_id' => $product->id]);

    // Assert: エラーレスポンス
    $response->assertUnprocessable();

    // Assert: 不変条件（DB が変化していないこと）
    expect(Order::count())->toBe($orderCountBefore);
});
```

---

## AAA パターンの視覚的な分離

空行で3フェーズを分離する。

| フェーズ | 役割 | 配置場所 |
|---|---|---|
| **Arrange**（準備） | 共通の前提条件 | `beforeEach` 内 |
| **Arrange**（個別） | そのテスト固有の追加条件 | `it` ブロックの先頭 |
| **Act**（実行） | テスト対象の振る舞いを1つだけ実行 | `it` ブロック内 |
| **Assert**（検証） | 戻り値 + DB の状態変化の両方を検証 | `it` ブロック内 |

### `beforeEach` を使ったデータ共有

```php
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('注文が確定すること', function () {
    $product = Product::factory()->create(['stock' => 10]);

    $response = $this->actingAs($this->user)
        ->postJson('/orders', ['product_id' => $product->id]);

    $response->assertCreated();
});

it('在庫不足の時に失敗すること', function () {
    $product = Product::factory()->create(['stock' => 0]);

    $response = $this->actingAs($this->user)
        ->postJson('/orders', ['product_id' => $product->id]);

    $response->assertUnprocessable();
});
```

---

## Pest Datasets（バリデーションテストの簡略化）

同じ検証ルールを複数パターンでテストする場合は Dataset を使う。

```php
it('無効なメールアドレスを拒否すること', function (string $email) {
    $response = $this->postJson('/register', ['email' => $email]);

    $response->assertUnprocessable();
})->with([
    '空文字' => [''],
    'ドメインなし' => ['foo@'],
    '@なし' => ['fooexample.com'],
]);
```
