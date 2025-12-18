# richarvey/nginx-php-fpmをベースとする
FROM richarvey/nginx-php-fpm:latest

# アプリケーション配置ディレクトリ
WORKDIR /var/www/html

# アプリケーションコードをコンテナにコピー
COPY . .

# Image config
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1

# Composer で PHP 依存関係をインストール
RUN composer install --no-dev --optimize-autoloader

# Install npm for building frontend assets
RUN apk update && apk add --no-cache npm

# フロントエンドをビルド
RUN npm install && npm run build

# richarvey ベースイメージの起動スクリプト
CMD ["/start.sh"]
