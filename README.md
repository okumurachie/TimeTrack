# 勤怠管理アプリ （TimeTrack）

## 環境構築

### Docker ビルド
- 1.git clone git@github.com:okumurachie/TimeTrack.git
- 2.docker-compose up -d --build

### laravel 環境構築
- 1.docker-compose exec php bash
- 2.composer install
- 3.cp .env.example .env(.env.example ファイルから.env を作成し、環境変数を変更)


        DB_HOST=mysql
        DB_DATABASE=laravel_db
        DB_USERNAME=laravel_user
        DB_PASSWORD=laravel_pass

        MAIL_MAILER=smtp
        MAIL_HOST=mailhog
        MAIL_PORT=1025
        MAIL_FROM_ADDRESS=hello@example.com


- 4.php artisan key:generate
- 5.php artisan migrate
- 6.php artisan db:seed

## 使用技術（実行環境）
- PHP 8.4.8
- Laravel 10.48.29
- MySQL 8.0
- nginx 1.21.1

---
## ユーザーのログイン情報
シーディングで、デフォルトのユーザーを作成
# TimeTrack
