#!/bin/bash
# 为Docker构建准备文件，将中文目录重命名为英文

echo "=== 准备Docker构建环境 ==="

# 创建构建临时目录
BUILD_DIR="docker-build"
rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"

echo "1. 复制核心文件..."
# 复制核心目录
cp -r bin "$BUILD_DIR/" 2>/dev/null || true
cp -r src "$BUILD_DIR/" 2>/dev/null || true

echo "2. 处理插件目录..."
mkdir -p "$BUILD_DIR/plugins"

# 复制英文插件
for plugin in DesertPyramid Dungeons EnderStructure ItemNBTViewer LiteCore Mineshaft MobSpawn relic Stronghold ChatLoggerPlugin; do
    if [ -d "plugins/$plugin" ]; then
        cp -r "plugins/$plugin" "$BUILD_DIR/plugins/" 2>/dev/null || true
        echo "  ✓ 复制插件: $plugin"
    fi
done

# 处理中文插件，复制为英文名
declare -A plugin_map=(
    ["PHP命令插件"]="PhpExecutor"
    ["世界传送插件"]="WorldTeleport"
    ["事件记录器插件"]="EventNotifier"
    ["假人插件"]="FakePlayer"
    ["刀撸西瓜掉落插件"]="CutWatermelon"
    ["刀砍玻璃掉落插件"]="CutGlass"
    ["刷矿机插件"]="OreGenerator"
    ["剑砍冰块掉落插件"]="CutIce"
    ["宝藏掉落插件"]="TreasureDrop"
    ["实体掉血粒子插件"]="SpecialHit"
    ["查看手中物品nbt插件"]="ItemNBTViewerCN"
    ["空手撸树叶掉落插件"]="LuLeaves"
    ["简单钓鱼插件"]="FishingPlugin"
    ["聊天记录插件"]="ChatLoggerCN"
    ["肾上腺素插件"]="Adrenaline"
    ["遗迹插件"]="RelicPlugin"
    ["钻木取火插件"]="BoreWoodFire"
    ["高原反应插件"]="AltitudeStress"
)

for chinese_name in "${!plugin_map[@]}"; do
    english_name="${plugin_map[$chinese_name]}"
    if [ -d "plugins/$chinese_name" ]; then
        cp -r "plugins/$chinese_name" "$BUILD_DIR/plugins/$english_name" 2>/dev/null || true
        echo "  ✓ $chinese_name -> $english_name"
    fi
done

echo "3. 处理配置文件..."
# 复制配置文件（如果存在）
for file in pocketmine.yml server.properties banned-ips.txt banned-cids.txt; do
    if [ -f "$file" ]; then
        cp "$file" "$BUILD_DIR/" 2>/dev/null || true
        echo "  ✓ 复制配置: $file"
    else
        touch "$BUILD_DIR/$file"
        echo "  ✓ 创建空配置: $file"
    fi
done

echo "4. 复制启动脚本和Dockerfile..."
cp docker-start.sh "$BUILD_DIR/" 2>/dev/null || true
cp Dockerfile.clean "$BUILD_DIR/Dockerfile" 2>/dev/null || true

echo "5. 创建必要目录..."
mkdir -p "$BUILD_DIR/worlds" "$BUILD_DIR/players" "$BUILD_DIR/crashdumps"

echo ""
echo "✓ Docker构建准备完成！"
echo "构建目录: $BUILD_DIR"
echo "现在可以运行: docker build -t pocketmine-server $BUILD_DIR" 