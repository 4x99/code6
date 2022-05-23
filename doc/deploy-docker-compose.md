# Docker-compose 部署
## 克隆代码
```
git clone https://github.com/4x99/code6.git
```

---

## 修改配置
```
vim .env.docker-compose
```
根据需要，修改相关配置：
```
TZ=Asia/Shanghai
MYSQL_HOST=mysql
MYSQL_DATABASE=code6
# Docker-compose 需要此环境变量
MYSQL_USER=code6_username
MYSQL_USERNAME=code6_username
MYSQL_PASSWORD=code6_password

# MySQL 端口映射(宿主机:容器)
MYSQL_PORT=3306
MYSQL_PORT_CONTAINER=3306

# 端口映射(宿主机:容器)
PORT=666
PORT_CONTAINER=80
```

---

## 启动容器
宿主机映射端口 `666` 与 MySQL 连接参数请根据情况修改，容器启动将自动连接 MySQL 并导入数据表：
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
