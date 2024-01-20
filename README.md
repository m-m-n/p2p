# これは何？

WebプログラマーでもわかるP2Pアプリケーションです  
PHPとhttpでP2Pアプリケーションを実装しました  
http通信なので悪用したらすぐバレると思いますしWebプログラマーが理解するためのサンプルなので悪用しないでね

## 使い方

- `php artisan app:client-start` でクライアントアプリケーションを開始する
- `node-add $HOST:$PORT` コマンドで検索ノードを追加できる
- shareディレクトリーに入れたファイルが他ノードからの検索対象となりアップロードすることができる
  - `refresh-share` コマンドでshareディレクトリーの情報が更新される
- ダウンロード中のファイルはshare/tmpディレクトリーに保存される
  - ダウンロードが完了したファイルはshareディレクトリーに移動される
- `search $SEARCH_WORD` コマンドでファイルを検索することが出来る
  - 該当するものがある場合 `ファイル名 ファイルサイズ ハッシュ値` が表示される
- `download $HASH` コマンドでファイルをダウンロードできる

### 起動方法

```bash
docker compose build
docker compose up -d
docker compose exec app php artisan app:client-start
```

### 最初にやること

- 初回立ち上げた後に初期ノードの登録を行う
  - `p2p > node-add $HOST:$PORT`
