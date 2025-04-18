<?php
namespace MyFakePlayerPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

class Main extends PluginBase implements Listener {

    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommandPreprocess(PlayerCommandPreprocessEvent $event) {
        $player = $event->getPlayer();
        $command = $event->getMessage();

        if ($command === "#生成假人") {
            $event->setCancelled(true);

            // Create a fake player entity
            $nbt = new CompoundTag("", [
                "Pos" => new ListTag("Pos", [
                    new DoubleTag("", $player->getX()),
                    new DoubleTag("", $player->getY()),
                    new DoubleTag("", $player->getZ())
                ]),
                "Motion" => new ListTag("Motion", [
                    new DoubleTag("", 0),
                    new DoubleTag("", 0),
                    new DoubleTag("", 0)
                ]),
                "Rotation" => new ListTag("Rotation", [
                    new FloatTag("", $player->getYaw()),
                    new FloatTag("", $player->getPitch())
                ]),
                "NameTag" => new StringTag("NameTag", "FakePlayer")
            ]);

            $level = $player->getLevel();
            $location = new Location($player->getX(), $player->getY(), $player->getZ(), $player->getYaw(), $player->getPitch(), $level);
            $human = new Human($level, $nbt);

            // Access the player's skin data
            if (method_exists($player, 'getSkinData') && method_exists($player, 'getSkinId')) {
                $human->setSkin($player->getSkinData(), $player->getSkinId());
            } else {
                $this->getLogger()->error("Failed to retrieve player's skin data.");
                return;
            }

            $human->setNameTagAlwaysVisible(true);
            $human->setNameTagVisible(true);
            $human->spawnToAll();

            $player->sendMessage(TextFormat::GREEN . "假人玩家已生成!");
        }
    }
}
