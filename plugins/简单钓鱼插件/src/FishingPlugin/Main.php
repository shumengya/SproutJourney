<?php
namespace FishingPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\FishingRod;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\utils\TextFormat;
use pocketmine\level\sound\PopSound;
use pocketmine\Player;
use pocketmine\scheduler\Task;

class Main extends PluginBase implements Listener {
    /** @var array */
    private $fishingPlayers = [];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "[简单钓鱼] 插件激活成功!");

        // 启动检查任务
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new class($this) extends Task {
            private $plugin;

            public function __construct(Main $plugin) {
                $this->plugin = $plugin;
            }

            public function onRun($currentTick) {
                $this->plugin->checkFishing();
            }
        }, 20); // 每秒检查一次
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $item = $event->getItem();

        if ($item instanceof FishingRod) {
            if (!isset($this->fishingPlayers[$player->getName()])) {
                $this->fishingPlayers[$player->getName()] = [
                    'lastCatch' => time(),
                    'nextCatchTime' => time() + mt_rand(5, 20)
                ];
                $player->sendMessage(TextFormat::GREEN . "开始钓鱼...");
            }
        }
    }

    public function checkFishing(): void {
        foreach ($this->fishingPlayers as $playerName => $data) {
            $player = $this->getServer()->getPlayerExact($playerName);
            if ($player === null || !($player->getInventory()->getItemInHand() instanceof FishingRod)) {
                unset($this->fishingPlayers[$playerName]);
                continue;
            }

            // 检查是否到达捕获时间
            if (time() >= $data['nextCatchTime']) {
                $this->catchFish($player);
                $this->fishingPlayers[$playerName] = [
                    'lastCatch' => time(),
                    'nextCatchTime' => time() + mt_rand(5, 20)
                ];
            }
        }
    }

    private function catchFish(Player $player): void {
        // 获取随机物品ID（从256到498之间）
        $randomId = mt_rand(256, 498);

        // 创建随机数量（1-64之间）
        $randomAmount = mt_rand(1, 3);

        // 随机经验值（1-3点）
        $xpAmount = mt_rand(1, 3);

        // 获取玩家手中的钓竿
        $fishingRod = $player->getInventory()->getItemInHand();

        if($fishingRod->getId() === Item::FISHING_ROD) {
            // 增加玩家经验值
            $player->addXp($xpAmount);

            // 创建物品实例
            $item = Item::get($randomId, 0, $randomAmount);

            if($item->getId() !== Item::AIR) {
                // 给予物品
                $player->getInventory()->addItem($item);
                $player->sendMessage("§a你钓到了 " . $item->getName() . " x" . $randomAmount);
            } else {
                $player->sendMessage("§c什么都没钓到...");
            }

            // 增加钓竿耐久损耗
            $damage = $fishingRod->getDamage() + 1;

            if($damage >= $fishingRod->getMaxDurability()) {
                // 钓竿损坏
                $player->getInventory()->setItemInHand(Item::get(Item::AIR));
                $player->sendMessage("§c你的钓竿损坏了!");
            } else {
                // 更新钓竿耐久度
                $fishingRod->setDamage($damage);
                $player->getInventory()->setItemInHand($fishingRod);
            }
        }
    }
}
