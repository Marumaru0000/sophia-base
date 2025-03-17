# Self Ordering System

[![packagist](https://badgen.net/packagist/v/revolution/self-ordering)](https://packagist.org/packages/revolution/self-ordering)
![php](https://badgen.net/packagist/php/revolution/self-ordering)
![tests](https://github.com/kawax/self-ordering/workflows/tests/badge.svg)
[![Maintainability](https://api.codeclimate.com/v1/badges/789874bd174d23ea7fb5/maintainability)](https://codeclimate.com/github/kawax/self-ordering/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/789874bd174d23ea7fb5/test_coverage)](https://codeclimate.com/github/kawax/self-ordering/test_coverage)

## 動作環境
- PHP ^8.2
- Laravel ^11.x
- Livewire 3.x
- Tailwind 3.x

## バージョン
| ver | PHP       | Laravel  |
|-----|-----------|----------|
| 1.x | ^7.4/^8.0 | 8.x      |
| 2.x | ^8.0      | 9.x/10.x |
| 3.x | ^8.1      | 10.x     |
| 4.x | ^8.2      | 11.x     |

## インストール
「Laravelでセルフオーダーシステムを作るためのスターターキット」なので必ずLaravelの新規プロジェクトを作るところから始めてください。`ordering:install`コマンドでファイルが上書きされます。

```shell
curl -s "https://laravel.build/self-ordering-project" | bash
cd ./self-ordering-project

composer require revolution/self-ordering

php artisan ordering:install --vercel
# Vercel用のファイルが不要なら--vercelを付けない
php artisan ordering:install

npm install && npm run build

./vendor/bin/sail up -d
```

http://localhost/order

### .env
```
ORDERING_MENU_DRIVER=array
ORDERING_ADMIN_PASSWORD=
ORDERING_DESCRIPTION=""

ORDERING_MICROCMS_API_KEY=
ORDERING_MICROCMS_ENDPOINT=https://
```

### routes/web.php
`/`のルートはQRコード表示に使う。

```php
//Route::get('/', function () {
//    return view('welcome');
//});

Route::view('/', 'ordering::help');
```

インストール後にページを増やすのは自由。

### アンインストール
新規プロジェクトにインストールしているはずなのでこのパッケージだけアンインストールはできません。プロジェクトごと終了。

## クイックスタート
上記の手順でインストール後に必要なことは「メニューデータの管理方法」と「注文情報の送信先」
を決める。

### メニューデータの管理方法
店舗側でメニューを変更するなら [microCMS](https://microcms.io/) が一番簡単だろうからmicroCMSにアカウントを作って進める。

### 注文情報の送信先
メールやLINE Notifyなど「注文された時にすぐに気付ける方法」を選ぶ。

### 店舗側での作業
- 店内のテーブルすべてに番号を振る。
- QRコードをテーブルに掲示。
- 「セルフオーダーの使い方」を掲示。こちらで用意したいけどまだない。
- セルフオーダーから注文が入った時のオペレーションを確認。

## 仕様
### ページ
- ユーザー向けの注文ページ
  - QRコードを読み込んで表示。テーブル番号を入力。
  - メニューを選択→注文確認画面→決済を使うなら支払い→注文を送信→注文履歴画面。
- 店舗向けのダッシュボード
  - デフォルトでは簡易的なパスワード認証。

### メニューデータ
- array（デフォルト）
- microCMS
- Googleスプレッドシート
- Contentful
- データベース
- POS

### 注文送信先
基本的にはLaravelの通知機能を使う。

- メール
- LINE Notify
- POS

### 決済
- レジで後払い
- PayPay

## CONTRIBUTING

### コーディング規約
- PSR-12への過渡期なのでLaravel(PSR-2ベース)とPSR-12の混在。
- StyleCI(laravelプリセット)とPhpStorm(Laravelプリセット改)の自動フォーマットに合わせる。

### このパッケージのローカルでの開発方法

starter側のcomposer.jsonでローカルのパッケージを使うように指定。

```json
    "repositories": [
        {
            "type": "path",
            "url": "../self-ordering"
        }
    ],
```
```json
    "require": {
        "revolution/self-ordering": "*"
    },
```

Sailを使うならdocker-compose.ymlのvolumesも変更。
```yaml
        volumes:
            - '.:/var/www/html'
            - '../self-ordering:/var/www/self-ordering'
```

```
composer install

cp .env.example .env
php artisan key:generate

npm i && npm run build
```

starter側を起動しながらパッケージ側で作業する。
```
./vendor/bin/sail up -d
```

## LICENCE
MIT
