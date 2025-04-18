<?php

namespace SpecialHit;


use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\sound\DoorBumpSound;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class SpecialHit extends PluginBase implements Listener
{
    public function onEnable()
    {
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "[生物s攻击粒子效果] 插件激活成功!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    public function onDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        if($event instanceof EntityDamageByEntityEvent)
        {
            //播放门撞坏的声音
            $entity->getLevel()->addSound(new DoorBumpSound($entity));
            //添加方块破坏粒子效果
            $entity->getLevel()->addParticle(new DestroyBlockParticle(new Vector3($entity->getX(), $entity->getY(), $entity->getZ()), Block::get(152)));
        }
    }
}
