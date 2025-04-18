#!/bin/bash
# 切换到脚本所在目录
cd "$(dirname "$0")"

# 执行 PHP 脚本
bin/php-linux-amd64/bin/php src/pocketmine/PocketMine.php

# 暂停脚本，等待用户输入
read -p "按回车键继续..."