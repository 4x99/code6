
## 使用docker compose 安装code6
使用以下进行安装
1. 手动安装docker和docker compose
2. 下载项目，如：`git clone https://github.com/4x99/code6.git`
3. code6目录下执行以下命令
`docker compose up -d --build`
2. 进code6容器添加账号（邮箱和密码需要自己修改）
```
docker exec -it code6 /bin/bash
php artisan code6:user-add test@test.com test
```
3. 访问127.0.0.1（或服务器ip）登录即可


## 改动或增加的文件
1. 增加docker-compose.yaml code6容器和MySQL容器(arm也可以运行)
2. 修改Dockerfile 优化docker层，增加MySQL默认的密码，wait-for-it.sh
3. 增加wait-for-it.sh 用于等待MySQL容器完成启动
4. ~~增加init.mysql 用于创建code6数据库~~（php artisan migrate会创建表，这里就不创建了）


## 在部署前修改密码或端口【请务必修改密码】
1. 修改MySQL的密码
```
修改docker-compose.yaml和Dockerfile中的密码
root的密码5ZXC7BR7m04tJ5Mr（Dockerfile没有这个）
code的密码8H5quv2130z96AzQ
```
2. 修改MySQL的端口
```
修改docker-compose.yaml
12行      # 宿主:容器，修改宿主的端口，如3307:3306
      - 3306:3306

```
3. 修改web的端口
```
修改docker-compose.yaml
41行，修改宿主的端口，如666:80
      - 80:80
```
改完再部署即可
