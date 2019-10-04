# One-Click-Viewer

[![Build Status](https://travis-ci.org/ryota-adr/one-click-viewer.svg?branch=master)](https://travis-ci.org/ryota-adr/one-click-viewer)
[![codecov](https://codecov.io/gh/ryota-adr/one-click-viewer/branch/master/graph/badge.svg)](https://codecov.io/gh/ryota-adr/one-click-viewer)

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

defines.sample.phpをdefines.phpに変更

```
<?php
define('APP_HOST', 'http(s)://your-app-host');
```

```
cd test
composer dump-autoload --optimize
```

<p>上記を終えてフォームのテキストボックスに完全修飾クラス名かphpファイルのパスを入れるとコードが表示されます。</p>