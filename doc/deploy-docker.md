# Docker 部署
## 创建 MySQL 实例
Docker 镜像不包含 MySQL 实例，请安装 MySQL、创建数据库并授权。

---

## 克隆代码
```
git clone https://github.com/4x99/code6.git
```

---

## 使用 Dockerfile 创建镜像
```
cd code6
docker build -t code6 .
```

---

## 启动容器
宿主机映射端口 `666` 与 MySQL 连接参数请根据情况修改，容器启动将自动连接 MySQL 并导入数据表：
```
docker run -d \
-p 666:80 \
-e MYSQL_HOST=172.17.0.1 \
-e MYSQL_PORT=3306 \
-e MYSQL_DATABASE=code6 \
-e MYSQL_USERNAME=xxx \
-e MYSQL_PASSWORD=xxxxxx \
--name code6-server code6
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
