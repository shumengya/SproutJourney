#!/bin/bash
# PocketMine服务器Docker快速部署脚本

echo "=== PocketMine服务器Docker部署 ==="

# 检查Docker是否安装
if ! command -v docker &> /dev/null; then
    echo "错误: Docker未安装，请先安装Docker"
    exit 1
fi

# 检查Docker Compose是否安装
if ! command -v docker-compose &> /dev/null; then
    echo "错误: Docker Compose未安装，请先安装Docker Compose"
    exit 1
fi

echo "0. 准备构建环境..."
chmod +x prepare-for-docker.sh
./prepare-for-docker.sh

echo ""
echo "1. 构建Docker镜像..."
docker build -t pocketmine-server docker-build

if [ $? -eq 0 ]; then
    echo "✓ 镜像构建成功"
else
    echo "✗ 镜像构建失败"
    exit 1
fi

echo ""
echo "2. 启动服务器..."
docker-compose up -d

if [ $? -eq 0 ]; then
    echo "✓ 服务器启动成功"
    echo ""
    echo "服务器信息:"
    echo "- 服务器地址: 您的服务器IP:19132"
    echo "- 查看日志: docker-compose logs -f"
    echo "- 停止服务器: docker-compose down"
    echo "- 重启服务器: docker-compose restart"
    echo ""
    echo "正在显示服务器日志 (按Ctrl+C退出日志查看)："
    echo "================================================"
    docker-compose logs -f
else
    echo "✗ 服务器启动失败"
    exit 1
fi 