![sample](http://ryota01.com/github/one-click-viewer.gif)

## 説明
1クリックで外部クラスのコードを表示できるPHPコードビューワー

## 機能
以下のコードを1クリックで表示します。

+ useしたクラス
+ 継承したクラス
+ 実装したインターフェース
+ ドキュメンテーションに書かれたクラス
+ メソッドで型宣言されたクラス
+ メソッド内に書かれたクラス

以下のコードの箇所に1クリックで移動します。

+ 同クラスのプロパティ
+ 同クラスのメソッド
+ 同クラスの定数
+ 外部クラスのプロパティ
+ 外部クラスのメソッド
+ 外部クラスの定数

## 使い方

```php:index.php
$autoloader = '\vendor\autoloader.php';
```

<p>autoloader.phpのパスを代入してから、フォームのテキストボックスに名前空間＋クラス名を入れるとPHPファイルが表示されます。</p>