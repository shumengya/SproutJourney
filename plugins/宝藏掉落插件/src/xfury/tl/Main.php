<?php

namespace xfury\tl;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;


class Main extends PluginBase implements Listener {

    // 定义常量来替代配置文件中的内容
    private const SIMPLE_TREASURE_PROBABILITY = 0.1;
    private const SIMPLE_TREASURE_ITEMS = [
        ['id' => 1, 'probability' => 0.5, 'minQuantity' => 1, 'maxQuantity' => 3],  // 物品 ID 1，掉落概率 50%，数量范围 1-3
        ['id' => 4, 'probability' => 0.5, 'minQuantity' => 1, 'maxQuantity' => 2]   // 物品 ID 4，掉落概率 50%，数量范围 1-2
    ];

    private const MYSTIC_TREASURE_PROBABILITY = 0.01;
    private const MYSTIC_TREASURE_ITEMS = [
        ['id' => 10, 'probability' => 0.6, 'minQuantity' => 2, 'maxQuantity' => 4], // 物品 ID 10，掉落概率 60%，数量范围 2-4
        ['id' => 11, 'probability' => 0.4, 'minQuantity' => 1, 'maxQuantity' => 3]  // 物品 ID 11，掉落概率 40%，数量范围 1-3
    ];

    private const ANCIENT_TREASURE_PROBABILITY = 0.001;
    private const ANCIENT_TREASURE_ITEMS = [
        ['id' => 276, 'probability' => 0.3, 'minQuantity' => 1, 'maxQuantity' => 1], // 物品 ID 276，掉落概率 30%，数量范围 1-1
        ['id' => 267, 'probability' => 0.7, 'minQuantity' => 2, 'maxQuantity' => 5]  // 物品 ID 267，掉落概率 70%，数量范围 2-5
    ];

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "[宝藏掉落] 插件激活成功!");
    }

    public function onBreak(BlockBreakEvent $e) {
        $p = $e->getPlayer();
        $b = $e->getBlock();

        // 普通宝藏
        if ($b->getId() == 1 && mt_rand() / mt_getrandmax() <= self::SIMPLE_TREASURE_PROBABILITY) {
            $this->getServer()->broadcastMessage(TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "[宝藏掉落] " . TextFormat::RESET . TextFormat::GREEN . $p->getName() . TextFormat::AQUA . " 找到了 " . TextFormat::BOLD . TextFormat::RED . "普通宝藏" . TextFormat::BLUE . "!");
            foreach (self::SIMPLE_TREASURE_ITEMS as $item) {
                if (mt_rand() / mt_getrandmax() <= $item['probability']) {
                    $quantity = mt_rand($item['minQuantity'], $item['maxQuantity']);
                    $p->getInventory()->addItem(Item::get($item['id'], 0, $quantity));
                }
            }
        }

        // 神秘宝藏
        if ($b->getId() == 1 && mt_rand() / mt_getrandmax() <= self::MYSTIC_TREASURE_PROBABILITY) {
            $this->getServer()->broadcastMessage(TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "[宝藏掉落]" . TextFormat::RESET . TextFormat::GREEN . $p->getName() . TextFormat::AQUA . " 找到了 " . TextFormat::BOLD . TextFormat::GOLD . "神秘宝藏" . TextFormat::BLUE . "!");
            foreach (self::MYSTIC_TREASURE_ITEMS as $item) {
                if (mt_rand() / mt_getrandmax() <= $item['probability']) {
                    $quantity = mt_rand($item['minQuantity'], $item['maxQuantity']);
                    $p->getInventory()->addItem(Item::get($item['id'], 0, $quantity));
                }
            }
        }

        // 上古宝藏
        if ($b->getId() == 1 && mt_rand() / mt_getrandmax() <= self::ANCIENT_TREASURE_PROBABILITY) {
            $this->getServer()->broadcastMessage(TextFormat::LIGHT_PURPLE . TextFormat::BOLD . "[宝藏掉落]" . TextFormat::RESET . TextFormat::GREEN . $p->getName() . TextFormat::AQUA . " 找到了 " . TextFormat::BOLD . TextFormat::YELLOW . "上古宝藏" . TextFormat::BLUE . "!");
            foreach (self::ANCIENT_TREASURE_ITEMS as $item) {
                if (mt_rand() / mt_getrandmax() <= $item['probability']) {
                    $quantity = mt_rand($item['minQuantity'], $item['maxQuantity']);
                    $p->getInventory()->addItem(Item::get($item['id'], 0, $quantity));
                }
            }
        }
    }
}
