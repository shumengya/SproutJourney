<?php

namespace DesertPyramid;

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
			$x = $event->getChunk()->getX() << 4;
			$z = $event->getChunk()->getZ() << 4;
			$block = new Vector3($x, ($y = $level->getHighestBlockAt($x, $z)), $z);
			if($level->getBlock($block)->getId() == 12 and $level->getBlock($block->add(20, 0, 20))->getId() == 12){
				if(mt_rand(0, 100) < 10){
					$blocks = json_decode(file_get_contents($this->getDataFolder() . "dange.nbt"));
					foreach ($blocks as $block) {
						if(isset($block->meta)){
							$meta = $block->meta;
						}else{
							$meta = 0;
						}
						$block->x = $x - $block->x;
						$block->y = $y - $block->y + 13;
						$block->z = $z + $block->z;
						$level->setBlock(new Vector3($block->x, $block->y, $block->z), Block::get($block->id, $meta));
					}
					$this->getServer()->getLogger()->info("New Desert Pyramid generated at $x, $y, $z");
				}
			}
		}
	}
}

?>
