<?php

namespace EventNotifierPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as C;
use pocketmine\utils\TextFormat;
//-----------------------------------------------------------------------------------------//
#玩家相关 player
use pocketmine\Player; // 玩家对象基类
use pocketmine\event\player\PlayerAchievementAwardedEvent; // 玩家获得成就事件
use pocketmine\event\player\PlayerAnimationEvent; // 玩家动画事件（挥手/肢体动作）
use pocketmine\event\player\PlayerBedEnterEvent; // 玩家进入床事件
use pocketmine\event\player\PlayerBedLeaveEvent; // 玩家离开床事件
use pocketmine\event\player\PlayerBucketEmptyEvent; // 玩家倒空桶事件（水/岩浆）
use pocketmine\event\player\PlayerBucketEvent; // 玩家使用桶通用事件
use pocketmine\event\player\PlayerBucketFillEvent; // 玩家装填桶事件
use pocketmine\event\player\PlayerChatEvent; // 玩家聊天消息事件
use pocketmine\event\player\PlayerCommandPreprocessEvent; // 玩家执行命令预处理事件
use pocketmine\event\player\PlayerCreationEvent; // 玩家对象创建事件（核心底层）
use pocketmine\event\player\PlayerDeathEvent; // 玩家死亡事件
use pocketmine\event\player\PlayerDropItemEvent; // 玩家丢弃物品事件
use pocketmine\event\player\PlayerExhaustEvent; // 玩家消耗体力事件（饥饿值变化）
use pocketmine\event\player\PlayerExperienceChangeEvent; // 玩家经验值变化事件
use pocketmine\event\player\PlayerFishEvent; // 玩家钓鱼事件（抛竿/收竿）
use pocketmine\event\player\PlayerGameModeChangeEvent; // 玩家游戏模式变更事件
use pocketmine\event\player\PlayerGlassBottleEvent; // 玩家使用玻璃瓶装液体事件
use pocketmine\event\player\PlayerHungerChangeEvent; // 玩家饥饿度变化事件
use pocketmine\event\player\PlayerInteractEvent; // 玩家交互事件（点击方块/空气）
use pocketmine\event\player\PlayerItemConsumeEvent; // 玩家消耗物品事件（吃食物）
use pocketmine\event\player\PlayerItemHeldEvent; // 玩家切换手持物品事件
use pocketmine\event\player\PlayerJoinEvent; // 玩家加入服务器事件
use pocketmine\event\player\PlayerJumpEvent; // 玩家跳跃事件
use pocketmine\event\player\PlayerKickEvent; // 玩家被踢出事件
use pocketmine\event\player\PlayerLoginEvent; // 玩家登录验证事件
use pocketmine\event\player\PlayerMoveEvent; // 玩家移动事件（坐标变化）
use pocketmine\event\player\PlayerPickupExpOrbEvent; // 玩家拾取经验球事件
use pocketmine\event\player\PlayerPreLoginEvent; // 玩家预登录事件（连接初期）
use pocketmine\event\player\PlayerQuitEvent; // 玩家退出服务器事件
use pocketmine\event\player\PlayerRespawnEvent; // 玩家重生事件
use pocketmine\event\player\PlayerTextPreSendEvent; // 玩家文本发送前事件（消息格式化）
use pocketmine\event\player\PlayerToggleFlightEvent; // 玩家切换飞行模式事件
use pocketmine\event\player\PlayerToggleGlideEvent; // 玩家切换滑翔事件（鞘翅）
use pocketmine\event\player\PlayerToggleSneakEvent; // 玩家切换潜行状态事件
use pocketmine\event\player\PlayerToggleSprintEvent; // 玩家切换疾跑状态事件
use pocketmine\event\player\PlayerTransferEvent; // 玩家跨服务器传送事件（Proxy相关）
use pocketmine\event\player\PlayerUseFishingRodEvent; // 玩家使用钓鱼竿事件（抛竿/收竿）
//-----------------------------------------------------------------------------------------//
//实体相关 entity
use pocketmine\event\entity\CreeperPowerEvent; // 苦力怕充能事件（闪电激活苦力怕时触发）
use pocketmine\event\entity\EntityArmorChangeEvent; // 实体护甲变更事件
use pocketmine\event\entity\EntityBlockChangeEvent; // 实体改变方块事件
use pocketmine\event\entity\EntityCombustByBlockEvent; // 方块引燃实体事件
use pocketmine\event\entity\EntityCombustByEntityEvent; // 实体引燃其他实体事件
use pocketmine\event\entity\EntityCombustEvent; // 实体燃烧通用事件
use pocketmine\event\entity\EntityConsumeTotemEvent; // 实体使用图腾事件
use pocketmine\event\entity\EntityDamageByBlockEvent; // 方块造成实体伤害事件
use pocketmine\event\entity\EntityDamageByChildEntityEvent; // 子实体造成伤害事件（如投掷物）
use pocketmine\event\entity\EntityDamageByEntityEvent; // 实体造成伤害事件
use pocketmine\event\entity\EntityDamageEvent; // 实体受伤通用事件
use pocketmine\event\entity\EntityDeathEvent; // 实体死亡事件
use pocketmine\event\entity\EntityDespawnEvent; // 实体消失事件
use pocketmine\event\entity\EntityDrinkPotionEvent; // 实体饮用药水事件
use pocketmine\event\entity\EntityEatBlockEvent; // 实体食用方块事件（如绵羊吃草）
use pocketmine\event\entity\EntityEatEvent; // 实体进食通用事件
use pocketmine\event\entity\EntityEatItemEvent; // 实体食用物品事件
use pocketmine\event\entity\EntityEffectAddEvent; // 实体获得药水效果事件
use pocketmine\event\entity\EntityEffectRemoveEvent; // 实体药水效果移除事件
use pocketmine\event\entity\EntityEvent; // 实体事件基类
use pocketmine\event\entity\EntityExplodeEvent; // 实体爆炸事件
use pocketmine\event\entity\EntityGenerateEvent; // 实体生成事件（用于刷怪笼）
use pocketmine\event\entity\EntityInventoryChangeEvent; // 实体背包变更事件
use pocketmine\event\entity\EntityLevelChangeEvent; // 实体切换世界事件
use pocketmine\event\entity\EntityMotionEvent; // 实体运动事件（强制移动时触发）
use pocketmine\event\entity\EntityRegainHealthEvent; // 实体生命恢复事件
use pocketmine\event\entity\EntityShootBowEvent; // 实体射箭事件
use pocketmine\event\entity\EntitySpawnEvent; // 实体生成事件
use pocketmine\event\entity\EntityTeleportEvent; // 实体传送事件
use pocketmine\event\entity\ExplosionPrimeEvent; // 爆炸预备事件（控制爆炸参数）
use pocketmine\event\entity\ItemDespawnEvent; // 物品实体消失事件
use pocketmine\event\entity\ItemSpawnEvent; // 物品实体生成事件
use pocketmine\event\entity\ProjectileHitEvent; // 抛射物击中事件
use pocketmine\event\entity\ProjectileLaunchEvent; // 抛射物发射事件
//-----------------------------------------------------------------------------------------//
#物品相关 inventory
use pocketmine\event\inventory\CraftItemEvent;//物品合成
use pocketmine\event\inventory\InventoryTransactionEvent;//物品栏改变，改变一个触发一次
use pocketmine\event\inventory\AnvilProcessEvent;//铁站处理
use pocketmine\event\inventory\InventoryEvent;//物品栏
use pocketmine\event\inventory\FurnaceBurnEvent;//熔炉燃烧
use pocketmine\event\inventory\InventoryClickEvent;//物品栏点击
use pocketmine\event\inventory\FurnaceSmeltEvent;//熔炉熔炼
use pocketmine\event\inventory\InventoryCloseEvent;//物品栏关闭
use pocketmine\event\inventory\InventoryOpenEvent;//物品栏打开
use pocketmine\event\inventory\InventoryPickupArrowEvent;//物品栏拾取箭头
use pocketmine\event\inventory\InventoryPickupItemEvent;//物品栏拾取物品
//-----------------------------------------------------------------------------------------//
#方块相关 block
use pocketmine\event\block\BlockEvent;//方块基础
use pocketmine\event\block\BlockBreakEvent;//方块破坏
use pocketmine\event\block\BlockPlaceEvent;//方块放置
use pocketmine\event\block\SignChangeEvent;//告示牌改变
use pocketmine\event\block\LeavesDecayEvent;//树叶消失
use pocketmine\event\block\ItemFrameDropItemEvent;//物品展示框掉落物品
use pocketmine\event\block\BlockUpdateEvent;//方块更新
use pocketmine\event\block\BlockSpreadEvent;//方块传播
use pocketmine\event\block\BlockGrowEvent;//方块生长
use pocketmine\event\block\BlockFormEvent;//方块形成
use pocketmine\event\block\BlockBurnEvent;//方块燃烧
//-----------------------------------------------------------------------------------------//
//世界相关 level
use \pocketmine\event\level\ChunkEvent;//区块处理
use \pocketmine\event\level\LevelEvent;//世界处理
use \pocketmine\event\level\ChunkLoadEvent;//区块加载
use \pocketmine\event\level\ChunkPopulateEvent;//区块填充
use \pocketmine\event\level\LevelInitEvent;//世界初始化
use \pocketmine\event\level\ChunkUnloadEvent;//区块卸载
use \pocketmine\event\level\LevelLoadEvent;//世界加载
use \pocketmine\event\level\LevelSaveEvent;//世界保存
use \pocketmine\event\level\LevelUnloadEvent;//世界卸载
use \pocketmine\event\level\SpawnChangeEvent;//出生点改变
use \pocketmine\event\level\WeatherChangeEvent;//天气改变
//-----------------------------------------------------------------------------------------//
//插件相关 plugin
use \pocketmine\event\plugin\PluginDisableEvent;
use \pocketmine\event\plugin\PluginEvent;
use \pocketmine\event\plugin\PluginEnableEvent;
//-----------------------------------------------------------------------------------------//
//服务器相关 server
use \pocketmine\event\server\ServerEvent;//服务器处理
use \pocketmine\event\server\DataPacketReceiveEvent;//数据包接收
use \pocketmine\event\server\LowMemoryEvent;//内存不足
use \pocketmine\event\server\QueryRegenerateEvent;//查询重新生成
use \pocketmine\event\server\ServerCommandEvent;//服务器命令
use \pocketmine\event\server\DataPacketSendEvent;//数据包发送
use \pocketmine\event\server\RemoteServerCommandEvent;//远程服务器命令
//-----------------------------------------------------------------------------------------//


