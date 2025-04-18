<?php

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\{CompoundTag};
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\Player;
use pocketmine\math\AxisAlignedBB;
use pocketmine\entity\Arrow;
use pocketmine\math\VoxelRayTrace;

class Skeleton extends Monster{
#----------基本数据优化----------
	const NETWORK_ID = 34;
	public $width = 0.6;
	public $length = 0.6;
	public $height = 1.8;
	public $dropExp = [0, 0];
	public $drag = 0.2;
	public $gravity = 0.15;
	private $step = 0.12;
	private $motionVector = null;
	private $farest = null;
	private $shoot = 30;
	protected $jumpVelocity = 1.3;
#----------基本数据优化----------

	public function getName() : string{
		return "Skeleton";
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
		$pk->type = Skeleton::NETWORK_ID;
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

		$pk = new MobEquipmentPacket();
		$pk->eid = $this->getId();
		$pk->item = new ItemItem(ItemItem::BOW);
		$pk->slot = 0;
		$pk->selectedSlot = 0;

		$player->dataPacket($pk);
	}

	public function getDrops(){
		$drops = [
			ItemItem::get(ItemItem::ARROW, 0, mt_rand(0, 2))
		];
		$drops[] = ItemItem::get(ItemItem::BONE, 0, mt_rand(0, 2));

		return $drops;
	}
	
	public function getArrowNBT() : CompoundTag{
		// 计算到目标的方向向量
		$target = $this->farest;
		if($target === null) {
			// 如果没有目标，使用当前朝向
			return Entity::createBaseNBT(
				$this->add(0, $this->getEyeHeight(), 0),
				new Vector3(
					-sin($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI),
					-sin($this->pitch / 180 * M_PI),
					cos($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI)
				),
				$this->yaw,
				$this->pitch
			);
		}
		
		// 计算到目标的向量
		$dx = $target->x - $this->x;
		$dy = $target->y + $target->getEyeHeight() - ($this->y + $this->getEyeHeight());
		$dz = $target->z - $this->z;
		
		// 计算水平距离
		$horizontalDistance = sqrt($dx * $dx + $dz * $dz);
		
		// 计算俯仰角(pitch)和偏航角(yaw)
		$pitch = -atan2($dy, $horizontalDistance) * 180 / M_PI;
		$yaw = atan2($dz, $dx) * 180 / M_PI - 90;
		
		// 标准化方向向量
		$length = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
		$dx /= $length;
		$dy /= $length;
		$dz /= $length;
		
		return Entity::createBaseNBT(
			$this->add(0, $this->getEyeHeight(), 0),
			new Vector3($dx, $dy, $dz),
			$yaw,
			$pitch
		);
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
	
		if($this->shoot > 0){
			$this->shoot--;
		}
	
		$time = $this->getLevel() !== null ? $this->getLevel()->getTime() % Level::TIME_FULL : Level::TIME_NIGHT;
		if((!$this->isInsideOfWater()) && ($time < Level::TIME_NIGHT || $time > Level::TIME_SUNRISE) && (!$this->hasHeadBlock())){
			$this->setOnFire(1);
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
							// 检查是否有射击条件
							$canShoot = false;
							$distance = $this->distance($this->farest);
							
							// 检查是否有视线
							$hasLineOfSight = $this->hasLineOfSight($this->farest);
							
							// 判断是否可以射击
							if($distance > 2 && $distance < 16 && $hasLineOfSight) {
								$canShoot = true;
							}
							
							if($canShoot) {
								// 可以射击时，停止移动并瞄准目标
								$this->motionVector = null;
								
								// 射击逻辑
								if($this->shoot <= 0){
									// 创建箭矢并设置更高的速度
									$arrow = new Arrow($this->getLevel(), $this->getArrowNBT(), $this);
									$arrow->setMotion($arrow->getMotion()->multiply(2.5)); // 增加箭矢速度
									$arrow->spawnToAll();
									
									// 重置射击冷却
									$this->shoot = 20; // 减少射击间隔到1秒
								}
							} else {
								// 不能射击时，移动向目标
								$this->motionVector = $this->farest->asVector3();
							}
						}else{
							$this->farest = null;
							$this->motionVector = null;
						}
					}
	
					// 同步移动核心逻辑（不含攻击）
					if(($this->motionVector == null) || ($this->distance($this->motionVector) < $this->step)){
						// 随机目标点（无目标或到达目标时）
						$rx = mt_rand(-5, 5);
						$rz = mt_rand(-5, 5);
						$this->motionVector = new Vector3($this->x + $rx, $this->y, $this->z + $rz);
					}else{
						// 计算移动方向
						$this->motionVector->y = $this->y; // 保持Y轴高度
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
	
						// 前方和上方方块检测（同步跳跃逻辑）
						$bx = floor($this->x + $x); // 目标方块坐标（根据x/z方向）
						$bz = floor($this->z + $z);
						$by = floor($this->y); // Y轴当前高度
						$blockAhead = $this->getLevel()->getBlock(new Vector3($bx, $by, $bz));       // 前方方块
						$blockAbove = $this->getLevel()->getBlock(new Vector3($bx, $by + 1, $bz));    // 上方方块
	
						if($blockAhead->isSolid() && !$blockAbove->isSolid()){
							// 前方有阻挡但上方可通过，触发跳跃
							$this->jump();
							$y = $this->getJumpVelocity(); // 使用实体的跳跃速度
						} elseif($blockAhead->isSolid() && $blockAbove->isSolid()){
							// 前方和上方都被阻挡，重置目标点
							$this->motionVector = null;
							$rx = mt_rand(-5, 5);
							$rz = mt_rand(-5, 5);
							$this->motionVector = new Vector3($this->x + $rx, $this->y, $this->z + $rz);
						} elseif($this->isInsideOfWater()){
							// 在水中调整移动速度（可选，原逻辑保留）
							$x += ($x > 0) ? 0.05 : -0.05;
							$z += ($z > 0) ? 0.05 : -0.05;
						}
	
						// 同步视角转向逻辑
						$this->yaw = $this->getMyYaw($x, $z); // 根据x/z计算偏航角
						$nextPos = new Vector3($this->x + $x, $this->y, $this->z + $z);
						$latestPos = new Vector3($this->x, $this->y, $this->z);
						$this->pitch = $this->getMyPitch($latestPos, $nextPos); // 计算俯仰角
					}
				}
			}
		}
	
		// 应用移动（保留原逻辑中的远程攻击距离判断）
		if((($x != 0) || ($y != 0) || ($z != 0)) && ($this->motionVector != null)){
			if($this->farest != null && $this->distance($this->farest) > 3){
				$this->setMotion(new Vector3($x, $y, $z));
			}elseif($this->farest == null){
				$this->setMotion(new Vector3($x, $y, $z));
			}
		}
	
		$this->timings->stopTiming();
	
		return $hasUpdate;
	}
	
	public function hasHeadBlock($height = 50): bool{
		$x = floor($this->getX());
		$y = floor($this->getY());
		$z = floor($this->getZ());
		$m = false;
		for($i=$y + 2; $i < $y + $height; $i++){
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
