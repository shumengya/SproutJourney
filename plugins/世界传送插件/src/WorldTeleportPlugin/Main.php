<?php

namespace WorldTeleportPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use pocketmine\utils\TextFormat;

class Main extends PluginBase {

    public function onEnable() : void {
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "[世界传送] 插件激活成功!");
    }

    public function onDisable() : void {
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "[世界传送] 插件卸载成功!");
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) : bool {
        switch($command->getName()) {
            case "tpworld":
                if (!$sender instanceof Player) {
                    $sender->sendMessage(C::RED . "这个命令只能在游戏中使用.");
                    return true;
                }

                if (count($args) < 1) {
                    $sender->sendMessage(C::RED . "Usage: /tpworld <world>");
                    return false;
                }

                $worldName = $args[0];
                $level = $this->getServer()->getLevelByName($worldName);

                if ($level === null) {
                    $sender->sendMessage(C::RED . "没有找到 '$worldName' 世界.");
                    return true;
                }

                $sender->teleport($level->getSafeSpawn());
                $sender->sendMessage(C::GREEN . "传送到 '$worldName' 世界.");
                return true;

            case "lsworld":
                $worlds = $this->getServer()->getLevels();
                $worldNames = array_map(function($level) {
                    return $level->getFolderName();
                }, $worlds);

                $sender->sendMessage(C::YELLOW . "当前世界有: " . implode(", ", $worldNames));
                return true;

            default:
                return false;
        }
    }
}
