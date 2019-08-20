![sample](https://github.com/ryota-adr/one-click-viewer/blob/master/one-click-viewer.gif)

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

<p>.env.exampleを.envに変更</p>

```text:.env
AUTOLOADERPATH=path/to/vendor/autoloader.php
```

<p>上記を終えてフォームのテキストボックスに完全修飾クラス名を入れるとコードが表示されます。</p>