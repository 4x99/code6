# Docker-compose 部署
## 克隆代码
```
git clone https://github.com/4x99/code6.git
```

---

## 修改配置
```
cd code6
cp .env.docker-compose.example .env.docker-compose
vim .env.docker-compose
```

请根据实际情况修改配置，这里 Web 端口以 `666` 为例：
```
# Web 映射到宿主机的端口
PORT=666

# MySQL 映射到宿主机的端口
MYSQL_PORT=3306

# MySQL 数据库名
MYSQL_DATABASE=code6

# MySQL 用户名
MYSQL_USER=

# MySQL 密码
MYSQL_PASSWORD=

# MySQL 挂载到宿主机的目录
MYSQL_VOLUME_PATH=
```

---

## 启动容器
启动容器，码小六将自动连接 MySQL 并导入数据表：
```
docker-compose --env-file .env.docker-compose up -d --build
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

## 访问系统
```
http://<宿主机 IP>:666
```

---

## 配置令牌与任务
进入系统后请前往 `[ 令牌配置 ]` 和 `[ 任务配置 ]` 模块进行配置，配置完毕即可使用！
