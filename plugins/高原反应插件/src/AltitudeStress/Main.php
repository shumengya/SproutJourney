<?php
/**
 * Created by PhpStorm.
 * User: ASUS-
 * Date: 2017/8/24
 * Time: 1:37
 */

namespace AltitudeStress;

use pocketmine\entity\Effect;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as C;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener
{
    public $high_80,$high_100,$high_120; // 这三个变量用于跟踪玩家在不同高度区域的移动次数

    // 插件启用时调用
    public function onEnable()
    {
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "[高原反应] 插件激活成功!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this); // 注册事件监听器
    }

    // 玩家移动事件监听器
    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer(); // 获取触发事件的玩家对象
        $y = $player->getY(); // 获取玩家的Y坐标（即玩家的高度）

        // 定义反胃和虚弱效果
        $effect9 = Effect::getEffect(Effect::NAUSEA)->setVisible(false)->setAmplifier(0)->setDuration(20*60); // 反胃效果，持续 2400 ticks
        $effect18 = Effect::getEffect(Effect::WEAKNESS)->setVisible(true)->setAmplifier(0)->setDuration(20*60); // 虚弱效果，持续 2400 ticks

        // 如果玩家的高度在80到100之间（高原区域的低海拔）
        if ($y >= 80 AND $y <= 100)
        {
            $this->high_80++; // 增加玩家在这个高度范围内的移动次数
            if($this->high_80 == 5) // 如果玩家在该高度范围内移动了 5 次
            {
                $player->addEffect($effect18); // 给玩家添加虚弱效果
               // $player->sendTitle(C::AQUA."高原反应", C::GOLD . "开始使你变得虚弱,再高一点甚至会出现眩晕状况!", "2", "2", "40"); // 显示提示标题
                $player->sendMessage(C::AQUA . "高原反应: " . C::GOLD . "开始使你变得虚弱, 再高一点甚至会出现眩晕状况!"); // 发送普通消息给玩家
            }
        }

        // 如果玩家的高度大于等于100（中高海拔区域）
        if ($y >= 100)
        {
            $this->high_100++; // 增加玩家在该高度范围内的移动次数
            if($this->high_100 == 5) // 如果玩家在该高度范围内移动了 5 次
            {
                $player->addEffect($effect9); // 给玩家添加反胃效果
               // $player->sendTitle(C::AQUA."高原反应", C::GOLD . "你已经出现眩晕状况,不能再往上走了!可能直接让你死亡!", "2", "2", "40"); // 显示提示标题
                $player->sendMessage(C::AQUA . "高原反应: " . C::GOLD . "你已经出现眩晕状况, 不能再往上走了!"); // 发送普通消息给玩家
            }
        }


        // 如果玩家的高度小于等于80（低海拔区域），重置所有计数器
        if ($y <= 80)
        {
            $this->high_80 = 0; // 重置高原80-100区域的计数器
            $this->high_100 = 0; // 重置高原100-240区域的计数器
            $this->high_120 = 0; // 重置高原240+区域的计数器
        }
    }
}
