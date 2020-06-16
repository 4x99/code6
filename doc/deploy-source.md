# 源码部署
## 克隆代码
```
git clone https://github.com/4x99/code6.git <项目目录>
```

---

## 配置 Apache
请确保已开启 `mod_rewrite` 模块
```
<VirtualHost *:80>
    ServerName <ServerName>
    DocumentRoot "<项目目录>/public"
</VirtualHost>
```

---

## 下载依赖包
安装 Composer：
```
curl -O https://mirrors.aliyun.com/composer/composer.phar
chmod +x composer.phar
mv composer.phar /usr/local/bin/composer
```

配置阿里云镜像：
```
composer config repo.packagist composer https://mirrors.aliyun.com/composer/
```

下载项目依赖包：
```
cd <项目目录> && composer install --no-dev --optimize-autoloader
```

---

## 项目配置
设置目录权限：
```
chmod -R 755 storage
chown -R <用户名>:<组名> storage
```

创建配置文件：
```
cp .env.example .env
```

生成应用密钥：
```
php artisan key:generate
```

创建并配置数据库：
```
vim .env
```
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

自动生成表结构：
```
php artisan migrate
```

---

## 配置任务调度
```
crontab -e -u <用户>
```

```
* * * * * cd <项目目录> && php artisan schedule:run >> /dev/null 2>&1
```

---

## 创建用户
```
docker exec -it code6-server /bin/bash
php artisan code6:user-add <邮箱> <密码>
```

如需查看用户列表或删除用户请执行：
```
php artisan code6:user-list
php artisan code6:user-delete <邮箱>
```

---

## 配置令牌与任务
进入系统后请前往 `[ 令牌配置 ]` 和 `[ 任务配置 ]` 模块进行配置，配置完毕即可使用！
