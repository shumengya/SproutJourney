# PocketMine服务器 Docker部署说明

## 前置要求
- 安装了Docker和Docker Compose
- Linux AMD64架构

## 快速开始

### 方法一：使用Docker Compose（推荐）
```bash
# 构建并启动服务器
docker-compose up -d

# 查看日志
docker-compose logs -f

# 停止服务器
docker-compose down
```

### 方法二：使用Docker命令
```bash
# 构建镜像
docker build -t pocketmine-server .

# 运行容器
docker run -d \
  --name pocketmine-server \
  -p 19132:19132/udp \
  -v $(pwd)/worlds:/server/worlds \
  -v $(pwd)/players:/server/players \
  -v $(pwd)/plugins:/server/plugins \
  pocketmine-server

# 查看日志
docker logs -f pocketmine-server

# 停止容器
docker stop pocketmine-server
```

## 数据持久化
Docker Compose配置会自动将以下目录映射到主机：
- `worlds/` - 游戏世界数据
- `players/` - 玩家数据
- `plugins/` - 插件文件
- 配置文件（`pocketmine.yml`, `server.properties` 等）

## 端口说明
- `19132/udp` - MinecraftPE默认端口

## 管理命令
```bash
# 进入容器执行命令
docker-compose exec pocketmine-server bash

# 重启服务器
docker-compose restart

# 查看资源使用情况
docker stats pocketmine-server
```

## 注意事项
1. 确保主机的19132端口没有被其他程序占用
2. 首次启动可能需要较长时间来初始化世界
3. 容器会自动重启，除非手动停止
4. 所有数据都会持久保存在主机上 