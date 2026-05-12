FROM php:8.2-apache

# 啟用 MySQL 支援 (mysqli)
RUN docker-php-ext-install mysqli

# 複製所有檔案到網頁目錄
COPY . /var/www/html/

# 設定 Apache 的入口檔案為 index.php
RUN echo "DirectoryIndex index.php" > /etc/apache2/conf-available/docker-index.conf && \
    a2enconf docker-index

# 啟用 Apache 的 mod_rewrite 模組
RUN a2enmod rewrite

EXPOSE 80