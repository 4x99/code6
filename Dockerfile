FROM php:7.4-apache

EXPOSE 80

ENV MYSQL_HOST="mysql"
ENV MYSQL_PORT="3306"
ENV MYSQL_DATABASE="code6"
ENV MYSQL_USERNAME=""
ENV MYSQL_PASSWORD=""
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# 复制代码
COPY . /var/www/html
COPY docker-entrypoint.sh docker-entrypoint.sh
WORKDIR /var/www/html

# 使用阿里镜像并安装包
RUN mv /etc/apt/sources.list /etc/apt/sources.list.bak;\
echo 'deb http://mirrors.aliyun.com/debian buster main' >> /etc/apt/sources.list;\
echo 'deb http://mirrors.aliyun.com/debian buster-updates main' >> /etc/apt/sources.list;\
apt-get update;\
apt-get install -y --allow-downgrades zip cron vim zlib1g=1:1.2.11.dfsg-1 zlib1g-dev libpng-dev;\
rm -rf /var/lib/apt/lists/*;\
# 安装 PHP 扩展
docker-php-ext-install pdo_mysql;\
docker-php-ext-install gd;\
# 配置 Web 路径
sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf;\
sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf;\
# 修改时区
rm -rf /etc/localtime;\
ln -s /usr/share/zoneinfo/PRC /etc/localtime;\
# 设置别名
echo "alias ll='ls -l'" >> /etc/bash.bashrc;\
# Vim 编码配置
echo 'set fileencodings=utf-8' >> /etc/vim/vimrc;\
echo 'set termencoding=utf-8' >> /etc/vim/vimrc;\
echo 'set encoding=utf-8' >> /etc/vim/vimrc;\
# 安装 Composer 及项目依赖包
curl -sO https://mirrors.aliyun.com/composer/composer.phar;\
chmod +x composer.phar;\
mv composer.phar /usr/local/bin/composer;\
composer config repo.packagist composer https://mirrors.aliyun.com/composer/;\
composer install --no-dev --no-progress --optimize-autoloader;\
chmod +x docker-entrypoint.sh;

ENTRYPOINT /bin/bash docker-entrypoint.sh
