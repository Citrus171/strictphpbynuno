# PRD: React Storefront for LunarPHP

## Problem Statement

このプロジェクトにはLunarPHP（ECコアエンジン）と管理パネル（Filament）が導入されているが、ユーザー向けのStorefrontが存在しない。商品閲覧からカート・注文確認までの購買フローをユーザーが利用できるフロントエンドが必要。

## Solution

既存のReact + Inertia.js + Tailwind CSS v4構成を活用し、LunarPHPのバックエンドAPIと連携したユーザー向けStorefrontを`localhost:8000`に構築する。shadcn/uiコンポーネントを活用し、モバイル対応のレスポンシブデザインで実装する。認証はゲスト対応とし、決済は将来のPhase 3で追加する。

## User Stories

### 商品閲覧

1. As a guest shopper, I want to browse a list of all products, so that I can discover items to purchase.
2. As a guest shopper, I want to filter products by brand, so that I can find products from my preferred brand.
3. As a guest shopper, I want to filter products by category (Collection), so that I can narrow down products by type.
4. As a guest shopper, I want to sort products by price (ascending/descending), so that I can find products within my budget.
5. As a guest shopper, I want to sort products by name, so that I can find a specific product alphabetically.
6. As a guest shopper, I want to search products by keyword, so that I can quickly find specific items.
7. As a guest shopper, I want paginated product results, so that the page loads quickly even with many products.
8. As a guest shopper, I want to see a product thumbnail image in the listing, so that I can visually identify products.
9. As a guest shopper, I want to see the product price in the listing, so that I can compare prices without opening each product.
10. As a guest shopper, I want to see the product brand in the listing, so that I can identify the manufacturer at a glance.

### 商品詳細

11. As a guest shopper, I want to view a product detail page, so that I can get full information about a product.
12. As a guest shopper, I want to see multiple product images with thumbnail switching, so that I can view the product from different angles.
13. As a guest shopper, I want to select product variants (size, color, etc.), so that I can choose the specific option I want.
14. As a guest shopper, I want to see the price update when I select a variant, so that I know the exact cost of my chosen option.
15. As a guest shopper, I want to see the stock availability (in stock / out of stock / remaining quantity), so that I know if I can purchase the item.
16. As a guest shopper, I want to read the full product description (rich text), so that I can make an informed purchase decision.
17. As a guest shopper, I want to see related products on the product detail page, so that I can discover similar items.
18. As a guest shopper, I want an "Add to Cart" button to be disabled when out of stock, so that I don't try to purchase unavailable items.

### カート

19. As a guest shopper, I want to add a product variant to my cart, so that I can collect items before purchasing.
20. As a guest shopper, I want to view my cart with all added items, so that I can review my selections.
21. As a guest shopper, I want to change the quantity of an item in my cart, so that I can adjust how many I want to buy.
22. As a guest shopper, I want to remove an item from my cart, so that I can change my mind about a product.
23. As a guest shopper, I want to see the subtotal per item in my cart, so that I can understand the cost breakdown.
24. As a guest shopper, I want to see the cart total (subtotal + shipping), so that I know the total amount before checkout.
25. As a guest shopper, I want to enter a coupon/discount code, so that I can apply a discount to my order.
26. As a guest shopper, I want to see the discount amount applied when a valid coupon is entered, so that I can confirm the saving.
27. As a guest shopper, I want to see an error message when an invalid coupon is entered, so that I know the code did not work.
28. As a guest shopper, I want to see an estimated shipping cost in my cart, so that I know the full cost before proceeding.
29. As a guest shopper, I want my cart to persist in my session, so that items remain if I navigate away and return.
30. As a guest shopper, I want to see a cart item count in the header/navigation, so that I can quickly see how many items are in my cart.

### チェックアウト・注文確認

31. As a guest shopper, I want to proceed to checkout from my cart, so that I can start the order process.
32. As a guest shopper, I want to enter my shipping address (name, postal code, prefecture, city, address line, phone), so that my order can be delivered.
33. As a guest shopper, I want to select from available shipping methods, so that I can choose my preferred delivery option.
34. As a guest shopper, I want to see the shipping cost update based on my selected shipping method, so that I know the delivery cost.
35. As a guest shopper, I want to see an order summary (items, quantities, prices, shipping, total) on the confirmation page, so that I can review everything before placing the order.
36. As a guest shopper, I want to place an order (without payment) and receive an order number, so that I have a record of my purchase.
37. As a guest shopper, I want to see an order completion page with my order number, so that I know my order was successfully placed.
38. As a guest shopper, I want to receive a summary of what I ordered on the completion page, so that I have a reference of my purchase.

### UI・UX

