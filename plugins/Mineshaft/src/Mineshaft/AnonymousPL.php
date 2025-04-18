<?php

namespace Mineshaft;

use pocketmine\event\Listener;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\nbt\tag\{CompoundTag, IntTag, StringTag};
use pocketmine\tile\Tile;
use pocketmine\item\Item;
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
			$y = $level->getHighestBlockAt($x, $z) - mt_rand(15, 60);
			if($y < 5){
				$y = mt_rand(10, 25);
			}
			if(mt_rand(0, 100) < 2){
				$blocks = json_decode(file_get_contents($this->getDataFolder() . "dange.nbt"));
				foreach ($blocks as $block) {
					if(isset($block->meta)){
						$meta = $block->meta;
					}else{
						$meta = 0;
					}
					$block->x = $x - $block->x;
					$block->y = $y - $block->y;
					$block->z = $z + $block->z;
					$level->setBlock(new Vector3($block->x, $block->y, $block->z), Block::get($block->id, $meta));
					if($block->id == 52){
						$nbt = new CompoundTag("", [
							new StringTag("id", Tile::MOB_SPAWNER),
							new IntTag("x", $block->x),
							new IntTag("y", $block->y),
							new IntTag("z", $block->z),
							new IntTag("EntityId", 40),
						]);
						Tile::createTile(Tile::MOB_SPAWNER, $level, $nbt);
					}
					if($block->id == 54){
						$chest = Tile::createTile(Tile::CHEST, $level, new CompoundTag("", [
							new StringTag("id", Tile::CHEST),
							new IntTag("x", $block->x),
							new IntTag("y", $block->y),
							new IntTag("z", $block->z)
						]));
						for($i = 0; $i < 20; $i++){
							$items = [
								264, 264,
								265, 265, 265,
								266, 266, 266,
								297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297,
								322, 322,
								466,
								334, 334, 334, 334, 334, 334, 334, 334, 334, 334,
								421, 421, 421,
								289, 289, 289, 289, 289, 289, 289, 289,
								325, 325, 325, 325, 325, 325, 325, 325, 325, 325, 325, 325,
								296, 296, 296, 296, 296, 296, 296, 296, 296, 296, 296, 296, 296, 296, 296, 296,
								352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352, 352,
								367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367, 367,
								263, 263, 263, 263, 263, 263, 263, 263, 263, 263
							];
							$item = array_rand($items);
							$chest->getInventory()->setItem(mt_rand(0, 27), Item::get($items[$item], 0, 1));
						}
					}
				}
				$this->getServer()->getLogger()->info("New Mineshaft generated at $x, $y, $z");
			}
		}
	}
}

?>
