<?php

namespace pocketmine\entity;


use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item as ItemItem;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\level\Level;
use pocketmine\math\AxisAlignedBB;

class Zombie extends Monster {
#----------基本数据优化----------
	const NETWORK_ID = 32;
	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;
	public $dropExp = [0, 0];
	public $drag = 0.2;
	public $gravity = 0.15;
	private $step = 0.12;
	private $motionVector = null;
	private $farest = null;
	private $attackTicks = 0;
	protected $jumpVelocity = 1.3;
#----------基本数据优化----------

	public function getName() : string{
		return "Zombie";
	}

	public function initEntity(){
		$this->setMaxHealth(20);
		parent::initEntity();
		
		// 初始化碰撞箱
		$this->boundingBox = new AxisAlignedBB(
			$this->x - $this->width / 2,
			$this->y,
			$this->z - $this->length / 2,
			$this->x + $this->width / 2,
			$this->y + $this->height,
			$this->z + $this->length / 2
		);
	}

	public function spawnTo(Player $player){
		$pk = new AddEntityPacket();
		$pk->eid = $this->getId();
		$pk->type = Zombie::NETWORK_ID;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->speedX = $this->motionX;
		$pk->speedY = $this->motionY;
		$pk->speedZ = $this->motionZ;
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->metadata = $this->dataProperties;
		$player->dataPacket($pk);

		parent::spawnTo($player);
	}

	public function getDrops(){
		$cause = $this->lastDamageCause;
		$drops = [];
		if($cause instanceof EntityDamageByEntityEvent){
			$damager = $cause->getDamager();
			if($damager instanceof Player){
				$lootingL = $damager->getItemInHand()->getEnchantmentLevel(Enchantment::TYPE_WEAPON_LOOTING);
				if(mt_rand(0, 199) < (5 + 2 * $lootingL)){
					switch(mt_rand(0, 3)){
						case 0:
							$drops[] = ItemItem::get(ItemItem::IRON_INGOT, 0, 1);
							break;
						case 1:
							$drops[] = ItemItem::get(ItemItem::CARROT, 0, 1);
							break;
						case 2:
							$drops[] = ItemItem::get(ItemItem::POTATO, 0, 1);
							break;
					}
				}
				$count = mt_rand(0, 2 + $lootingL);
				if($count > 0){
					$drops[] = ItemItem::get(ItemItem::ROTTEN_FLESH, 0, $count);
				}
			}
		}

		return $drops;
	}
	
