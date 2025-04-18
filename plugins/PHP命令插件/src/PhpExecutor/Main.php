<?php
namespace PhpExecutor;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

    public function onEnable()
    {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "[PHP命令执行] 插件激活成功!");
        // 创建权限节点（默认只允许OP使用）
    }

    public function onChat(PlayerChatEvent $event) {
        $player = $event->getPlayer();
        $message = $event->getMessage();

        // 检测消息是否以 .php 开头
        if (strtolower(substr($message, 0, 4)) === ".php") {
            // 取消聊天事件传播（防止其他玩家看到指令）
            $event->setCancelled();

            // 权限检查
            if (!$player->hasPermission("phpexecutor.command.php")) {
                $player->sendMessage(TextFormat::RED . "你没有权限执行PHP命令！");
                return;
            }

            // 提取PHP代码
            $code = substr($message, 4);
            if (empty($code)) {
                $player->sendMessage(TextFormat::RED . "请输入要执行的PHP代码！");
                return;
            }

            // 执行PHP代码并捕获输出
            try {
                ob_start();
                $result = eval($code);
                $output = ob_get_clean();

                // 构建返回消息
                $response = TextFormat::GREEN . "执行成功！\n";
                if ($output !== "") $response .= TextFormat::AQUA . "输出: " . $output . "\n";
                if ($result !== null) $response .= TextFormat::AQUA . "返回值: " . print_r($result, true);

                $player->sendMessage($response);

            } catch (\Throwable $e) {
                $player->sendMessage(TextFormat::RED . "执行出错: " . $e->getMessage());
            }
        }
    }
}
