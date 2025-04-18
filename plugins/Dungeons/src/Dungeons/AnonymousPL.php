<?php

namespace Dungeons;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\event\level\ChunkPopulateEvent;
use pocketmine\nbt\tag\{CompoundTag, IntTag, StringTag};
use pocketmine\tile\Tile;
use pocketmine\tile\MobSpawner;

class AnonymousPL extends PluginBase implements Listener{
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onChunk(ChunkPopulateEvent $event){
		$level = $event->getLevel();
		$chance = mt_rand(0, 30);
		if($chance == 1){
			$y = $level->getHighestBlockAt($event->getChunk()->getX() << 4, $event->getChunk()->getZ() << 4) - mt_rand(20, 70);
			if($y < 1){
				$y = 10;
			}
			$xmin = $event->getChunk()->getX() << 4;
			$xmax = ($event->getChunk()->getX() << 4) + mt_rand(7, 9);
			$ymin = $y;
			$ymax = $y + mt_rand(4, 5);
			$zmin = $event->getChunk()->getZ() << 4;
			$zmax = ($event->getChunk()->getZ() << 4) + mt_rand(7, 9);
			$nmax = ($xmax - $xmin + 1) * ($ymax - $ymin + 1) * ($zmax - $zmin + 1);
			for($x = $xmin; $x <= $xmax; $x++){
				for($y = $ymin; $y <= $ymax; $y++){
					for($z = $zmin; $z <= $zmax; $z++){
						$rand = mt_rand(0, 100);
						if(mt_rand(0, 100) > 25) $id = 4;
						else $id = 48;
						$level->setBlock(new Vector3($x, $y, $z), Block::get($id));
					}
				}
			}
			for($x = $xmin + 1; $x <= $xmax - 1; $x++){
				for($y = $ymin + 1; $y <= $ymax - 1; $y++){
					for($z = $zmin + 1; $z <= $zmax - 1; $z++){
						$level->setBlock(new Vector3($x, $y, $z), Block::get(Block::AIR));
					}
				}
			}

			$x = $xmax - (($xmax - $xmin) / 2);
			$y = $ymin + 1;
			$z = $zmax - (($zmax - $zmin) / 2);
			$mobs = [34, 35, 32];
			$mob = array_rand($mobs);
			$pos = new Vector3($x, $y, $z);
			/*$tile = $this->getLevel()->getTile($this);
			$this->meta = $item->getDamage();
			$level->setBlock($this, $this, true, false);
			$tile->setEntityId($this->meta);*/
			$level->setBlock($pos, Block::get(Block::MONSTER_SPAWNER), true, true);
			new MobSpawner($level,
			new CompoundTag("", [
				new StringTag("id", Tile::MOB_SPAWNER),
				new IntTag("x", $pos->x),
				new IntTag("y", $pos->y),
				new IntTag("z", $pos->z),
				new IntTag("EntityId", $mobs[$mob])
			]));
			$this->getLogger()->notice("New dungeon generated at $x, $y, $z");
			$x = $xmax - (($xmax - 1) - ($xmin + 1));
			$y = $ymin + 1;
			$z = $zmax - (($zmax - 1) - ($zmin + 1));
			$pos = new Vector3($x, $y, $z);
			$level->setBlock($pos, Block::get(Block::CHEST));
			$chest = Tile::createTile(Tile::CHEST, $level, new CompoundTag("", [
				new StringTag("id", Tile::CHEST),
				new IntTag("x", $pos->x),
				new IntTag("y", $pos->y),
				new IntTag("z", $pos->z)
			]));
			for($i = 0; $i < 20; $i++){
				$items = [
					264, 264,
					265, 265, 265,
					266, 266, 266,
					297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297, 297,
					322, 322,
					466,
					444,
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
}
