#!/bin/bash
# Docker容器启动脚本

# 切换到脚本所在目录
cd "$(dirname "$0")"

# 确保日志目录存在
mkdir -p crashdumps

# 执行 PHP 脚本
exec bin/php-linux-amd64/bin/php src/pocketmine/PocketMine.php 