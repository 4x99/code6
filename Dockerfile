FROM php:7.4-apache

EXPOSE 80

ENV MYSQL_HOST="172.17.0.1"
ENV MYSQL_PORT="3306"
ENV MYSQL_DATABASE="code6"
ENV MYSQL_USERNAME=""
ENV MYSQL_PASSWORD=""

# 复制代码
COPY . /var/www/html
WORKDIR /var/www/html

# 使用阿里镜像并安装包
RUN mv /etc/apt/sources.list /etc/apt/sources.list.bak
RUN echo 'deb http://mirrors.aliyun.com/debian buster main' >> /etc/apt/sources.list
RUN echo 'deb http://mirrors.aliyun.com/debian buster-updates main' >> /etc/apt/sources.list
RUN apt-get update && apt-get install -y --allow-downgrades zip cron vim zlib1g=1:1.2.11.dfsg-1 zlib1g-dev libpng-dev
RUN rm -rf /var/lib/apt/lists/* && apt-get clean

# 安装 PHP 扩展
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install gd

# 配置 Web 路径
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf
RUN sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 修改时区
RUN rm -rf /etc/localtime
RUN ln -s /usr/share/zoneinfo/PRC /etc/localtime

# 设置别名
RUN echo "alias ll='ls -l'" >> /etc/bash.bashrc

# Vim 编码配置
RUN echo 'set fileencodings=utf-8' >> /etc/vim/vimrc
RUN echo 'set termencoding=utf-8' >> /etc/vim/vimrc
RUN echo 'set encoding=utf-8' >> /etc/vim/vimrc

# 安装 Composer 及项目依赖包
RUN curl -sO https://mirrors.aliyun.com/composer/composer.phar
RUN chmod +x composer.phar
RUN mv composer.phar /usr/local/bin/composer
RUN composer config repo.packagist composer https://mirrors.aliyun.com/composer/
RUN composer install --no-dev --no-progress --optimize-autoloader

RUN chmod +x docker-entrypoint.sh
ENTRYPOINT /bin/bash docker-entrypoint.sh
