#!/bin/bash

# 项目配置
cp .env.example .env
chmod -R 755 storage
chown -R www-data:www-data storage
php artisan key:generate
sed -i "s!DB_HOST=127.0.0.1!DB_HOST=$MYSQL_HOST!" .env
sed -i "s!DB_PORT=3306!DB_PORT=$MYSQL_PORT!" .env
sed -i "s!DB_DATABASE=code6!DB_DATABASE=$MYSQL_DATABASE!" .env
sed -i "s!DB_USERNAME=!DB_USERNAME=$MYSQL_USERNAME!" .env
sed -i "s!DB_PASSWORD=!DB_PASSWORD=$MYSQL_PASSWORD!" .env
php artisan migrate --force

# 配置任务调度
service cron start
echo "* * * * * cd /var/www/html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1" >> /etc/cron.d/code6
crontab /etc/cron.d/code6

# 配置 Apache
a2enmod rewrite
service apache2 start

tail -f /dev/null
