<?php

namespace Stronghold;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\event\level\ChunkPopulateEvent;

class AnonymousPL extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

	public function populate(ChunkPopulateEvent $event){
		$level = $event->getLevel();
		if($level->getFolderName() == "world"){
			if(mt_rand(0, 500) == 1){
				$blocks = json_decode(file_get_contents($this->getDataFolder() . "dange.nbt"));
				$x = $event->getChunk()->getX() << 4;
				$z = $event->getChunk()->getZ() << 4;
				$y = ($level->getHighestBlockAt($x, $z)) - mt_rand(30, 70);
				if($y < 30){
					$y = 30;
				}
				foreach ($blocks as $block) {
					$block->x = $x - $block->x;
					$block->y = $y - $block->y;
					$block->z = $z + $block->z;
					$level->setBlock(new Vector3($block->x, $block->y, $block->z), Block::get($block->id));
				}
			}
		}
	}
}

?>