39. As a shopper on mobile, I want the storefront to be fully responsive, so that I can shop comfortably on my smartphone.
40. As a shopper, I want the UI to be in Japanese, so that I can understand all content natively.
41. As a shopper, I want loading states (skeleton/spinner) while data is fetching, so that I know the page is working.
42. As a shopper, I want error messages to be clearly displayed, so that I know what went wrong.

## Implementation Decisions

### モジュール構成

**1. ProductCatalog モジュール**
- 商品一覧の取得・フィルタ・ソート・検索・ページネーションを担うActionクラス群
- `GetProductsAction`: フィルタ/ソート/検索/ページネーション付き商品一覧取得
- `GetProductAction`: 単一商品（バリアント・画像・関連商品付き）取得
- 対応するControllerとInertia Pageコンポーネント

**2. Cart モジュール**
- LunarPHPの`CartSession`ファサードをラップするActionクラス群
- `AddToCartAction`: バリアントをカートに追加
- `UpdateCartItemAction`: カート内アイテムの数量変更
- `RemoveCartItemAction`: カートからアイテム削除
- `ApplyCouponAction`: クーポンコードの適用・検証
- カートデータはセッションベース（ゲスト対応）

**3. Checkout モジュール**
- `CreateOrderAction`: LunarのOrderパイプラインを通じてOrder作成
- 配送先住所はLunarの`Address`モデルで管理
- 配送方法はLunarの`ShippingOption`から取得
- 決済処理はOut of Scope（Phase 3）

**4. React Pages（Inertia）**
- `Products/Index` - 商品一覧（フィルタ・ソート・検索・ページネーション）
- `Products/Show` - 商品詳細（バリアント選択・画像・在庫・関連商品）
- `Cart/Index` - カート（数量変更・削除・クーポン・送料・合計）
- `Checkout/Address` - 配送先住所入力
- `Checkout/Shipping` - 配送方法選択
- `Checkout/Confirm` - 注文確認
- `Checkout/Complete` - 注文完了

### アーキテクチャ決定事項

- フロントエンド: React + Inertia.js v2（既存構成を踏襲）
- UIコンポーネント: shadcn/ui + Tailwind CSS v4（既存コンポーネントを流用）
- 認証: ゲスト対応（セッションベースカート）、ログイン必須化はPhase 3
- LunarPHP統合: CartSessionファサード・Pricingファサード・Eloquentモデルを直接利用
- 言語: 日本語（多言語対応はOut of Scope）
- レスポンシブ: モバイルファースト

## Testing Decisions

### テスト方針

- **良いテストの条件**: 実装の詳細ではなく外部から観察可能な振る舞いをテストする。Actionクラスは入力と出力をテストし、Controllerテストは実際のHTTPレスポンスを検証する。

### テスト対象モジュール

**Actionクラス（Unit/Featureテスト）**
- `GetProductsAction`: フィルタ・ソート・検索・ページネーションが正しく機能するか
- `GetProductAction`: バリアント・画像・関連商品が正しく取得されるか
- `AddToCartAction`: カートへの追加、在庫チェック、重複アイテムの数量加算
- `UpdateCartItemAction`: 数量変更、0以下で削除
- `RemoveCartItemAction`: アイテム削除
- `ApplyCouponAction`: 有効/無効クーポンの処理
- `CreateOrderAction`: Order作成の成功・失敗パス

**Controllerテスト（Featureテスト）**
- 商品一覧ページのHTTPレスポンス（フィルタ・検索パラメータ含む）
- 商品詳細ページのHTTPレスポンス（存在しない商品は404）
- カートAPIエンドポイント（追加・更新・削除）
- チェックアウトフロー（住所→配送→確認→完了）
- クーポン適用エンドポイント

### 既存のテストとの整合性
- 既存のPestテスト構成に準拠
- `tests/Feature/` に配置
- ファクトリはLunarPHPのモデルファクトリを活用

## Out of Scope

- 決済処理（Stripe等）→ Phase 3
- ログイン必須チェックアウト → Phase 3
- 会員登録・ログイン後のカートマージ → Phase 3
- 注文履歴ページ（マイアカウント）→ Phase 3
- 多言語対応（日本語/英語切り替え）→ 将来対応
- 商品レビュー・評価機能
- ウィッシュリスト
- SEO最適化（OGタグ・メタタグ等）
- メール通知（注文確認メール等）
- 在庫管理（管理パネル側はLunarPHPが担当）
- localhost:8001 Livewireスターターキット → 別プロジェクトとして構築

## Further Notes

- localhost:8001にLivewireスターターキットを先行構築し、カート・チェックアウト・クーポン・送料の実装パターンを把握してからPhase 2の実装に入る
- クーポン・送料はLunarPHPの設定が必要なため、Livewireキット動作確認後に実装方針を最終決定する
- LunarPHPのOrderパイプラインは複雑なため、CreateOrderActionの実装前にLivewireキットのソースコードを参照する
