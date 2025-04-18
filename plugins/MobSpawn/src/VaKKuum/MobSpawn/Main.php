<?php
namespace VaKKuum\MobSpawn;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\plugin\Plugin;
use pocketmine\math\Vector3;
use pocketmine\item\Item;
use pocketmine\entity\Entity;
use pocketmine\entity\Cow;
use pocketmine\entity\Sheep;
use pocketmine\entity\PigZombie;
use pocketmine\entity\Pig;
use pocketmine\entity\Zombie;
use pocketmine\entity\Skeleton;
use pocketmine\entity\Creeper;
use pocketmine\entity\Wolf;
use pocketmine\entity\Enderman;
use pocketmine\entity\WitherSkeleton;
use pocketmine\entity\Spider;
use pocketmine\level\Position;
use pocketmine\level\Chunk;
use pocketmine\level\Level;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\CallbackTask;
use pocketmine\event\entity\EntityGenerateEvent;
use pocketmine\nbt\tag\{CompoundTag, IntTag, FloatTag, ListTag, StringTag, IntArrayTag, DoubleTag};

class Main extends PluginBase{
	
	private $task;
	private $spawned = [];
	
	public function onEnable(){
		$this->task = $this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"generateMob"]),20 * 60);
	}
	public function onDisable(){
		if($this->task instanceof Task){
			$this->task->cancel();
		}
	}
	
	public function generateMob(){
		foreach($this->getServer()->getLevels() as $level){
			foreach($this->getServer()->getOnlinePlayers() as $player){
				$entities = [];
				foreach($level->getEntities() as $entity){
					if($entity instanceof Zombie or $entity instanceof Creeper or $entity instanceof Skeleton or $entity instanceof Cow or $entity instanceof Sheep or $entity instanceof Pig or $entity instanceof PigZombie or $entity instanceof Spider or $entity instanceof Wolf or $entity instanceof Enderman){
						if($player->distance($entity) < 32){
							$entities[] = $entity;
						}
					}
				}
				
				if(count($entities) < 5){
					$x = mt_rand($player->getX() - 16, $player->getX() + 16);
					$z = mt_rand($player->getZ() - 16, $player->getZ() + 16);
					$pos = new Vector3($x, $player->getY(), $z);
					$pos = $level->getSafeSpawn($pos);
					$pos = new Position($pos->x, $pos->y, $pos->z, $level);
					
					
					$mobs = [];
					$time = $level ? $level->getTime() % Level::TIME_FULL : Level::TIME_NIGHT;
					if(($level->getName() != "nether") && (($time > Level::TIME_NIGHT) && ($time < Level::TIME_SUNRISE))){
						$mobs[] = Zombie::NETWORK_ID;
						$mobs[] = Zombie::NETWORK_ID;
						$mobs[] = Zombie::NETWORK_ID;
						$mobs[] = Zombie::NETWORK_ID;
						$mobs[] = Zombie::NETWORK_ID;
						$mobs[] = Zombie::NETWORK_ID;
						$mobs[] = Skeleton::NETWORK_ID;
						$mobs[] = Skeleton::NETWORK_ID;
						$mobs[] = Skeleton::NETWORK_ID;
						$mobs[] = Skeleton::NETWORK_ID;
						$mobs[] = Spider::NETWORK_ID;
						$mobs[] = Spider::NETWORK_ID;
						$mobs[] = Spider::NETWORK_ID;
						$mobs[] = Spider::NETWORK_ID;
						$mobs[] = Creeper::NETWORK_ID;
						$mobs[] = Creeper::NETWORK_ID;
						$mobs[] = Creeper::NETWORK_ID;
						$mobs[] = Creeper::NETWORK_ID;
						$mobs[] = Enderman::NETWORK_ID;
						$mobs[] = Enderman::NETWORK_ID;
		    			$mobs[] = Enderman::NETWORK_ID;
                    	$mobs[] = Enderman::NETWORK_ID;
					}elseif($level->getName() == "nether"){
						$mobs[] = PigZombie::NETWORK_ID;
						$mobs[] = PigZombie::NETWORK_ID;
						$mobs[] = PigZombie::NETWORK_ID;
						$mobs[] = PigZombie::NETWORK_ID;
					}elseif(($level->getName() != "nether") && (($time < Level::TIME_NIGHT) || ($time > Level::TIME_SUNRISE))){
						$mobs[] = Cow::NETWORK_ID;
						$mobs[] = Cow::NETWORK_ID;
						$mobs[] = Cow::NETWORK_ID;
						$mobs[] = Pig::NETWORK_ID;
						$mobs[] = Pig::NETWORK_ID;
						$mobs[] = Pig::NETWORK_ID;
						$mobs[] = Sheep::NETWORK_ID;
						$mobs[] = Sheep::NETWORK_ID;
						$mobs[] = Wolf::NETWORK_ID;
						$mobs[] = Wolf::NETWORK_ID;
						$mobs[] = Enderman::NETWORK_ID;
						$mobs[] = Enderman::NETWORK_ID;					
					}
				
					if(count($mobs) > 0){
						$id = array_rand($mobs);
						$this->getServer()->getPluginManager()->callEvent($ev = new EntityGenerateEvent($pos, $mobs[$id], EntityGenerateEvent::CAUSE_AI_HOLDER));
						if(!$ev->isCancelled()){
							$mob = Entity::createEntity($mobs[$id], $level, $this->getNBT($pos));
							$mob->spawnToAll();
						}
					}
				
				}
			}
		}
	}
	
	public function getNBT(Position $pos) : CompoundTag{
		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $pos->x),
				new DoubleTag("", $pos->y),
				new DoubleTag("", $pos->z)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", 0),
				new FloatTag("", 0)
			]),
		]);
		return $nbt;
	}
	
}
?>