class Main extends PluginBase implements Listener {

    public function onEnable() : void {
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "[事件记录] 插件激活成功!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

//玩家相关用绿色来表示-------------------------------------------------------------------------------------------
    public function onPlayerJoin(PlayerJoinEvent $event) : void {
        $player = $event->getPlayer();
        $this->getLogger()->info(C::GREEN . "[PlayerJoin] " . $player->getName() . " 加入了游戏");
        //$player->sendMessage(C::GREEN . "欢迎 " . $player->getName() . "! 你加入了游戏.");
    }
    public function onPlayerQuit(PlayerQuitEvent $event) : void {
        $player = $event->getPlayer();
        $this->getLogger()->info(C::GREEN . "[PlayerQuit] " . $player->getName() . " 离开了游戏");
        //$player->sendMessage(C::YELLOW . "再见 " . $player->getName() . "! 你离开了游戏.");
    }
    public function onPlayerChat(PlayerChatEvent $event) : void {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $this->getLogger()->info(C::GREEN . "[PlayerChat] " . $player->getName() . " 说: " . $message);
        //$player->sendMessage(C::AQUA . $player->getName() . " 说: " . $message);
    }
    public function onPlayerDeath(PlayerDeathEvent $event) : void {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            $this->getLogger()->info(C::GREEN . "[PlayerDeath] " . $player->getName() . " 死亡了");
           // $player->sendMessage(C::DARK_RED . "你死了！");
        }
    }
    public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event) : void {
        $player = $event->getPlayer();
        $this->getLogger()->info(C::GREEN . "[PlayerCommandPreprocess] " . $player->getName() . " 输入了命令: " . $event->getMessage());
       // $player->sendMessage(C::YELLOW . "你输入了命令：" . $event->getMessage());
    }
    public function onPlayerInteract(PlayerInteractEvent $event) : void {
        $player = $event->getPlayer();
        $this->getLogger()->info(C::GREEN . "[PlayerInteract] " . $player->getName() . " 进行了互动");
       // $player->sendMessage(C::WHITE . "你进行了互动！");
    }
    public function onPlayerDropItem(PlayerDropItemEvent $event) : void {
        $player = $event->getPlayer();
        $this->getLogger()->info(C::GREEN . "[PlayerDropItem] " . $player->getName() . " 扔掉了物品");
       // $player->sendMessage(C::GOLD . "你扔掉了一个物品！");
    }
    public function onInventoryTransaction(InventoryTransactionEvent $event) : void {
        foreach ($event->getTransaction()->getInventories() as $inventory) {
            $holder = $inventory->getHolder();
            if ($holder instanceof Player) {
                $this->getLogger()->info(C::GREEN . "[InventoryTransaction] " . $holder->getName() . " 更改了物品栏");
                //$holder->sendMessage(C::LIGHT_PURPLE . "物品栏物品改变！");
            }
        }
    }
    public function onItemFrameDropItem(ItemFrameDropItemEvent $event) : void {
        $player = $event->getPlayer();
        $this->getLogger()->info(C::GREEN . "[ItemFrameDropItem] " . $player->getName() . " 物品从物品框中掉落");
        //$player->sendMessage(C::DARK_AQUA . "物品从物品框中掉落！");
    }



