<?php

namespace LuLeaves;


use pocketmine\block\Leaves;
use pocketmine\block\Leaves2;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as C;

class Main extends PluginBase implements Listener
{
    public function onEnable()
    {
        $this->getServer()->getLogger()->info(TextFormat::GREEN . "[空手撸树叶掉落] 插件激活成功!");

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function OnBreak(BlockBreakEvent $event)
    {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand()->getId();
        $block = $event->getBlock();
        if( ($block instanceof Leaves) OR ($block instanceof Leaves2) )
        {
            if($item == "0")
            {
                $blockid = $block->getId();
                $blockide = $block->getDamage();
                $event->setDrops(array(Item::get($blockid,$blockide,1)));
                //$player->sendMessage(C::GREEN ."手撸树叶掉落 ");
            }
        }
    }
}