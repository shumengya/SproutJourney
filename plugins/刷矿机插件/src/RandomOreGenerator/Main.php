<?php

namespace RandomOreGenerator;

use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "[刷矿机] 插件激活成功!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * 监听方块更新事件
     * @param BlockUpdateEvent $event
     */
    public function onBlockUpdate(BlockUpdateEvent $event): void {
        $block = $event->getBlock(); // 获取触发事件的方块

        if (($block->getId() === Block::STONE) || ($block->getId() === Block::COBBLESTONE)) {
            $level = $block->getLevel(); // 获取方块所在的 Level
            if ($this->hasAdjacentWaterAndLava($level, $block)) {
                $this->transformBlock($level, $block);
            }
        }
    }

    /**
     * 检查方块周围是否有水和熔岩
     * @param Level $level
     * @param Block $block
     * @return bool
     */
    private function hasAdjacentWaterAndLava(Level $level, Block $block): bool {
        $directions = [
            new Vector3(1, 0, 0), new Vector3(-1, 0, 0),
            new Vector3(0, 1, 0), new Vector3(0, -1, 0),
            new Vector3(0, 0, 1), new Vector3(0, 0, -1)
        ];

        $hasWater = false;
        $hasLava = false;

        foreach ($directions as $direction) {
            $adjacentBlock = $level->getBlock($block->add($direction));
            if ($adjacentBlock->getId() === Block::WATER) {
                $hasWater = true;
            } elseif ($adjacentBlock->getId() === Block::LAVA) {
                $hasLava = true;
            }

            if ($hasWater && $hasLava) {
                return true;
            }
        }

        return false;
    }

    /**
     * 将石头转换为随机矿石
     * @param Level $level
     * @param Block $block
     */
    private function transformBlock(Level $level, Block $block): void {

        // 30% 概率进行转换
        if (mt_rand() / mt_getrandmax() > 0.3) {
            return; // 如果没有达到30%的概率，直接返回，不进行转换
        }

        $ores = [
            Block::COAL_ORE => 0.1,  // 10% 概率煤矿
            Block::REDSTONE_ORE => 0.1, // 20% 概率红石矿
            Block::NETHER_QUARTZ_ORE => 0.1,// 8% 概率下界石英矿
            Block::IRON_ORE => 0.05, // 15% 概率铁矿
            Block::LAPIS_ORE => 0.05, // 10% 概率青金石矿
            Block::GOLD_ORE => 0.03, // 5% 概率金矿
            Block::DIAMOND_ORE => 0.01, // 1% 概率钻石矿
            Block::EMERALD_ORE => 0.008 // 1% 概率绿宝石矿



        ];

        foreach ($ores as $ore => $probability) {
            if (mt_rand() / mt_getrandmax() <= $probability) {
                $this->getLogger()->info("转换石头，圆石为矿石 ID: " . $ore);
                $level->setBlock($block, Block::get($ore));
                break; // 一旦转换成功，退出循环
            }
        }
    }
}
