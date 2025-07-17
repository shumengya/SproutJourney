# 使用轻量级的Alpine Linux作为基础镜像
FROM alpine:latest

# 设置工作目录
WORKDIR /server

# 设置环境变量
ENV LANG=C.UTF-8
ENV LC_ALL=C.UTF-8

# 安装必要的运行时依赖
RUN apk add --no-cache \
    bash \
    curl \
    && rm -rf /var/cache/apk/*

# 复制启动脚本
COPY docker-start.sh /server/

# 复制PHP二进制文件和库
COPY bin/php-linux-amd64/ /server/bin/php-linux-amd64/

# 复制源代码
COPY src/ /server/src/

# 复制英文插件（避免中文编码问题）
COPY plugins/DesertPyramid/ /server/plugins/DesertPyramid/
COPY plugins/Dungeons/ /server/plugins/Dungeons/
COPY plugins/EnderStructure/ /server/plugins/EnderStructure/
COPY plugins/ItemNBTViewer/ /server/plugins/ItemNBTViewer/
COPY plugins/LiteCore/ /server/plugins/LiteCore/
COPY plugins/Mineshaft/ /server/plugins/Mineshaft/
COPY plugins/MobSpawn/ /server/plugins/MobSpawn/
COPY plugins/relic/ /server/plugins/relic/
COPY plugins/Stronghold/ /server/plugins/Stronghold/
COPY plugins/ChatLoggerPlugin/ /server/plugins/ChatLoggerPlugin/

# 创建必要的目录
RUN mkdir -p /server/worlds /server/players /server/crashdumps

# 创建空的配置文件（如果不存在）
RUN touch /server/pocketmine.yml /server/server.properties /server/banned-ips.txt /server/banned-cids.txt

# 设置执行权限
RUN chmod +x /server/bin/php-linux-amd64/bin/php \
    && chmod +x /server/docker-start.sh

# 创建数据目录的挂载点
VOLUME ["/server/worlds", "/server/players", "/server/plugins"]

# 暴露默认的MinecraftPE端口
EXPOSE 19132/udp

# 设置启动命令
CMD ["/server/docker-start.sh"] 