	public function onUpdate($currentTick){
		if($this->isClosed() or !$this->isAlive()){
			return parent::onUpdate($currentTick);
		}
		
		if($this->isMorph){
			return true;
		}

		$this->timings->startTiming();

		$hasUpdate = parent::onUpdate($currentTick);
        if ($this->getLevel() !== null) {
            $block = $this->getLevel()->getBlock(new Vector3(floor($this->x), floor($this->y) - 1, floor($this->z)));
        }else{
            return false;
        }
		
		$time = $this->getLevel() !== null ? $this->getLevel()->getTime() % Level::TIME_FULL : Level::TIME_NIGHT;
		if((!$this->isInsideOfWater()) && ($time < Level::TIME_NIGHT || $time > Level::TIME_SUNRISE) && (!$this->hasHeadBlock())){
			$this->setOnFire(1);
		}
		
		if($this->attackTicks > 0){
			$this->attackTicks--;
		}
		
		$x = 0;
		$y = 0;
		$z = 0;
		
		if($this->isOnGround()){
			if($this->fallDistance > 0){
				$this->updateFallState($this->fallDistance, true);
			}else{
				if($this->willMove()){
					foreach($this->getViewers() as $viewer){
						if(($viewer instanceof Player)and($viewer->isSurvival())and($this->distance($viewer) < 16)){
							if($this->farest == null){
								$this->farest = $viewer;
							}
							
							if($this->farest != $viewer){
								if($this->distance($viewer) < $this->distance($this->farest)){
									$this->farest = $viewer;
								}
							}
						}
					}
					
					if($this->farest != null){
						if(($this->farest instanceof Player)and($this->farest->isSurvival())and($this->distance($this->farest) < 16)){
							$this->motionVector = $this->farest->asVector3();
						}else{
							$this->farest = null;
							$this->motionVector = null;
						}
					}
					
					if($this->farest != null){
						if($this->distance($this->farest) < 1){
							if($this->attackTicks == 0){
								$damage = 4;
								$ev = new EntityDamageByEntityEvent($this, $this->farest, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage);
								if($this->farest->attack($damage, $ev) == true){
									$ev->useArmors();
								}
								$this->attackTicks = 20;
							}
						}
					}
					
					if(($this->motionVector == null)or($this->distance($this->motionVector) < $this->step)){
						$rx = mt_rand(-5, 5);
						$rz = mt_rand(-5, 5);
						$this->motionVector = new Vector3($this->x + $rx, $this->y, $this->z + $rz);
					}else{
						$this->motionVector->y = $this->y;
						if(($this->motionVector->x - $this->x) > $this->step){
							$x = $this->step;
						}elseif(($this->motionVector->x - $this->x) < -$this->step){
							$x = -$this->step;
						}
						if(($this->motionVector->z - $this->z) > $this->step){
							$z = $this->step;
						}elseif(($this->motionVector->z - $this->z) < -$this->step){
							$z = -$this->step;
						}
						
						$bx = floor($this->x);
						$by = floor($this->y);
						$bz = floor($this->z);
						if($x > 0){
							$bx++;
						}elseif($x < 0){
							$bx--;
						}
						if($y > 0){
							$by++;
						}elseif($y < 0){
							$by--;
						}
						if($z > 0){
							$bz++;
						}elseif($z < 0){
							$bz--;
						}
						$block1 = new Vector3($bx, $by, $bz);
						$block2 = new Vector3($bx, $by + 1, $bz);
						
						$blockAhead = $this->level->getBlock($block1);
						$blockAbove = $this->level->getBlock($block2);
						
						if($blockAhead->isSolid() && !$blockAbove->isSolid()){
							$this->jump();
							$y = $this->getJumpVelocity();
						} elseif($blockAhead->isSolid() && $blockAbove->isSolid()){
							$this->motionVector = null;
							$rx = mt_rand(-5, 5);
							$rz = mt_rand(-5, 5);
							$this->motionVector = new Vector3($this->x + $rx, $this->y, $this->z + $rz);
						} elseif($this->isInsideOfWater() || ($blockAhead->isSolid() && !$blockAbove->isSolid())){
							if($x > 0){
								$x = $x + 0.05;
							}elseif($x < 0){
								$x = $x - 0.05;
							}
							if($z > 0){
								$z = $z + 0.05;
							}elseif($z < 0){
								$z = $z - 0.05;
							}
							$this->move(0, 1.5, 0);
						}
						
						$this->yaw = $this->getMyYaw($x, $z);
						$nextPos = new Vector3($this->x + $x, $this->y, $this->z + $z);
						$latestPos = new Vector3($this->x, $this->y, $this->z);
						$this->pitch = $this->getMyPitch($latestPos, $nextPos);
					}
				}
			}
		}
		
		if((($x != 0)or($y != 0)or($z != 0))and($this->motionVector != null)){
			$this->setMotion(new Vector3($x, $y, $z));
		}
		
		$this->timings->stopTiming();

		return $hasUpdate;
	}
	
	public function hasHeadBlock($height = 50): bool{
		$x = floor($this->getX());
		$y = floor($this->getY()) + 2;
		$z = floor($this->getZ());
		$m = false;
		for($i=$y; $i < $y + $height; $i++){
			$block = $this->getLevel()->getBlock(new Vector3($x, $i, $z));
			if($block->getId() != 0){
				$m = true;
			}
		}
		return $m;
	}
	
	public function fall($fallDistance){
		if($this->isInsideOfWater()){
			return;
		}
		
		// 只有从6格以上掉落才受到伤害
		if($fallDistance > 6){
			$damage = ceil($fallDistance - 6);
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FALL, $damage);
			$this->attack($ev->getFinalDamage(), $ev);
		}
	}
	
}
