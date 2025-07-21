# WP Smart Slug

日本語のURL（スラッグ）を自動的に英語に翻訳するWordPressプラグインです。長いbase64エンコードされたURLを防ぎ、SEOフレンドリーなURLを生成します。

## 機能

- **自動翻訳**: 投稿、固定ページ、メディアのスラッグを自動的に英語に翻訳
- **複数の翻訳サービス対応**: MyMemory、LibreTranslate、DeepL API Freeに対応
- **簡潔なスラッグ生成**: 1-2単語の短くて分かりやすいスラッグを生成
- **管理画面**: 直感的な設定画面でサービスとAPIキーを管理
- **一括処理**: 既存コンテンツを一括でスラッグ変換
- **国際化対応**: 日本語インターフェース完備

## 対応翻訳サービス

### MyMemory Translation API
- 無料で1日5,000リクエストまで利用可能
- APIキー不要（基本利用）
- 登録不要ですぐに使用開始

### LibreTranslate
- オープンソースの翻訳サービス
- 自分のサーバーでホスト可能
- APIキーはインスタンスによって必要/不要

### DeepL API Free
- 高品質な翻訳結果
- 月間50万文字まで無料
- APIキーが必要（要登録）

## インストール

### GitHubリリースから（推奨）

1. [Releases](https://github.com/wadatch/wp-smart-slug/releases)から最新の`wp-smart-slug-x.x.x.zip`をダウンロード
2. WordPress管理画面で「プラグイン > 新規追加 > プラグインのアップロード」
3. ダウンロードしたZIPファイルをアップロードして有効化
4. 「設定 > WP Smart Slug」で翻訳サービスを設定

### 手動ビルド

開発版を使用する場合：

```bash
git clone https://github.com/wadatch/wp-smart-slug.git
cd wp-smart-slug
make build
# dist/wp-smart-slug-x.x.x.zip が生成されます
```

## 使用方法

1. **基本設定**
   - WordPress管理画面の「設定 > WP Smart Slug」を開く
   - 使用する翻訳サービスを選択
   - 必要に応じてAPIキーやホストURLを入力

2. **機能有効化**
   - 投稿、固定ページ、メディアの自動翻訳を個別に有効/無効設定
   - 既存コンテンツは一括処理機能で変換

3. **動作確認**
   - 日本語タイトルで新規投稿を作成
   - スラッグが自動的に英語に変換されることを確認

## 開発・ビルド

### 必要な環境
- PHP 7.4 以上
- Composer
- WordPress 5.0 以上（開発・テスト用）

### 開発環境のセットアップ
```bash
# リポジトリをクローン
git clone https://github.com/wadatch/wp-smart-slug.git
cd wp-smart-slug

# 依存関係をインストール
composer install

# または
make dev-setup
```

### 開発コマンド

```bash
# テスト実行
make test
composer test

# コード品質チェック
make lint
composer phpcs

# コード自動修正
make fix
composer phpcbf

# 翻訳ファイル生成
make i18n

# プロダクションビルド
make build
./build.sh

# ビルドファイルのクリーンアップ
make clean
```

### リリースプロセス

#### 自動リリース（推奨）

バージョンタグをプッシュすると自動的にGitHub Actionsがビルド・リリースを実行：

```bash
# 新しいバージョンタグを作成
git tag v1.0.1
git push origin v1.0.1

# GitHub Actionsが自動実行され、以下が作成されます：
# - GitHub Releasesページにリリース
# - ビルド済みZIPファイル
# - チェックサムファイル（SHA256, MD5）
```

#### 手動ビルド

ローカルでビルドする場合：

```bash
./build.sh
# または
make build
```

ビルドスクリプトは以下の処理を実行します：

1. **品質チェック**
   - PHPUnitテストの実行
   - コード品質チェック（PHPCS）
   - 自動コード修正

2. **依存関係の最適化**
   - プロダクション用のComposer依存関係をインストール
   - オートローダーの最適化

3. **配布用ファイル作成**
   - 不要ファイルを除外したZIP作成
   - SHA256とMD5チェックサム生成
   - `dist/`ディレクトリに出力

### プロジェクト構造

```
wp-smart-slug/
├── admin/                 # 管理画面
├── assets/               # CSS/JavaScript
├── includes/             # コア機能
│   ├── Core/            # プラグインコア
│   ├── Hooks/           # WordPressフック
│   └── Translation/     # 翻訳システム
├── languages/           # 翻訳ファイル
├── tests/              # PHPUnitテスト
├── build.sh           # ビルドスクリプト
├── Makefile           # 開発コマンド
└── wp-smart-slug.php  # メインプラグインファイル
```

### テスト

```bash
# 全テスト実行
composer test

# 単体テスト（モック使用）
./vendor/bin/phpunit

# カバレッジレポート生成（要Xdebug）
./vendor/bin/phpunit --coverage-html coverage/
```

### コード品質

- WordPress Coding Standards準拠
- PSR-4オートローディング
- PHPUnit単体テスト
- GitHub Actions CI/CD

## ライセンス

MIT License - 詳細は[LICENSE](LICENSE)をご確認ください。

## 貢献

プルリクエストやIssueを歓迎します。開発に参加する場合は：

1. このリポジトリをフォーク
2. 機能ブランチを作成 (`git checkout -b feature/amazing-feature`)
3. 変更をコミット (`git commit -m 'Add amazing feature'`)
4. ブランチにプッシュ (`git push origin feature/amazing-feature`)
5. プルリクエストを作成

## サポート

- [GitHub Issues](https://github.com/wadatch/wp-smart-slug/issues) - バグレポート・機能リクエスト
- [GitHub Discussions](https://github.com/wadatch/wp-smart-slug/discussions) - 質問・ディスカッション
