<?php

namespace ChatLoggerPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\TextFormat;
use DateTime;

class Main extends PluginBase implements Listener {

    private $logFilePath; // 移除类型声明以兼容 PHP 7.2

public function onEnable() : void {
    @mkdir($this->getDataFolder() . "chats", 0777, true);

    $startTime = new DateTime();
    $this->logFilePath = $this->getDataFolder() . "chats/" . $startTime->format('Y-m-d_H-i-s') . ".log";

    $this->getServer()->getLogger()->info(TextFormat::GREEN . "[聊天记录] 插件激活成功!");
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
}

    public function onDisable() : void {
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "[聊天记录] 插件卸载成功!");
    }

    /**
     * @param PlayerChatEvent $event
     */
    public function onPlayerChat(PlayerChatEvent $event) : void {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $logMessage = "[" . date('Y-m-d H:i:s') . "] " . $player->getName() . ": " . $message . PHP_EOL;

        file_put_contents($this->logFilePath, $logMessage, FILE_APPEND);
    }
}
