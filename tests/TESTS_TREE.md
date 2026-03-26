# テスト一覧（ツリー形式）

```
tests/
├── Browser/
│   ├── WelcomeTest
│   │   └── has welcome page
│   ├── CartPageTest
│   │   ├── カートページが正しく表示されること
│   │   ├── カートが空の時に空メッセージが表示されること
│   │   ├── 商品追加後に送料オプションが表示されること
│   │   ├── クーポン入力フォームが表示されること
│   │   └── 有効なクーポンを適用した時に割引が表示されること
│   ├── CheckoutPageTest
│   │   ├── カートが空の時、チェックアウトページがカートにリダイレクトされること
│   │   ├── カートページに「レジに進む」ボタンが表示されること
│   │   ├── チェックアウト住所ページが正しく表示されること
│   │   ├── 住所を入力して配送方法ページに進めること
│   │   ├── 配送方法を選択して注文確認ページに進めること
│   │   └── 注文確認から注文完了まで全フローが動作すること
│   └── ProductsPageTest
│       ├── 商品一覧ページが正しく表示されること
│       ├── ストアフロントのヘッダーナビゲーションが表示されること
│       ├── 商品カードに商品名・ブランド・価格が表示されること
│       ├── 商品がない場合に空状態メッセージが表示されること
│       └── 商品カードをクリックした時、商品詳細ページへ遷移できること
├── Feature/
│   ├── Actions/
│   │   ├── ApplyCouponTest
│   │   │   ├── 有効なクーポンコードの時、カートにクーポンが設定されtrueが返ること
│   │   │   ├── 無効なクーポンコードの時、カートが変更されずfalseが返ること
│   │   │   └── 期限切れクーポンの時、falseが返ること
│   │   ├── GetProductTest
│   │   │   ├── 公開済み商品のslugを指定した時、商品を取得できること
│   │   │   ├── 下書き商品のslugを指定した時、商品が見つからないこと
│   │   │   ├── バリアントのSKUと在庫数が取得できること
│   │   │   ├── バリアントのオプション情報が取得できること
│   │   │   └── 関連商品が取得できること
│   │   └── GetProductsTest
│   │       ├── 公開済み商品をページネーション付きで取得できること
│   │       ├── デフォルトで12件ずつ取得すること
│   │       ├── ページ番号を指定して取得できること
│   │       ├── price_ascを指定した時、価格が安い順で返すこと
│   │       ├── price_descを指定した時、価格が高い順で返すこと
│   │       ├── name_ascを指定した時、名前のアルファベット順で返すこと
│   │       ├── キーワードを指定した時、名前に含む商品のみ返すこと
│   │       ├── キーワードが一致しない時、空の結果を返すこと
│   │       ├── ブランドを指定した時、そのブランドの商品のみ返すこと
│   │       ├── コレクションを指定した時、そのコレクションの商品のみ返すこと
│   │       └── ブランドフィルタとソートを組み合わせた時、正しく動作すること
│   └── Controllers/
│       ├── CheckoutControllerTest
│       │   ├── カートが空の時、GET /checkout/addressはカートページにリダイレクトされること
│       │   ├── カートが空の時、GET /checkout/shippingはカートページにリダイレクトされること
│       │   ├── 有効な住所データをPOSTした時、Checkout/Shippingにリダイレクトされること
│       │   ├── 有効な住所データをPOSTした時、カートに住所が保存されること
│       │   ├── 有効な配送方法identifierをPOSTした時、カートに保存されリダイレクトされること
│       │   ├── identifierが欠けている時、POST /checkout/shippingがバリデーションエラーを返すこと
│       │   ├── 必須フィールドが欠けている時、POST /checkout/addressがバリデーションエラーを返すこと（6ケース）
│       │   ├── カートが空の時、POST /checkout/addressはカートページにリダイレクトされること
│       │   ├── カートが空の時、POST /checkout/shippingはカートページにリダイレクトされること
│       │   ├── カートに商品がある時、GET /checkout/addressがアドレスページを表示すること
│       │   ├── カートに商品がある時、GET /checkout/shippingが配送オプションを含むページを表示すること
│       │   ├── カートに商品と配送情報がある時、GET /checkout/confirmが確認ページを表示すること
│       │   ├── 有効なカートの時、POST /checkout/confirmで注文が作成されCheckout/Completeにリダイレクトされること
│       │   ├── 有効なカートの時、POST /checkout/confirmで注文確定後にカートがクリアされること
│       │   ├── 有効な注文の時、GET /checkout/completeが注文完了ページを表示すること
│       │   ├── カートが空の時、GET /checkout/confirmはカートページにリダイレクトされること
│       │   └── カートが空の時、POST /checkout/confirmはカートページにリダイレクトされること
│       ├── CartControllerTest
│       │   ├── /cartにアクセスした時、カートページが表示されること
│       │   ├── カートが空の時、itemsが空配列であること
│       │   ├── バリアントIDと数量を送信した時、カートにアイテムが追加されること
│       │   ├── 同じバリアントを2回追加した時、数量が加算されること
│       │   ├── カートラインIDと数量を送信した時、数量が更新されること
│       │   ├── カートラインIDを指定した時、アイテムが削除されること
│       │   ├── カートに追加後、GETでitemsに商品名・数量・小計が含まれること
│       │   ├── カートに追加後、GETでtotalが含まれること
│       │   ├── 有効なクーポンコードを送信した時、カートに適用されリダイレクトされること
│       │   ├── 無効なクーポンコードを送信した時、エラーがセッションに入ること
│       │   ├── クーポン適用後にGETするとcouponCodeとdiscountTotalが含まれること
│       │   ├── クーポンが未適用の時、GETでcouponCodeがnullであること
│       │   ├── GETでshippingOptionsが配列として返されること
│       │   ├── 送料オプションが登録されている時、identifier・name・priceが含まれること
│       │   ├── クーポンが適用されている時、DELETE /cart/couponでクーポンが削除されること
│       │   └── カートが存在しない時もDELETE /cart/couponでリダイレクトされること
│       ├── Shipping/
│       │   └── FlatRateShippingTest
│       │       ├── デフォルト通貨が存在しない時、nextを呼び出してカートを返すこと
│       │       └── 税クラスが存在しない時、nextを呼び出してカートを返すこと
│       ├── ProductControllerTest
│       │   ├── /productsにアクセスした時、商品一覧ページが表示されること
│       │   ├── 公開済み商品の一覧がpropsに含まれること
│       │   ├── 下書き商品は一覧に含まれないこと
│       │   ├── ページネーションが動作すること
│       │   ├── brandパラメータを指定した時、フィルタが適用されること
│       │   ├── searchパラメータを指定した時、検索が適用されること
│       │   ├── sortパラメータを指定した時、フィルタ情報がpropsに含まれること
│       │   ├── /products/{slug}にアクセスした時、商品詳細ページが表示されること
│       │   ├── 存在しないslugで商品詳細にアクセスした時、404を返すこと
│       │   ├── 商品詳細propsにバリアント情報（SKU・価格・在庫）が含まれること
│       │   ├── purchasableがalwaysのバリアントの時、inStockがtrueになること
│       │   ├── 在庫切れバリアントの時、inStockがfalseになること
│       │   ├── 商品詳細propsに全画像一覧が含まれること
│       │   ├── 商品に画像がある時、imagesにURLが含まれること
│       │   ├── バリアントにオプション値がある時、optionsにname・valueが含まれること
│       │   └── 商品詳細propsに関連商品が含まれること
│       ├── SessionControllerTest
│       │   ├── renders login page
│       │   ├── may create a session
│       │   ├── may create a session with remember me
│       │   ├── redirects to two factor challenge when enabled
│       │   ├── fails with invalid credentials
│       │   ├── requires email
│       │   ├── requires password
│       │   ├── may destroy a session
│       │   ├── redirects authenticated users away from login
│       │   ├── throttles login attempts after too many failures
│       │   ├── clears rate limit after successful login
│       │   └── dispatches lockout event when rate limit is reached
│       ├── UserControllerTest
│       │   ├── renders registration page
│       │   ├── may register a new user
│       │   ├── requires name
│       │   ├── requires email
│       │   ├── requires valid email
│       │   ├── requires unique email
│       │   ├── requires password
│       │   ├── requires password confirmation
│       │   ├── requires matching password confirmation
│       │   ├── may delete user account
│       │   ├── requires password to delete account
│       │   ├── requires correct password to delete account
│       │   └── redirects authenticated users away from registration
│       ├── UserEmailResetNotificationTest
│       │   ├── renders forgot password page
│       │   ├── may send password reset notification
│       │   ├── returns generic message for non-existent email
│       │   ├── requires email
│       │   ├── requires valid email format
│       │   └── redirects authenticated users away from forgot password
│       ├── UserEmailVerificationNotificationControllerTest
│       │   ├── renders verify email page
│       │   ├── redirects verified users to dashboard
│       │   ├── may send verification notification
│       │   └── redirects verified users when sending notification
│       ├── UserEmailVerificationTest
│       │   ├── may verify email
│       │   ├── redirects to dashboard if already verified
│       │   └── requires valid signature
│       ├── UserPasswordControllerTest
│       │   ├── renders reset password page
│       │   ├── may reset password
│       │   ├── fails with invalid token
│       │   ├── fails with non-existent email
│       │   ├── requires email
│       │   ├── requires password
│       │   ├── requires password confirmation
│       │   ├── requires matching password confirmation
│       │   ├── renders edit password page
│       │   ├── may update password
│       │   ├── requires current password to update
│       │   ├── requires correct current password to update
│       │   ├── requires new password to update
│       │   └── redirects authenticated users away from reset password
│       ├── UserProfileControllerTest
│       │   ├── renders profile edit page
│       │   ├── may update profile information
│       │   ├── resets email verification when email changes
│       │   ├── keeps email verification when email stays the same
│       │   ├── requires name
│       │   ├── requires email
│       │   ├── requires valid email
│       │   ├── requires unique email except own
│       │   └── allows keeping same email
│       └── UserTwoFactorAuthenticationControllerTest
│           ├── renders two factor authentication page
│           ├── shows two factor disabled when not enabled
│           └── shows two factor enabled when enabled
└── Unit/
    ├── Actions/
    │   ├── CreateUserTest
    │   │   └── may create a user
    │   ├── CreateUserPasswordTest
    │   │   ├── may create a new user password
    │   │   ├── returns invalid token status for incorrect token
    │   │   ├── returns invalid user status for non-existent email
    │   │   └── updates remember token when resetting password
    │   ├── CreateUserEmailResetNotificationTest
    │   │   ├── may send password reset notification
    │   │   ├── returns throttled status when too many attempts
    │   │   └── returns invalid user status for non-existent email
    │   ├── CreateUserEmailVerificationNotificationTest
    │   │   └── may send email verification notification
    │   ├── DeleteUserTest
    │   │   └── may delete a user
    │   ├── UpdateUserPasswordTest
    │   │   └── may update a user password
    │   └── UpdateUserTest
    │       ├── may update a user
    │       ├── resets email verification and sends notification when email changes
    │       └── keeps email verification and does not send notification when email stays the same
    ├── Middleware/
    │   ├── HandleAppearanceTest
    │   │   ├── shares appearance cookie value with views
    │   │   ├── defaults to system when appearance cookie not present
    │   │   ├── handles light appearance
    │   │   └── handles system appearance
    │   └── HandleInertiaRequestsTest
    │       ├── shares app name from config
    │       ├── shares null user when guest
    │       ├── shares authenticated user data
    │       ├── defaults sidebarOpen to true when no cookie
    │       ├── sets sidebarOpen to true when cookie is true
    │       ├── sets sidebarOpen to false when cookie is false
    │       └── includes parent shared data
    ├── Models/
    │   └── UserTest
    │       └── to array
    ├── Rules/
    │   └── ValidEmailTest
    │       ├── it works with valid email (36ケース)
    │       └── it fails with invalid email (26ケース)
    └── ArchTest
        ├── preset → php
        ├── preset → strict
        ├── preset → security → ignoring assert
        └── controllers
```