//方块相关红色表示---------------------------------------------------------------------------------------
    public function onBlockBreak(BlockBreakEvent $event) : void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $this->getLogger()->info(C::RED . "[BlockBreak] " . $player->getName() . " 破坏了 " . $block->getName() . " 方块");
        //$player->sendMessage(C::RED . "你破坏了一个方块: " . $block->getName());
    }
    public function onBlockPlace(BlockPlaceEvent $event) : void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $this->getLogger()->info(C::RED . "[BlockPlace] " . $player->getName() . " 放置了 " . $block->getName() . " 方块");
       // $player->sendMessage(C::BLUE . "你放置了一个方块: " . $block->getName());
    }
    public function onSignChange(SignChangeEvent $event) : void {
        $player = $event->getPlayer();
        $this->getLogger()->info(C::RED . "[SignChange] " . $player->getName() . " 更改了告示牌");
        //$player->sendMessage(C::DARK_GREEN . "你更改了一个告示牌！");
    }
    public function onLeavesDecay(LeavesDecayEvent $event) : void {
        $block = $event->getBlock();
        $level = $block->getLevel();
        foreach ($level->getPlayers() as $player) {
            if ($player->distance($block) <= 10) {
                $this->getLogger()->info(C::RED . "[LeavesDecay] 附近的树叶正在腐烂！");
               // $player->sendMessage(C::GREEN . "附近的树叶正在腐烂！");
            }
        }
    }
    public function onBlockGrow(BlockGrowEvent $event) : void {
        foreach ($event->getBlock()->getLevel()->getPlayers() as $player) {
            $this->getLogger()->info(C::RED . "[BlockGrow] 方块正在生长！");
            //$player->sendMessage(C::LIGHT_GREEN . "方块正在生长！");
        }
    }
    public function onBlockForm(BlockFormEvent $event) : void {
        foreach ($event->getBlock()->getLevel()->getPlayers() as $player) {
            $this->getLogger()->info(C::RED . "[BlockForm] 方块正在形成！");
           // $player->sendMessage(C::LIGHT_BLUE . "方块正在形成！");
        }
    }
    public function onBlockBurn(BlockBurnEvent $event) : void {
        foreach ($event->getBlock()->getLevel()->getPlayers() as $player) {
            $this->getLogger()->info(C::RED . "[BlockBurn] 方块正在燃烧！");
            //$player->sendMessage(C::RED . "方块正在燃烧！");
        }
    }

//实体生物相关蓝色来显示------------------------------------------------------------------------------
    public function onEntityDamage(EntityDamageEvent $event) : void {
        $entity = $event->getEntity();
        if ($entity instanceof Player) {
            $this->getLogger()->info(C::BLUE . "[EntityDamage] " . $entity->getName() . " 受到了伤害");
           // $entity->sendMessage(C::MAGENTA . "你受到了伤害！");
        }
    }


}
