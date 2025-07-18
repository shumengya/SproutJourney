<?php

namespace pocketmine\entity;

use pocketmine\block\Block;
use pocketmine\block\Fire;
use pocketmine\block\Portal;
use pocketmine\block\PressurePlate;
use pocketmine\block\SlimeBlock;
use pocketmine\block\Water;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityEffectRemoveEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Timings;
use pocketmine\item\Elytra;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Math;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\SetEntityMotionPacket;
use pocketmine\network\mcpe\protocol\SetEntityLinkPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\Binary;

abstract class Entity extends Location implements Metadatable {

	protected const STEP_CLIP_MULTIPLIER = 0.4;

	const NETWORK_ID = -1;

	const DATA_TYPE_BYTE = 0;
	const DATA_TYPE_SHORT = 1;
	const DATA_TYPE_INT = 2;
	const DATA_TYPE_FLOAT = 3;
	const DATA_TYPE_STRING = 4;
	const DATA_TYPE_SLOT = 5;
	const DATA_TYPE_POS = 6;
	const DATA_TYPE_LONG = 7;
	const DATA_TYPE_VECTOR3F = 8;

	const DATA_FLAGS = 0;
	const DATA_HEALTH = 1; //int (minecart/boat)
	const DATA_VARIANT = 2; //int
	const DATA_COLOR = 3, DATA_COLOUR = 3; //byte
	const DATA_NAMETAG = 4; //string
	const DATA_OWNER_EID = 5; //long
	const DATA_TARGET_EID = 6; //long
	const DATA_AIR = 7; //short
	const DATA_POTION_COLOR = 8; //int (ARGB!)
	const DATA_POTION_AMBIENT = 9; //byte
	/* 10 (byte) */
	const DATA_HURT_TIME = 11; //int (minecart/boat)
	const DATA_HURT_DIRECTION = 12; //int (minecart/boat)
	const DATA_PADDLE_TIME_LEFT = 13; //float
	const DATA_PADDLE_TIME_RIGHT = 14; //float
	const DATA_EXPERIENCE_VALUE = 15; //int (xp orb)
	const DATA_MINECART_DISPLAY_BLOCK = 16; //int (id | (data << 16))
	const DATA_MINECART_DISPLAY_OFFSET = 17; //int
	const DATA_MINECART_HAS_DISPLAY = 18; //byte (must be 1 for minecart to show block inside)

	//TODO: add more properties

	const DATA_ENDERMAN_HELD_ITEM_ID = 23; //short
	const DATA_ENDERMAN_HELD_ITEM_DAMAGE = 24; //short
	const DATA_ENTITY_AGE = 25; //short

	/* 27 (byte) player-specific flags
	 * 28 (int) player "index"?
	 * 29 (block coords) bed position */
	const DATA_FIREBALL_POWER_X = 30; //float
	const DATA_FIREBALL_POWER_Y = 31;
	const DATA_FIREBALL_POWER_Z = 32;
	/* 33 (unknown)
	 * 34 (float) fishing bobber
	 * 35 (float) fishing bobber
	 * 36 (float) fishing bobber */
	const DATA_POTION_AUX_VALUE = 37; //short
	const DATA_LEAD_HOLDER_EID = 38; //long
	const DATA_SCALE = 39; //float
	const DATA_INTERACTIVE_TAG = 40; //string (button text)
	const DATA_NPC_SKIN_ID = 41; //string
	const DATA_URL_TAG = 42; //string
	const DATA_MAX_AIR = 43; //short
	const DATA_MARK_VARIANT = 44; //int
	/* 45 (byte) container stuff
	 * 46 (int) container stuff
	 * 47 (int) container stuff */
	const DATA_BLOCK_TARGET = 48; //block coords (ender crystal)
	const DATA_WITHER_INVULNERABLE_TICKS = 49; //int
	const DATA_WITHER_TARGET_1 = 50; //long
	const DATA_WITHER_TARGET_2 = 51; //long
	const DATA_WITHER_TARGET_3 = 52; //long
	/* 53 (short) */
	const DATA_BOUNDING_BOX_WIDTH = 54; //float
	const DATA_BOUNDING_BOX_HEIGHT = 55; //float
	const DATA_FUSE_LENGTH = 56; //int
	const DATA_RIDER_SEAT_POSITION = 57; //vector3f
	const DATA_RIDER_ROTATION_LOCKED = 58; //byte
	const DATA_RIDER_MAX_ROTATION = 59; //float
	const DATA_RIDER_MIN_ROTATION = 60; //float
	const DATA_AREA_EFFECT_CLOUD_RADIUS = 61; //float
	const DATA_AREA_EFFECT_CLOUD_WAITING = 62; //int
	const DATA_AREA_EFFECT_CLOUD_PARTICLE_ID = 63; //int
	/* 64 (int) shulker-related */
	const DATA_SHULKER_ATTACH_FACE = 65; //byte
	/* 66 (short) shulker-related */
	const DATA_SHULKER_ATTACH_POS = 67; //block coords
	const DATA_TRADING_PLAYER_EID = 68; //long

	/* 70 (byte) command-block */
	const DATA_COMMAND_BLOCK_COMMAND = 71; //string
	const DATA_COMMAND_BLOCK_LAST_OUTPUT = 72; //string
	const DATA_COMMAND_BLOCK_TRACK_OUTPUT = 73; //byte
	const DATA_CONTROLLING_RIDER_SEAT_NUMBER = 74; //byte
	const DATA_STRENGTH = 75; //int
	const DATA_MAX_STRENGTH = 76; //int
	/* 77 (int) */
	const DATA_ARMOR_STAND_POSE_INDEX = 78; //int
	const DATA_ENDER_CRYSTAL_TIME_OFFSET = 79; //int
	const DATA_FLAGS2 = 91; //long (extended data flags)


	const DATA_FLAG_ONFIRE = 0;
	const DATA_FLAG_SNEAKING = 1;
	const DATA_FLAG_RIDING = 2;
	const DATA_FLAG_SPRINTING = 3;
	const DATA_FLAG_ACTION = 4;
	const DATA_FLAG_INVISIBLE = 5;
	const DATA_FLAG_TEMPTED = 6;
	const DATA_FLAG_INLOVE = 7;
	const DATA_FLAG_SADDLED = 8;
	const DATA_FLAG_POWERED = 9;
	const DATA_FLAG_IGNITED = 10;
	const DATA_FLAG_BABY = 11;
	const DATA_FLAG_CONVERTING = 12;
	const DATA_FLAG_CRITICAL = 13;
	const DATA_FLAG_CAN_SHOW_NAMETAG = 14;
	const DATA_FLAG_ALWAYS_SHOW_NAMETAG = 15;
	const DATA_FLAG_IMMOBILE = 16, DATA_FLAG_NO_AI = 16;
	const DATA_FLAG_SILENT = 17;
	const DATA_FLAG_WALLCLIMBING = 18;
	const DATA_FLAG_CAN_CLIMB = 19;
	const DATA_FLAG_SWIMMER = 20;
	const DATA_FLAG_CAN_FLY = 21;
	const DATA_FLAG_RESTING = 22;
	const DATA_FLAG_SITTING = 23;
	const DATA_FLAG_ANGRY = 24;
	const DATA_FLAG_INTERESTED = 25;
	const DATA_FLAG_CHARGED = 26;
	const DATA_FLAG_TAMED = 27;
	const DATA_FLAG_LEASHED = 28;
	const DATA_FLAG_SHEARED = 29;
	const DATA_FLAG_GLIDING = 30;
	const DATA_FLAG_ELDER = 31;
	const DATA_FLAG_MOVING = 32;
	const DATA_FLAG_BREATHING = 33;
	const DATA_FLAG_CHESTED = 34;
	const DATA_FLAG_STACKABLE = 35;
	const DATA_FLAG_SHOWBASE = 36;
	const DATA_FLAG_REARING = 37;
	const DATA_FLAG_VIBRATING = 38;
	const DATA_FLAG_IDLING = 39;
	const DATA_FLAG_EVOKER_SPELL = 40;
	const DATA_FLAG_CHARGE_ATTACK = 41;

	const DATA_FLAG_LINGER = 45;

	const SOUTH = 0;
	const WEST = 1;
	const NORTH = 2;
	const EAST = 3;

	public static $entityCount = 1;
	/** @var Entity[] */
	private static $knownEntities = [];
	private static $shortNames = [];

	public static function init(){
		Entity::registerEntity(Arrow::class);
		Entity::registerEntity(Bat::class);
		Entity::registerEntity(Blaze::class);
		Entity::registerEntity(Boat::class);
		Entity::registerEntity(CaveSpider::class);
		Entity::registerEntity(Chicken::class);
		Entity::registerEntity(Cow::class);
		Entity::registerEntity(Creeper::class);
		Entity::registerEntity(Donkey::class);
		Entity::registerEntity(DroppedItem::class);
		Entity::registerEntity(Egg::class);
		Entity::registerEntity(ElderGuardian::class);
		Entity::registerEntity(Enderman::class);
		Entity::registerEntity(Endermite::class);
		Entity::registerEntity(EnderDragon::class);
		Entity::registerEntity(EnderPearl::class);
		Entity::registerEntity(Evoker::class);
		Entity::registerEntity(FallingSand::class);
		Entity::registerEntity(FishingHook::class);
		Entity::registerEntity(Ghast::class);
		Entity::registerEntity(Guardian::class);
		Entity::registerEntity(Horse::class);
		Entity::registerEntity(Husk::class);
		Entity::registerEntity(IronGolem::class);
		Entity::registerEntity(LavaSlime::class); //Magma Cube
		Entity::registerEntity(Lightning::class);
		Entity::registerEntity(Llama::class);
		Entity::registerEntity(Minecart::class);
		Entity::registerEntity(MinecartChest::class);
		Entity::registerEntity(MinecartHopper::class);
		Entity::registerEntity(MinecartTNT::class);
		Entity::registerEntity(Mooshroom::class);
		Entity::registerEntity(Mule::class);
		Entity::registerEntity(Ocelot::class);
		Entity::registerEntity(Painting::class);
		Entity::registerEntity(Pig::class);
		Entity::registerEntity(PigZombie::class);
		Entity::registerEntity(PolarBear::class);
		Entity::registerEntity(PrimedTNT::class);
		Entity::registerEntity(Rabbit::class);
		Entity::registerEntity(Sheep::class);
		Entity::registerEntity(Shulker::class);
		Entity::registerEntity(Silverfish::class);
		Entity::registerEntity(Skeleton::class);
		Entity::registerEntity(SkeletonHorse::class);
		Entity::registerEntity(Slime::class);
		Entity::registerEntity(Snowball::class);
		Entity::registerEntity(SnowGolem::class);
		Entity::registerEntity(Spider::class);
		Entity::registerEntity(Squid::class);
		Entity::registerEntity(Stray::class);
		Entity::registerEntity(ThrownExpBottle::class);
		Entity::registerEntity(ThrownPotion::class);
		Entity::registerEntity(Vex::class);
		Entity::registerEntity(Villager::class);
		Entity::registerEntity(Vindicator::class);
		Entity::registerEntity(Witch::class);
		Entity::registerEntity(Wither::class);
		Entity::registerEntity(WitherSkeleton::class);
		Entity::registerEntity(Wolf::class);
		Entity::registerEntity(XPOrb::class);
		Entity::registerEntity(Zombie::class);
		Entity::registerEntity(ZombieHorse::class);
		Entity::registerEntity(ZombieVillager::class);
		Entity::registerEntity(WitherTNT::class);
		Entity::registerEntity(EnderCrystal::class);

		Entity::registerEntity(Human::class, true);

		Attribute::init();
		Effect::init();
	}

	/**
	 * @var Player[]
	 */
	protected $hasSpawned = [];

	/** @var Effect[] */
	protected $effects = [];

	protected $id;

	protected $dataFlags = 0;

	protected $dataProperties = [
		self::DATA_FLAGS => [self::DATA_TYPE_LONG, 0],
		self::DATA_AIR => [self::DATA_TYPE_SHORT, 400],
		self::DATA_MAX_AIR => [self::DATA_TYPE_SHORT, 400],
		self::DATA_NAMETAG => [self::DATA_TYPE_STRING, ""],
		self::DATA_LEAD_HOLDER_EID => [self::DATA_TYPE_LONG, -1],
		self::DATA_SCALE => [self::DATA_TYPE_FLOAT, 1],
//		self::DATA_BOUNDING_BOX_WIDTH => [self::DATA_TYPE_FLOAT, 0.6],
//		self::DATA_BOUNDING_BOX_HEIGHT => [self::DATA_TYPE_FLOAT, 1.8],
	];

	public $passenger = null;
	public $vehicle = null;

	/** @var Chunk|null */
	public $chunk;

	protected $lastDamageCause = null;

	/** @var Block[]|null */
	private $blocksAround = null;

	/** @var float */
	public $lastX;
	/** @var float */
	public $lastY;
	/** @var float */
	public $lastZ;

	public $motionX;
	public $motionY;
	public $motionZ;
	/** @var Vector3 */
	public $temporalVector;
	public $lastMotionX;
	public $lastMotionY;
	public $lastMotionZ;

	/** @var bool */
	protected $forceMovementUpdate = false;

	public $lastYaw;
	public $lastPitch;

	/** @var AxisAlignedBB */
	public $boundingBox;
	public $onGround;
	public $inBlock = false;
	public $positionChanged;
	public $motionChanged;
	/** @var int */
	public $deadTicks = 0;
	/** @var int */
	protected $maxDeadTicks = 0;
	/** @var int */
	protected $age = 0;

	public $height;

	public $eyeHeight = null;

	public $width;
	public $length;

	/** @var float */
	protected $baseOffset = 0.0;

	/** @var bool */
	private $savedWithChunk = true;

	/** @var float */
	private $health = 20.0;
	private $maxHealth = 20;

	protected $ySize = 0;
	protected $stepHeight = 0;
	public $keepMovement = false;

	public $fallDistance = 0.0;
	public $ticksLived = 0;
	public $lastUpdate;
	public $fireTicks = 0;
	public $namedtag;
	public $canCollide = true;

	protected $isStatic = false;

	public $isCollided = false;
	public $isCollidedHorizontally = false;
	public $isCollidedVertically = false;

	public $noDamageTicks;
	protected $justCreated = true;
	private $invulnerable;

	/** @var AttributeMap */
	protected $attributeMap;

	protected $gravity;
	protected $drag;

	/** @var Server */
	protected $server;

	public $closed = false;

	/** @var \pocketmine\event\TimingsHandler */
	protected $timings;
	protected $isPlayer = false;

	/** @var Entity */
	protected $linkedEntity = null;
	/** 0 no linked 1 linked other 2 be linked */
	protected $linkedType = null;


	protected $riding = null;

	public $dropExp = [0, 0];

	public $isMorph = false;

	/** @var bool */
	protected $constructed = false;

	/** @var bool */
	private $closeInFlight = false;

	public function __construct(Level $level, CompoundTag $nbt){
		$this->constructed = true;
		$this->timings = Timings::getEntityTimings($this);

		$this->isPlayer = $this instanceof Player;

		$this->temporalVector = new Vector3();

		if($this->eyeHeight === null){
			$this->eyeHeight = $this->height / 2 + 0.1;
		}

		$this->id = Entity::$entityCount++;
		$this->namedtag = $nbt;
		$this->server = $level->getServer();

		$this->chunk = $level->getChunkAtPosition($this, true);
		if($this->chunk === null){
			throw new \InvalidStateException("Cannot create entities in unloaded chunks");
		}
		
		$this->setLevel($level);
		
		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
		$this->setPositionAndRotation(
			$this->temporalVector->setComponents(
				$this->namedtag["Pos"][0],
				$this->namedtag["Pos"][1],
				$this->namedtag["Pos"][2]
			),
			$this->namedtag->Rotation[0],
			$this->namedtag->Rotation[1]
		);
		if(isset($this->namedtag->Motion)){
			$this->setMotion($this->temporalVector->setComponents($this->namedtag["Motion"][0], $this->namedtag["Motion"][1], $this->namedtag["Motion"][2]));
		}else{
			$this->setMotion($this->temporalVector->setComponents(0, 0, 0));
		}

		$this->resetLastMovements();

		assert(!is_nan($this->x) and !is_infinite($this->x) and !is_nan($this->y) and !is_infinite($this->y) and !is_nan($this->z) and !is_infinite($this->z));

		if(!isset($this->namedtag->FallDistance)){
			$this->namedtag->FallDistance = new FloatTag("FallDistance", 0.0);
		}
		$this->fallDistance = $this->namedtag["FallDistance"];

		if(!isset($this->namedtag->Fire)){
			$this->namedtag->Fire = new ShortTag("Fire", 0);
		}
		$this->fireTicks = $this->namedtag["Fire"];
		if($this->isOnFire()){
			$this->setGenericFlag(self::DATA_FLAG_ONFIRE);
		}

		if(!isset($this->namedtag->Air)){
			$this->namedtag->Air = new ShortTag("Air", 300);
		}
		$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, $this->namedtag["Air"]);

		if(!isset($this->namedtag->OnGround)){
			$this->namedtag->OnGround = new ByteTag("OnGround", 0);
		}
		$this->onGround = $this->namedtag["OnGround"] > 0 ? true : false;

		if(!isset($this->namedtag->Invulnerable)){
			$this->namedtag->Invulnerable = new ByteTag("Invulnerable", 0);
		}
		$this->invulnerable = $this->namedtag["Invulnerable"] > 0 ? true : false;

		$this->attributeMap = new AttributeMap();
		$this->addAttributes();

		$this->initEntity();

		$this->chunk->addEntity($this);
		$this->level->addEntity($this);
		
		$this->lastUpdate = $this->server->getTick();
		$this->server->getPluginManager()->callEvent(new EntitySpawnEvent($this));

		$this->scheduleUpdate();

	}

	public static function createBaseNBT(Vector3 $position, Vector3 $motion, float $yaw, float $pitch) : CompoundTag{
        return new CompoundTag("", [
            "Pos" => new ListTag("Pos", [
                new DoubleTag("", $position->x),
                new DoubleTag("", $position->y),
                new DoubleTag("", $position->z)
            ]),
            "Motion" => new ListTag("Motion", [
                new DoubleTag("", $motion->x),
                new DoubleTag("", $motion->y),
                new DoubleTag("", $motion->z)
            ]),
            "Rotation" => new ListTag("Rotation", [
                new FloatTag("", $yaw),
                new FloatTag("", $pitch),
            ]),
        ]);
    }

	//add original function (use create AI etc)

	/**
	 * @return mixed
	 */
	public function getHeight(){
		return $this->height;
	}

	/**
	 * @return mixed
	 */
	public function getWidth(){
		return $this->width;
	}

	/**
	 * @return mixed
	 */
	public function getLength(){
		return $this->length;
	}

	/**
	 * @param $value
	 */
	public function setScale(float $value) : void{
		if($value <= 0){
			throw new \InvalidArgumentException("Scale must be greater than 0");
		}
		$multiplier = $value / $this->getScale();

		$this->width *= $multiplier;
		$this->height *= $multiplier;
		$this->eyeHeight *= $multiplier;

		$this->recalculateBoundingBox();

		$this->setDataProperty(self::DATA_SCALE, self::DATA_TYPE_FLOAT, $value);
	}

	/**
	 * @return mixed
	 */
	public function getScale(){
		return $this->getDataProperty(self::DATA_SCALE, self::DATA_TYPE_FLOAT);
	}

	/**
	 * @return int
	 */
	public function getDropExpMin() : int{
		return $this->dropExp[0];
	}

	/**
	 * @return int
	 */
	public function getDropExpMax() : int{
		return $this->dropExp[1];
	}

	/**
	 * @return string
	 */
	public function getNameTag(){
		return $this->getDataProperty(self::DATA_NAMETAG);
	}

	/**
	 * @return bool
	 */
	public function isNameTagVisible(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_SHOW_NAMETAG);
	}

	/**
	 * @return bool
	 */
	public function isNameTagAlwaysVisible(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ALWAYS_SHOW_NAMETAG);
	}

	/**
	 * @param string $name
	 */
	public function setNameTag($name){
		$this->setDataProperty(self::DATA_NAMETAG, self::DATA_TYPE_STRING, $name);
	}

	/**
	 * @param bool $value
	 */
	public function setNameTagVisible($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_SHOW_NAMETAG, $value);
	}

	/**
	 * @param bool $value
	 */
	public function setNameTagAlwaysVisible($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ALWAYS_SHOW_NAMETAG, $value);
	}

	/**
	 * @return bool
	 */
	public function isSneaking(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SNEAKING);
	}

	/**
	 * @param bool $value
	 */
	public function setSneaking($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SNEAKING, (bool) $value);
	}

	/**
	 * @return bool
	 */
	public function isSprinting(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SPRINTING);
	}

	/**
	 * @param bool $value
	 */
	public function setSprinting($value = true){
		if($value !== $this->isSprinting()){
			$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SPRINTING, (bool) $value);
			$attr = $this->attributeMap->getAttribute(Attribute::MOVEMENT_SPEED);
			$attr->setValue($value ? ($attr->getValue() * 1.3) : ($attr->getValue() / 1.3), false, true);
		}
	}

	/**
	 * @return bool
	 */
	public function isGliding(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_IDLING);
	}

	/**
	 * @param bool $value
	 */
	public function setGliding($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_GLIDING, (bool) $value);
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_IDLING, (bool) $value);
	}

	/**
	 * @return bool
	 */
	public function isImmobile() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_IMMOBILE);
	}

	/**
	 * @param bool $value
	 */
	public function setImmobile($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_IMMOBILE, $value);
	}

	public function isInvisible() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_INVISIBLE);
	}

	public function setInvisible(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_INVISIBLE, $value);
	}

	/**
	 * Returns whether the entity is able to climb blocks such as ladders or vines.
	 *
	 * @return bool
	 */
	public function canClimb() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_CLIMB);
	}

	/**
	 * Sets whether the entity is able to climb climbable blocks.
	 *
	 * @param bool $value
	 */
	public function setCanClimb(bool $value){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_CLIMB, $value);
	}

	/**
	 * Returns whether this entity is climbing a block. By default this is only true if the entity is climbing a ladder or vine or similar block.
	 *
	 * @return bool
	 */
	public function canClimbWalls() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_WALLCLIMBING);
	}

	/**
	 * Sets whether the entity is climbing a block. If true, the entity can climb anything.
	 *
	 * @param bool $value
	 */
	public function setCanClimbWalls(bool $value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_WALLCLIMBING, $value);
	}
	
	/**
	 * Returns the entity ID of the owning entity, or null if the entity doesn't have an owner.
	 * @return int|string|null
	 */
	public function getOwningEntityId(){
		return $this->getDataProperty(self::DATA_OWNER_EID);
	}
	
	/**
	 * Returns the owning entity, or null if the entity was not found.
	 * @return Entity|null
	 */
	public function getOwningEntity(){
		$eid = $this->getOwningEntityId();
		if($eid !== null){
			return $this->server->findEntity($eid);
		}

		return null;
	}
	
	/**
	 * Sets the owner of the entity.
	 *
	 * @param Entity $owner
	 *
	 * @throws \InvalidArgumentException if the supplied entity is not valid
	 */
	public function setOwningEntity(Entity $owner){
		if($owner->closed){
			throw new \InvalidArgumentException("Supplied owning entity is garbage and cannot be used");
			return false;
		}
		
		$this->setDataProperty(self::DATA_OWNER_EID, self::DATA_TYPE_LONG, $owner->getId());
		return true;
	}

	/**
	 * Returns the entity ID of the entity's target, or null if it doesn't have a target.
	 * @return int|string|null
	 */
	public function getTargetEntityId(){
		return $this->getDataProperty(self::DATA_TARGET_EID);
	}

	/**
	 * Returns the entity's target entity, or null if not found.
	 * This is used for things like hostile mobs attacking entities, and for fishing rods reeling hit entities in.
	 *
	 * @return Entity|null
	 */
	public function getTargetEntity(){
		$eid = $this->getTargetEntityId();
		if($eid !== null){
			return $this->server->findEntity($eid);
		}

		return null;
	}

	/**
	 * Sets the entity's target entity.
	 *
	 * @param Entity $target
	 *
	 * @throws \InvalidArgumentException if the target entity is not valid
	 */
	public function setTargetEntity(Entity $target){
		if($target->closed){
			throw new \InvalidArgumentException("Supplied target entity is garbage and cannot be used");
		}

		$this->setDataProperty(self::DATA_TARGET_EID, self::DATA_TYPE_LONG, $target->getId());
	}

	/**
	 * @return Effect[]
	 */
	public function getEffects(){
		return $this->effects;
	}

	public function removeAllEffects(){
		foreach($this->effects as $effect){
			$this->removeEffect($effect->getId());
		}
	}

	/**
	 * @param $effectId
	 *
	 * @return bool
	 */
	public function removeEffect($effectId){
		if(isset($this->effects[$effectId])){
			$effect = $this->effects[$effectId];
			$hasExpired = $effect->hasExpired();
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityEffectRemoveEvent($this, $effectId));
			if($effectId === Effect::ABSORPTION and $this instanceof Human){
				$this->setAbsorption(0);
			}
			if($ev->isCancelled()){
				if($hasExpired and !$ev->getEffect()->hasExpired()){ //altered duration of an expired effect to make it not get removed
					$ev->getEffect()->add($this, true);
				}
			    return false;
		    }

			unset($this->effects[$effectId]);
			$effect->remove($this);

			$this->recalculateEffectColor();

			return true;
		}

		return false;
	}


	/**
	 * @param $effectId
	 *
	 * @return null|Effect
	 */
	public function getEffect($effectId){
		return isset($this->effects[$effectId]) ? $this->effects[$effectId] : null;
	}

	/**
	 * @param $effectId
	 *
	 * @return bool
	 */
	public function hasEffect($effectId){
		return isset($this->effects[$effectId]);
	}

	/**
	 * @param Effect $effect
	 *
	 * @return bool
	 */
	public function addEffect(Effect $effect){
		Server::getInstance()->getPluginManager()->callEvent($ev = new EntityEffectAddEvent($this, $effect));
		if($ev->isCancelled()){
			return false;
		}
		if($effect->getId() === Effect::HEALTH_BOOST){
			$this->setHealth($this->getHealth() + 4 * ($effect->getAmplifier() + 1));
		}
		if($effect->getId() === Effect::ABSORPTION and $this instanceof Human){
			$this->setAbsorption(4 * ($effect->getAmplifier() + 1));
		}

		if(isset($this->effects[$effect->getId()])){
			$oldEffect = $this->effects[$effect->getId()];
			if(($effect->getAmplifier() < ($oldEffect->getAmplifier())) and $effect->getDuration() < $oldEffect->getDuration()){
				return false;
			}
			$effect->add($this, true, $oldEffect);
		}else{
			$effect->add($this, false);
		}

		$this->effects[$effect->getId()] = $effect;

		$this->recalculateEffectColor();

		return true;
	}

	protected function recalculateEffectColor(){
		//TODO: add transparency values
		$color = [0, 0, 0]; //RGB
		$count = 0;
		$ambient = true;
		foreach($this->effects as $effect){
			if($effect->isVisible()){
				$c = $effect->getColor();
				$color[0] += $c[0] * $effect->getEffectLevel();
				$color[1] += $c[1] * $effect->getEffectLevel();
				$color[2] += $c[2] * $effect->getEffectLevel();
				$count += $effect->getEffectLevel();
				if(!$effect->isAmbient()){
					$ambient = false;
				}
			}
		}

		if($count > 0){
			$r = ($color[0] / $count) & 0xff;
			$g = ($color[1] / $count) & 0xff;
			$b = ($color[2] / $count) & 0xff;

			$this->setDataProperty(Entity::DATA_POTION_COLOR, Entity::DATA_TYPE_INT, 0xff000000 | ($r << 16) | ($g << 8) | $b);
			$this->setDataProperty(Entity::DATA_POTION_AMBIENT, Entity::DATA_TYPE_BYTE, $ambient ? 1 : 0);
		}else{
			$this->setDataProperty(Entity::DATA_POTION_COLOR, Entity::DATA_TYPE_INT, 0);
			$this->setDataProperty(Entity::DATA_POTION_AMBIENT, Entity::DATA_TYPE_BYTE, 0);
		}
	}

	/**
	 * @param int|string  $type
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 * @param             $args
	 *
	 * @return Entity|Projectile
	 */
	public static function createEntity($type, Level $level, CompoundTag $nbt, ...$args){
		if(isset(self::$knownEntities[$type])){
			$class = self::$knownEntities[$type];

			return new $class($level, $nbt, ...$args);
		}

		return null;
	}

	/**
	 * @param      $className
	 * @param bool $force
	 *
	 * @return bool
	 */
	public static function registerEntity($className, $force = false){
		$class = new \ReflectionClass($className);
		if(is_a($className, Entity::class, true) and !$class->isAbstract()){
			if($className::NETWORK_ID !== -1){
				self::$knownEntities[$className::NETWORK_ID] = $className;
			}elseif(!$force){
				return false;
			}

			self::$knownEntities[$class->getShortName()] = $className;
			self::$shortNames[$className] = $class->getShortName();

			return true;
		}

		return false;
	}

	/**
	 * Returns the short save name
	 *
	 * @return string
	 */
	public function getSaveId(){
		return self::$shortNames[static::class];
	}

	public function saveNBT(){
		if(!($this instanceof Player)){
			$this->namedtag->id = new StringTag("id", $this->getSaveId());
			
			if($this->getNameTag() !== ""){
				$this->namedtag->CustomName = new StringTag("CustomName", $this->getNameTag());
				$this->namedtag->CustomNameVisible = new StringTag("CustomNameVisible", $this->isNameTagVisible());
				$this->namedtag->CustomNameAlwaysVisible = new StringTag("CustomNameAlwaysVisible", $this->isNameTagAlwaysVisible());
			}else{
				unset($this->namedtag->CustomName);
				unset($this->namedtag->CustomNameVisible);
				unset($this->namedtag->CustomNameAlwaysVisible);
			}
		}

		$this->namedtag->Pos = new ListTag("Pos", [
			new DoubleTag(0, $this->x),
			new DoubleTag(1, $this->y),
			new DoubleTag(2, $this->z)
		]);

		$this->namedtag->Motion = new ListTag("Motion", [
			new DoubleTag(0, $this->motionX),
			new DoubleTag(1, $this->motionY),
			new DoubleTag(2, $this->motionZ)
		]);

		$this->namedtag->Rotation = new ListTag("Rotation", [
			new FloatTag(0, $this->yaw),
			new FloatTag(1, $this->pitch)
		]);

		$this->namedtag->FallDistance = new FloatTag("FallDistance", $this->fallDistance);
		$this->namedtag->Fire = new ShortTag("Fire", $this->fireTicks);
		$this->namedtag->Air = new ShortTag("Air", $this->getDataProperty(self::DATA_AIR));
		$this->namedtag->OnGround = new ByteTag("OnGround", $this->onGround ? 1 : 0);
		$this->namedtag->Invulnerable = new ByteTag("Invulnerable", $this->invulnerable ? 1 : 0);

		if(count($this->effects) > 0){
			$effects = [];
			foreach($this->effects as $effect){
				$effects[$effect->getId()] = new CompoundTag($effect->getId(), [
					"Id" => new ByteTag("Id", $effect->getId()),
					"Amplifier" => new ByteTag("Amplifier", Binary::signByte($effect->getAmplifier())),
					"Duration" => new IntTag("Duration", $effect->getDuration()),
					"Ambient" => new ByteTag("Ambient", 0),
					"ShowParticles" => new ByteTag("ShowParticles", $effect->isVisible() ? 1 : 0)
				]);
			}

			$this->namedtag->ActiveEffects = new ListTag("ActiveEffects", $effects);
		}else{
			unset($this->namedtag->ActiveEffects);
		}
	}

	protected function initEntity(){
		if(!($this->namedtag instanceof CompoundTag)){
			throw new \InvalidArgumentException("Expecting CompoundTag, received " . get_class($this->namedtag));
		}

		if(isset($this->namedtag->CustomName)){
			$this->setNameTag($this->namedtag["CustomName"]);
			if(isset($this->namedtag->CustomNameVisible)){
				$this->setNameTagVisible($this->namedtag["CustomNameVisible"] > 0);
			}
			if(isset($this->namedtag->CustomNameAlwaysVisible)){
				$this->setNameTagAlwaysVisible($this->namedtag["CustomNameAlwaysVisible"] > 0);
			}
		}

		$this->addAttributes();

		if(isset($this->namedtag->ActiveEffects)){
			foreach($this->namedtag->ActiveEffects->getValue() as $e){
				$amplifier = Binary::unsignByte($e->Amplifier->getValue()); //0-255 only

				$effect = Effect::getEffect($e["Id"]);
				if($effect === null){
					continue;
				}

				$effect->setAmplifier($amplifier)->setDuration($e["Duration"])->setVisible($e["ShowParticles"] > 0);

				$this->addEffect($effect);
			}
		}

	}

	protected function addAttributes(){
	}

	/**
	 * @return Player[]
	 */
	public function getViewers(){
		return $this->hasSpawned;
	}

	public function spawnTo(Player $player){
		if(
			!isset($this->hasSpawned[$player->getLoaderId()]) and
			$this->chunk !== null and
			isset($player->usedChunks[$chunkHash = Level::chunkHash($this->chunk->getX(), $this->chunk->getZ())]) and
			$player->usedChunks[$chunkHash] === true
		){
			$this->hasSpawned[$player->getLoaderId()] = $player;
		}
	}

	/**
	 * @param Player $player
	 */
	public function sendPotionEffects(Player $player){
		foreach($this->effects as $effect){
			$pk = new MobEffectPacket();
			$pk->eid = $this->id;
			$pk->effectId = $effect->getId();
			$pk->amplifier = $effect->getAmplifier();
			$pk->particles = $effect->isVisible();
			$pk->duration = $effect->getDuration();
			$pk->eventId = MobEffectPacket::EVENT_ADD;

			$player->dataPacket($pk);
		}
	}

	/**
	 * @param Player[]|Player $player
	 * @param array           $data Properly formatted entity data, defaults to everything
	 */
	public function sendData($player, array $data = null){
		if(!is_array($player)){
			$player = [$player];
		}

        //frida-trace -U -i "_ZN19SetEntityDataPacket4readER12BinaryStream" com.mojang.minecraftpe
		$pk = new SetEntityDataPacket();
		$pk->eid = $this->getId();
		$pk->metadata = $data === null ? $this->dataProperties : $data;

		foreach($player as $p){
			if($p === $this){
				continue;
			}
			$p->dataPacket(clone $pk);
		}
		if($this instanceof Player){
			$this->dataPacket($pk);
		}
	}

	/**
	 * @param Player[]|null $players
	 */
	public function broadcastEntityEvent(int $eventId, ?int $eventData = null, ?array $players = null) : void{
		$pk = new EntityEventPacket();
		$pk->eid = $this->id;
		$pk->event = $eventId;
		$pk->data = $eventData ?? 0;

		$this->server->broadcastPacket($players ?? $this->getViewers(), $pk);
	}

	/**
	 * @deprecated WARNING: This function DOES NOT permanently hide the entity from the player. As soon as the entity or
	 * player moves, the player will once again be able to see the entity.
	 */
	public function despawnFrom(Player $player, bool $send = true){
		if(isset($this->hasSpawned[$player->getLoaderId()])){
			if($send){
				$pk = new RemoveEntityPacket();
				$pk->eid = $this->id;
				$player->dataPacket($pk);
			}
			unset($this->hasSpawned[$player->getLoaderId()]);
		}
	}

	/**
	 * @param float             $damage
	 * @param EntityDamageEvent $source
	 *
	 * @return bool
	 */
	public function attack($damage, EntityDamageEvent $source){
		if($this->hasEffect(Effect::FIRE_RESISTANCE)
			and ($source->getCause() === EntityDamageEvent::CAUSE_FIRE
				or $source->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK
				or $source->getCause() === EntityDamageEvent::CAUSE_LAVA)
		){
			$source->setCancelled();
		}

		$this->server->getPluginManager()->callEvent($source);
		if($source->isCancelled()){
			return false;
		}
		$this->setLastDamageCause($source);

		if($this instanceof Human){
			$damage = round($source->getFinalDamage());
			if($this->getAbsorption() > 0){
				$absorption = $this->getAbsorption() - $damage;
				$this->setAbsorption($absorption <= 0 ? 0 : $absorption);
				$this->setHealth($this->getHealth() + $absorption);
			}else{
				$this->setHealth($this->getHealth() - $damage);
			}
		}else{
			$this->setHealth($this->getHealth() - round($source->getFinalDamage()));
		}

		return true;
	}

	/**
	 * @param float                   $amount
	 * @param EntityRegainHealthEvent $source
	 *
	 */
	public function heal($amount, EntityRegainHealthEvent $source){
		$this->server->getPluginManager()->callEvent($source);
		if($source->isCancelled()){
			return;
		}

		$this->setHealth($this->getHealth() + $source->getAmount());
	}

	/**
	 * @return float
	 */
	public function getHealth() : float{
		return $this->health;
	}

	/**
	 * @return bool
	 */
	public function isAlive(){
		return $this->health > 0;
	}

	/**
	 * Returns whether this entity will be saved when its chunk is unloaded.
	 */
	public function canSaveWithChunk() : bool{
		return $this->savedWithChunk;
	}

	/**
	 * Sets whether this entity will be saved when its chunk is unloaded. This can be used to prevent the entity being
	 * saved to disk.
	 */
	public function setCanSaveWithChunk(bool $value) : void{
		$this->savedWithChunk = $value;
	}

	/**
	 * Sets the health of the Entity. This won't send any update to the players
	 *
	 * @param float $amount
	 */
	public function setHealth(float $amount){
		if($amount == $this->health){
			return;
		}

		if($amount <= 0){
			if($this->isAlive()){
				$this->health = 0;
				$this->kill();
			}
		}elseif($amount <= $this->getMaxHealth() or $amount < $this->health){
			$this->health = $amount;
		}else{
			$this->health = $this->getMaxHealth();
		}
	}

	/**
	 * @param EntityDamageEvent $type
	 */
	public function setLastDamageCause(EntityDamageEvent $type){
		$this->lastDamageCause = $type;
	}

	/**
	 * @return EntityDamageEvent|null
	 */
	public function getLastDamageCause(){
		return $this->lastDamageCause;
	}

	public function getAttributeMap() : AttributeMap{
		return $this->attributeMap;
	}

	/**
	 * @return int
	 */
	public function getMaxHealth(){
		return $this->maxHealth + ($this->hasEffect(Effect::HEALTH_BOOST) ? 4 * ($this->getEffect(Effect::HEALTH_BOOST)->getAmplifier() + 1) : 0);
	}

	/**
	 * @param int $amount
	 */
	public function setMaxHealth(int $amount){
		$this->maxHealth = $amount;
	}

	/**
	 * @param Entity $entity
	 *
	 * @return bool
	 */
	public function canCollideWith(Entity $entity){
		return !$this->justCreated and $entity !== $this;
	}

	public function canBeCollidedWith() : bool{
		return $this->isAlive();
	}

	/**
	 * @param $x
	 * @param $y
	 * @param $z
	 *
	 * @return bool
	 */
	protected function checkObstruction($x, $y, $z){
		if(count($this->level->getCollisionCubes($this, $this->getBoundingBox(), false)) === 0){
			return false;
		}
		
		$i = (int) floor($x);
		$j = (int) floor($y);
		$k = (int) floor($z);

		$diffX = $x - $i;
		$diffY = $y - $j;
		$diffZ = $z - $k;

		if(Block::$solid[$this->level->getBlockIdAt($i, $j, $k)]){
			$flag = !Block::$solid[$this->level->getBlockIdAt($i - 1, $j, $k)];
			$flag1 = !Block::$solid[$this->level->getBlockIdAt($i + 1, $j, $k)];
			$flag2 = !Block::$solid[$this->level->getBlockIdAt($i, $j - 1, $k)];
			$flag3 = !Block::$solid[$this->level->getBlockIdAt($i, $j + 1, $k)];
			$flag4 = !Block::$solid[$this->level->getBlockIdAt($i, $j, $k - 1)];
			$flag5 = !Block::$solid[$this->level->getBlockIdAt($i, $j, $k + 1)];

			$direction = -1;
			$limit = 9999;

			if($flag){
				$limit = $diffX;
				$direction = 0;
			}

			if($flag1 and 1 - $diffX < $limit){
				$limit = 1 - $diffX;
				$direction = 1;
			}

			if($flag2 and $diffY < $limit){
				$limit = $diffY;
				$direction = 2;
			}

			if($flag3 and 1 - $diffY < $limit){
				$limit = 1 - $diffY;
				$direction = 3;
			}

			if($flag4 and $diffZ < $limit){
				$limit = $diffZ;
				$direction = 4;
			}

			if($flag5 and 1 - $diffZ < $limit){
				$direction = 5;
			}

			$force = lcg_value() * 0.2 + 0.1;

			if($direction === 0){
				$this->motionX = -$force;

				return true;
			}

			if($direction === 1){
				$this->motionX = $force;

				return true;
			}

			if($direction === 2){
				$this->motionY = -$force;

				return true;
			}

			if($direction === 3){
				$this->motionY = $force;

				return true;
			}

			if($direction === 4){
				$this->motionZ = -$force;

				return true;
			}

			if($direction === 5){
				$this->motionZ = $force;

				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $tickDiff
	 *
	 * @return bool
	 */
	public function entityBaseTick($tickDiff = 1){

		Timings::$timerEntityBaseTick->startTiming();
		//TODO: check vehicles

		//$this->blocksAround = null;
		$this->justCreated = false;

		if(!$this->isAlive()){
			$this->removeAllEffects();
			$this->despawnFromAll();
			if(!$this->isPlayer){
				$this->close();
			}

			Timings::$timerEntityBaseTick->stopTiming();

			return false;
		}

		if(count($this->effects) > 0){
			foreach($this->effects as $effect){
				if($effect->canTick()){
					$effect->applyEffect($this);
				}
				$effect->setDuration(max(0, $effect->getDuration() - $tickDiff));
			    if($effect->getDuration() <= 0){
					$this->removeEffect($effect->getId());
				}
			}
		}

		$hasUpdate = false;

		$this->checkBlockCollision();

		if($this->y <= -16 and $this->isAlive()){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_VOID, 10);
			$this->attack($ev->getFinalDamage(), $ev);
			$hasUpdate = true;
		}

		if($this->fireTicks > 0){
			if($this->isFireProof()){
				if($this->fireTicks > 1){
					$this->fireTicks = 1;
				}else{
					$this->fireTicks -= 1;
				}
			}else{
				if(!$this->hasEffect(Effect::FIRE_RESISTANCE) and (($this->fireTicks % 20) === 0 or $tickDiff > 20)){
					$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FIRE_TICK, 1);
					$this->attack($ev->getFinalDamage(), $ev);
				}
				$this->fireTicks -= $tickDiff;
			}

			if($this->fireTicks <= 0 && $this->fireTicks > -10){
				$this->extinguish();
			}else{
				$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ONFIRE, true);
				$hasUpdate = true;
			}
		}

		if($this->noDamageTicks > 0){
			$this->noDamageTicks -= $tickDiff;
			if($this->noDamageTicks < 0){
				$this->noDamageTicks = 0;
			}
		}

		$this->age += $tickDiff;
		$this->ticksLived += $tickDiff;

		Timings::$timerEntityBaseTick->stopTiming();

		return $hasUpdate;
	}

	protected function updateMovement(bool $teleport = false){
		$diffPosition = ($this->x - $this->lastX) ** 2 + ($this->y - $this->lastY) ** 2 + ($this->z - $this->lastZ) ** 2;
		$diffRotation = ($this->yaw - $this->lastYaw) ** 2 + ($this->pitch - $this->lastPitch) ** 2;

		$diffMotion = $this->getMotion()->subtract($this->getLastMotion())->lengthSquared();

		$still = $this->getMotion()->lengthSquared() == 0.0;
		$wasStill = $this->getLastMotion()->lengthSquared() == 0.0;
		if($wasStill !== $still){
			//TODO: hack for client-side AI interference: prevent client sided movement when motion is 0
			$this->setImmobile($still);
		}

		if($teleport or $diffPosition > 0.0001 or $diffRotation > 1.0 or (!$wasStill and $still)){
			$this->lastX = $this->x;
			$this->lastY = $this->y;
			$this->lastZ = $this->z;

			$this->lastYaw = $this->yaw;
			$this->lastPitch = $this->pitch;

			$this->broadcastMovement($teleport);
		}

		if($diffMotion > 0.0025 or $wasStill !== $still){ //0.05 ** 2
			$this->lastMotionX = $this->motionX;
			$this->lastMotionY = $this->motionY;
			$this->lastMotionZ = $this->motionZ;

			$this->broadcastMotion();
		}
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		return new Vector3($vector3->x, $vector3->y + $this->baseOffset, $vector3->z);
	}

	protected function broadcastMovement(bool $teleport = false) : void{
		$pk = new MoveEntityPacket();
		$pk->eid = $this->id;
		$fix = $this->getOffsetPosition($this);
		$pk->x = $fix->x;
        $pk->y = $fix->y;
        $pk->z = $fix->z;
		$pk->yaw = $this->yaw;
		$pk->headYaw = $this->yaw;
		$pk->pitch = $this->pitch;
		//$pk->onGround = $this->onGround;
		$pk->teleported = $teleport;

		$this->level->broadcastPacketToViewers($this, $pk);
	}

	protected function broadcastMotion() : void{
		$pk = new SetEntityMotionPacket();
		$pk->eid = $this->id;
		$pk->motionX = $this->motionX;
		$pk->motionY = $this->motionY;
		$pk->motionZ = $this->motionZ;

		$this->level->broadcastPacketToViewers($this, $pk);
	}

	/**
	 * @return Vector3
	 */
	public function getDirectionVector(){
		$y = -sin(deg2rad($this->pitch));
		$xz = cos(deg2rad($this->pitch));
		$x = -$xz * sin(deg2rad($this->yaw));
		$z = $xz * cos(deg2rad($this->yaw));

		return $this->temporalVector->setComponents($x, $y, $z)->normalize();
	}

	/**
	 * @return Vector2
	 */
	public function getDirectionPlane(){
		return (new Vector2(-cos(deg2rad($this->yaw) - M_PI_2), -sin(deg2rad($this->yaw) - M_PI_2)))->normalize();
	}

	/**
	 * @param $currentTick
	 *
	 * @return bool
	 */
	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}

		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 0){
			if(!$this->justCreated){
				$this->server->getLogger()->debug("Expected tick difference of at least 1, got $tickDiff for " . get_class($this));
			}

			return true;
		}

		$this->lastUpdate = $currentTick;

		if(!$this->isAlive()){
			++$this->deadTicks;
			if($this->deadTicks >= $this->maxDeadTicks){
				$this->despawnFromAll();
				if(!$this->isPlayer){
					$this->close();
				}
			}

			return $this->deadTicks < $this->maxDeadTicks;
		}

		$this->updateMovement();

		$this->timings->startTiming();
		$hasUpdate = $this->entityBaseTick($tickDiff);
		$this->timings->stopTiming();

		//if($this->isStatic())
		return $hasUpdate;
		//return !($this instanceof Player);
	}

	public function onNearbyBlockChange() : void{
		$this->setForceMovementUpdate();
		$this->scheduleUpdate();
	}

	/**
	 * Flags the entity as needing a movement update on the next tick. Setting this forces a movement update even if the
	 * entity's motion is zero. Used to trigger movement updates when blocks change near entities.
	 */
	final public function setForceMovementUpdate(bool $value = true) : void{
		$this->forceMovementUpdate = $value;

		$this->blocksAround = null;
	}

	public final function scheduleUpdate(){
		if($this->closed){
			return;
			//throw new \InvalidStateException("Cannot schedule update on garbage entity " . get_class($this));
		}
		
		$this->level->updateEntities[$this->id] = $this;
	}

	/**
	 * @return bool
	 */
	public function isOnFire(){
		return $this->fireTicks > 0;
	}

	/**
	 * @param $seconds
	 */
	public function setOnFire($seconds){
		$ticks = $seconds * 20;
		if($ticks > $this->fireTicks){
			$this->fireTicks = $ticks;
		}
	}

	/**
	 * @return int
	 */
	public function getFireTicks() : int{
		return $this->fireTicks;
	}

	/**
	 * @param int $fireTicks
	 */
	public function setFireTicks(int $fireTicks) : void{
		$this->fireTicks = $fireTicks;
	}

	/**
	 * @return bool
	 */
	public function isFireProof() : bool{
		return false;
	}

	/**
	 * @return int|null
	 */
	public function getDirection(){
		$rotation = fmod($this->yaw - 90, 360);
		if($rotation < 0){
			$rotation += 360.0;
		}
		if((0 <= $rotation and $rotation < 45) or (315 <= $rotation and $rotation < 360)){
			return 2; //North
		}elseif(45 <= $rotation and $rotation < 135){
			return 3; //East
		}elseif(135 <= $rotation and $rotation < 225){
			return 0; //South
		}elseif(225 <= $rotation and $rotation < 315){
			return 1; //West
		}else{
			return null;
		}
	}

	public function extinguish(){
		$this->fireTicks = 0;
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ONFIRE, false);
	}

	/**
	 * @return bool
	 */
	public function canTriggerWalking(){
		return true;
	}

	public function resetFallDistance(){
		$this->fallDistance = 0.0;
	}

	/**
	 * @param $distanceThisTick
	 * @param $onGround
	 */
	protected function updateFallState($distanceThisTick, $onGround){
		if($onGround){
			if($this->fallDistance > 0){
				if($this instanceof Living){
					$this->fall($this->fallDistance);
				}
				$this->resetFallDistance();
			}
		}elseif($distanceThisTick < $this->fallDistance){
			//we've fallen some distance (distanceThisTick is negative)
			//or we ascended back towards where fall distance was measured from initially (distanceThisTick is positive but less than existing fallDistance)
			$this->fallDistance -= $distanceThisTick;
		}else{
			//we ascended past the apex where fall distance was originally being measured from
			//reset it so it will be measured starting from the new, higher position
			$this->fallDistance = 0;
		}
	}

	/**
	 * @return AxisAlignedBB
	 */
	public function getBoundingBox(){
		return $this->boundingBox;
	}

	protected function recalculateBoundingBox() : void{
		$halfWidth = $this->width / 2;

		$this->boundingBox->setBounds(
			$this->x - $halfWidth,
			$this->y + $this->ySize,
			$this->z - $halfWidth,
			$this->x + $halfWidth,
			$this->y + $this->height + $this->ySize,
			$this->z + $halfWidth
		);
	}

	/**
	 * @param $fallDistance
	 */
	public function fall($fallDistance){
		if($this instanceof Player and $this->isSpectator()){
			return;
		}
		if($fallDistance > 3){
			$this->getLevel()->addParticle(new DestroyBlockParticle($this, $this->getLevel()->getBlock($this->floor()->subtract(0, 1, 0))));
		}
		if($this->isInsideOfWater()){
			return;
		}
		$damage = ceil($fallDistance - 3 - ($this->hasEffect(Effect::JUMP) ? $this->getEffect(Effect::JUMP)->getEffectLevel() : 0));

		//Get the block directly beneath the player's feet, check if it is a slime block
		if($this->getLevel()->getBlock($this->floor()->subtract(0, 1, 0)) instanceof SlimeBlock){
			$damage = 0;
		}
		//TODO Improve
		if($this instanceof Player){
			if($this->getInventory()->getChestplate() instanceof Elytra){
				$damage = 0;
			}
		}
		if($damage > 0){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FALL, $damage);
			$this->attack($ev->getFinalDamage(), $ev);
		}
	}

	/**
	 * @return float|int|null
	 */
	public function getEyeHeight(){
		return $this->eyeHeight;
	}

	/**
	 * @param Human $entityPlayer
	 */
	public function onCollideWithPlayer(Human $entityPlayer){

	}



	/**
	 * @param Level $targetLevel
	 *
	 * @return bool
	 */
	protected function switchLevel(Level $targetLevel){
		if($this->closed){
			return false;
		}

		if($this->isValid()){
			$this->server->getPluginManager()->callEvent($ev = new EntityLevelChangeEvent($this, $this->level, $targetLevel));
			if($ev->isCancelled()){
				return false;
			}

			$this->level->removeEntity($this);
			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
			}
			$this->despawnFromAll();
		}

		$this->setLevel($targetLevel);
		$this->level->addEntity($this);
		$this->chunk = null;

		return true;
	}

	/**
	 * @return Position
	 */
	public function getPosition(){
		return new Position($this->x, $this->y, $this->z, $this->level);
	}

	/**
	 * @return Location
	 */
	public function getLocation(){
		return new Location($this->x, $this->y, $this->z, $this->yaw, $this->pitch, $this->level);
	}

	/**
	 * @return bool
	 */
	public function isInsideOfPortal(){
		$blocks = $this->getBlocksAround();

		foreach($blocks as $block){
			if($block instanceof Portal){
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isInsideOfWater(){
		if($this->level == null) return false;
		$block = $this->level->getBlockAt(Math::floorFloat($this->x), Math::floorFloat($y = ($this->y + $this->getEyeHeight())), Math::floorFloat($this->z));

		if($block instanceof Water){
			$f = ($block->y + 1) - ($block->getFluidHeightPercent() - 0.1111111);
			return $y < $f;
		}

		return false;
	}

	public function isUnderwater() : bool{
		$block = $this->level->getBlockAt((int) floor($this->x), (int) floor($y = ($this->y + $this->getEyeHeight())), (int) floor($this->z));

		if($block instanceof Water){
			$f = ($block->y + 1) - ($block->getFluidHeightPercent() - 0.1111111);
			return $y < $f;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function isInsideOfSolid(){
		$block = $this->level->getBlockAt((int) floor($this->x), (int) floor($y = ($this->y + $this->getEyeHeight())), (int) floor($this->z));

		return $block->isSolid() and !$block->isTransparent() and $block->collidesWithBB($this->getBoundingBox());
	}

	/**
	 * @return bool
	 */
	public function isInsideOfFire(){
		foreach($this->getBlocksAround() as $block){
			if($block instanceof Fire){
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $dx
	 * @param $dy
	 * @param $dz
	 *
	 * @return bool
	 */
	public function fastMove($dx, $dy, $dz){
		$this->blocksAround = null;

		if($dx == 0 and $dz == 0 and $dy == 0){
			return true;
		}

		Timings::$entityMoveTimer->startTiming();

		$newBB = $this->boundingBox->getOffsetBoundingBox($dx, $dy, $dz);

		$list = $this->level->getCollisionCubes($this, $newBB, false);

		if(count($list) === 0){
			$this->boundingBox = $newBB;
		}

		$this->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
		$this->y = $this->boundingBox->minY - $this->ySize;
		$this->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;

		$this->checkChunks();

		if(!$this->onGround or $dy != 0){
			$bb = clone $this->boundingBox;
			$bb->minY -= 0.75;
			$this->onGround = false;

			if(count($this->level->getCollisionBlocks($bb)) > 0){
				$this->onGround = true;
			}
		}
		$this->isCollided = $this->onGround;
		$this->updateFallState($dy, $this->onGround);

		Timings::$entityMoveTimer->stopTiming();

		return true;
	}

	public function move($dx, $dy, $dz){
		$this->blocksAround = null;

		Timings::$entityMoveTimer->startTiming();

		$movX = $dx;
		$movY = $dy;
		$movZ = $dz;

		if($this->keepMovement){
			$this->boundingBox->offset($dx, $dy, $dz);
		}else{
			$this->ySize *= self::STEP_CLIP_MULTIPLIER;

			/*
			if($this->isColliding){ //With cobweb?
				$this->isColliding = false;
				$dx *= 0.25;
				$dy *= 0.05;
				$dz *= 0.25;
				$this->motionX = 0;
				$this->motionY = 0;
				$this->motionZ = 0;
			}
			*/

			$axisalignedbb = clone $this->boundingBox;

			/*$sneakFlag = $this->onGround and $this instanceof Player;

			if($sneakFlag){
				for($mov = 0.05; $dx != 0.0 and count($this->level->getCollisionCubes($this, $this->boundingBox->getOffsetBoundingBox($dx, -1, 0))) === 0; $movX = $dx){
					if($dx < $mov and $dx >= -$mov){
						$dx = 0;
					}elseif($dx > 0){
						$dx -= $mov;
					}else{
						$dx += $mov;
					}
				}

				for(; $dz != 0.0 and count($this->level->getCollisionCubes($this, $this->boundingBox->getOffsetBoundingBox(0, -1, $dz))) === 0; $movZ = $dz){
					if($dz < $mov and $dz >= -$mov){
						$dz = 0;
					}elseif($dz > 0){
						$dz -= $mov;
					}else{
						$dz += $mov;
					}
				}

				//TODO: big messy loop
			}*/

			assert(abs($dx) <= 20 and abs($dy) <= 20 and abs($dz) <= 20, "Movement distance is excessive: dx=$dx, dy=$dy, dz=$dz");

			//TODO: bad hack here will cause unexpected behaviour under heavy lag
			$list = $this->level->getCollisionCubes($this, $this->level->getTickRateTime() > 50 ? $this->boundingBox->offsetCopy($dx, $dy, $dz) : $this->boundingBox->addCoord($dx, $dy, $dz), false);

			foreach($list as $bb){
				$dy = $bb->calculateYOffset($this->boundingBox, $dy);
			}

			$this->boundingBox->offset(0, $dy, 0);

			$fallingFlag = ($this->onGround or ($dy != $movY and $movY < 0));

			foreach($list as $bb){
				$dx = $bb->calculateXOffset($this->boundingBox, $dx);
			}

			$this->boundingBox->offset($dx, 0, 0);

			foreach($list as $bb){
				$dz = $bb->calculateZOffset($this->boundingBox, $dz);
			}

			$this->boundingBox->offset(0, 0, $dz);

			if($this->stepHeight > 0 and $fallingFlag and ($movX != $dx or $movZ != $dz)){
				$cx = $dx;
				$cy = $dy;
				$cz = $dz;
				$dx = $movX;
				$dy = $this->stepHeight;
				$dz = $movZ;

				$axisalignedbb1 = clone $this->boundingBox;

				$this->boundingBox->setBB($axisalignedbb);

				$list = $this->level->getCollisionCubes($this, $this->boundingBox->addCoord($dx, $dy, $dz), false);

				foreach($list as $bb){
					$dy = $bb->calculateYOffset($this->boundingBox, $dy);
				}

				$this->boundingBox->offset(0, $dy, 0);

				foreach($list as $bb){
					$dx = $bb->calculateXOffset($this->boundingBox, $dx);
				}

				$this->boundingBox->offset($dx, 0, 0);

				foreach($list as $bb){
					$dz = $bb->calculateZOffset($this->boundingBox, $dz);
				}

				$this->boundingBox->offset(0, 0, $dz);

				$reverseDY = -$dy;
				foreach($list as $bb){
					$reverseDY = $bb->calculateYOffset($this->boundingBox, $reverseDY);
				}
				$dy += $reverseDY;
				$this->boundingBox->offset(0, $reverseDY, 0);

				if(($cx ** 2 + $cz ** 2) >= ($dx ** 2 + $dz ** 2)){
					$dx = $cx;
					$dy = $cy;
					$dz = $cz;
					$this->boundingBox->setBB($axisalignedbb1);
				}else{
					$this->ySize += $dy;
				}
			}
		}

		$this->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
		$this->y = $this->boundingBox->minY - $this->ySize;
		$this->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;

		$this->checkChunks();
		$this->checkBlockCollision();
		$this->checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz);
		$this->updateFallState($dy, $this->onGround);

		if($movX != $dx){
			$this->motionX = 0;
		}

		if($movY != $dy){
			$this->motionY = 0;
		}

		if($movZ != $dz){
			$this->motionZ = 0;
		}

		//TODO: vehicle collision events (first we need to spawn them!)

		Timings::$entityMoveTimer->stopTiming();
	}

	/**
	 * @param $movX
	 * @param $movY
	 * @param $movZ
	 * @param $dx
	 * @param $dy
	 * @param $dz
	 */
	protected function checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz){
		$this->isCollidedVertically = $movY != $dy;
		$this->isCollidedHorizontally = ($movX != $dx or $movZ != $dz);
		$this->isCollided = ($this->isCollidedHorizontally or $this->isCollidedVertically);
		$this->onGround = ($movY != $dy and $movY < 0);
	}

	/**
	 * @deprecated WARNING: Despite what its name implies, this function DOES NOT return all the blocks around the entity.
	 * Instead, it returns blocks which have reactions for an entity intersecting with them.
	 *
	 * @return Block[]
	 */
	public function getBlocksAround(){
		if($this->blocksAround === null){
			$inset = 0.001; //Offset against floating-point errors

			$minX = (int) floor($this->boundingBox->minX + $inset);
			$minY = (int) floor($this->boundingBox->minY + $inset);
			$minZ = (int) floor($this->boundingBox->minZ + $inset);
			$maxX = (int) floor($this->boundingBox->maxX - $inset);
			$maxY = (int) floor($this->boundingBox->maxY - $inset);
			$maxZ = (int) floor($this->boundingBox->maxZ - $inset);

			$this->blocksAround = [];

			for($z = $minZ; $z <= $maxZ; ++$z){
				for($x = $minX; $x <= $maxX; ++$x){
					for($y = $minY; $y <= $maxY; ++$y){
						$block = $this->level->getBlockAt($x, $y, $z);
						if($block->hasEntityCollision()){
							$this->blocksAround[] = $block;
						}
					}
				}
			}
		}

		return $this->blocksAround;
	}

	/**
	 * Returns whether this entity can be moved by currents in liquids.
	 *
	 * @return bool
	 */
	public function canBeMovedByCurrents() : bool{
		return true;
	}

	protected function checkBlockCollision(){
		$vector = $this->temporalVector->setComponents(0, 0, 0);

		foreach($this->getBlocksAround() as $block){
			$block->onEntityCollide($this);
			$block->addVelocityToEntity($this, $vector);
		}

		if($vector->lengthSquared() > 0){
			$vector = $vector->normalize();
			$d = 0.014;
			$this->motionX += $vector->x * $d;
			$this->motionY += $vector->y * $d;
			$this->motionZ += $vector->z * $d;
		}
	}

	public function setRotation(float $yaw, float $pitch) : void{
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		$this->scheduleUpdate();
	}

	public function setPositionAndRotation(Vector3 $pos, float $yaw, float $pitch) : bool{
		if($this->setPosition($pos)){
			$this->setRotation($yaw, $pitch);

			return true;
		}

		return false;
	}

	protected function checkChunks(){
		$chunkX = $this->getFloorX() >> 4;
		$chunkZ = $this->getFloorZ() >> 4;
		if($this->chunk === null or ($this->chunk->getX() !== $chunkX or $this->chunk->getZ() !== $chunkZ)){
			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
			}
			$this->chunk = $this->level->getChunk($chunkX, $chunkZ, true);

			if(!$this->justCreated){
				$newChunk = $this->level->getViewersForPosition($this);
				foreach($this->hasSpawned as $player){
					if(!isset($newChunk[$player->getLoaderId()])){
						$this->despawnFrom($player);
					}else{
						unset($newChunk[$player->getLoaderId()]);
					}
				}
				foreach($newChunk as $player){
					$this->spawnTo($player);
				}
			}

			if($this->chunk === null){
				return;
			}

			$this->chunk->addEntity($this);
		}
	}

	/**
	 * @param Location $pos
	 *
	 * @return bool
	 */
	public function setLocation(Location $pos){
		if($this->closed){
			return false;
		}

		$this->setPositionAndRotation($pos, $pos->yaw, $pos->pitch);

		return true;
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return bool
	 */
	public function setPosition(Vector3 $pos){
		if($this->closed){
			return false;
		}

		if($pos instanceof Position and $pos->level !== null and $pos->level !== $this->level){
			if($this->switchLevel($pos->getLevel()) === false){
				return false;
			}
		}

		$this->x = $pos->x;
		$this->y = $pos->y;
		$this->z = $pos->z;

		$this->recalculateBoundingBox();

		$this->blocksAround = null;

		$this->checkChunks();

		return true;
	}

	protected function resetLastMovements() : void{
		list($this->lastX, $this->lastY, $this->lastZ) = [$this->x, $this->y, $this->z];
		list($this->lastYaw, $this->lastPitch) = [$this->yaw, $this->pitch];
		list($this->lastMotionX, $this->lastMotionY, $this->lastMotionZ) = [$this->motionX, $this->motionY, $this->motionZ];
	}

	/**
	 * @return Vector3
	 */
	public function getMotion(){
		return new Vector3($this->motionX, $this->motionY, $this->motionZ);
	}

	/**
	 * @return Vector3
	 */
	public function getLastMotion(){
		return new Vector3($this->lastMotionX, $this->lastMotionY, $this->lastMotionZ);
	}

	/**
	 * @param Vector3 $motion
	 *
	 * @return bool
	 */
	public function setMotion(Vector3 $motion){
		if(!$this->justCreated){
			$this->server->getPluginManager()->callEvent($ev = new EntityMotionEvent($this, $motion));
			if($ev->isCancelled()){
				return false;
			}
		}

		$this->motionX = $motion->x;
		$this->motionY = $motion->y;
		$this->motionZ = $motion->z;

		if(!$this->justCreated){
			$this->updateMovement();
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function isOnGround(){
		return $this->onGround === true;
	}

	public function kill(){
		$this->health = 0;
		$this->removeAllEffects();
		$this->scheduleUpdate();

		if($this->getLevel()->getServer()->expEnabled){
			$exp = mt_rand($this->getDropExpMin(), $this->getDropExpMax());
			if($exp > 0) $this->getLevel()->spawnXPOrb($this, $exp);
		}
	}

	/**
	 * @param Vector3|Position|Location $pos
	 */
	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null) : bool{
		if($pos instanceof Location){
			$yaw = $yaw ?? $pos->yaw;
			$pitch = $pitch ?? $pos->pitch;
		}
		$from = Position::fromObject($this, $this->level);
		$to = Position::fromObject($pos, $pos instanceof Position ? $pos->getLevel() : $this->level);
		$this->server->getPluginManager()->callEvent($ev = new EntityTeleportEvent($this, $from, $to));
		if($ev->isCancelled()){
			return false;
		}
		$this->ySize = 0;
		$pos = $ev->getTo();

		$this->setMotion($this->temporalVector->setComponents(0, 0, 0));
		if($this->setPositionAndRotation($pos, $yaw ?? $this->yaw, $pitch ?? $this->pitch)){
			$this->resetFallDistance();
			$this->setForceMovementUpdate();

			$this->updateMovement(true);

			return true;
		}

		return false;
	}

	/**
	 * @return int
	 */
	public function getId() : int{
		return $this->id;
	}

	public function respawnToAll(){
		foreach($this->hasSpawned as $key => $player){
			unset($this->hasSpawned[$key]);
			$this->spawnTo($player);
		}
	}

	public function spawnToAll(){
		if($this->chunk === null or $this->closed){
			return;
		}
		foreach($this->level->getViewersForPosition($this) as $player){
			if($player->isOnline()){
				$this->spawnTo($player);
			}
		}
	}
	
	/**
	 * @deprecated WARNING: This function DOES NOT permanently hide the entity from viewers. As soon as the entity or
	 * player moves, viewers will once again be able to see the entity.
	 */
	public function despawnFromAll(){
		foreach($this->hasSpawned as $player){
			$this->despawnFrom($player);
		}
	}

	/**
	 * Returns whether the entity has been "closed".
	 */
	public function isClosed() : bool{
		return $this->closed;
	}

	/**
	 * Closes the entity and frees attached references.
	 *
	 * WARNING: Entities are unusable after this has been executed!
	 */
	public function close(){
		if($this->closeInFlight){
			return;
		}

		if(!$this->closed){
			$this->closeInFlight = true;
			$this->server->getPluginManager()->callEvent(new EntityDespawnEvent($this));
			$this->closed = true;

			$this->removeEffect(Effect::HEALTH_BOOST); //TODO:Проверить данный вызов на нужность.

			$this->despawnFromAll();
			$this->hasSpawned = [];
			
			if($this->linkedType != 0){
				$this->linkedEntity->setLinked(0, $this);
			}

			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
				$this->chunk = null;
			}

			if($this->isValid()){
				$this->level->removeEntity($this);
				//$this->setLevel(null);
			}

			$this->namedtag = null;
			$this->lastDamageCause = null;
			$this->closeInFlight = false;
		}
	}

	/**
	 * @param int   $id
	 * @param int   $type
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function setDataProperty($id, $type, $value){
		if($this->getDataProperty($id) !== $value){
			$this->dataProperties[$id] = [$type, $value];

			$this->sendData($this->hasSpawned, [$id => $this->dataProperties[$id]]);

			return true;
		}

		return false;
	}

	/**
	 * @param Entity $entity
	 *
	 * @return bool
	 */
	public function linkEntity(Entity $entity){
		return $this->setLinked(1, $entity);
	}

	public function sendLinkedData(){
		if($this->linkedEntity instanceof Entity){
			$this->setLinked($this->linkedType, $this->linkedEntity);
		}
	}

	/**
	 * @param int    $type
	 * @param Entity $entity
	 *
	 * @return bool
	 */
	public function setLinked($type, Entity $entity){
		if($entity instanceof Boat or $entity instanceof Minecart){
			$this->setDataProperty(57, 8, [0, 1, 0]); //This is a fast hack for Boat. TODO: Improve it
		}

		if($type != 0 and $entity === null){
			return false;
		}
		if($entity === $this){
			return false;
		}
		switch($type){
			case 0:
				if($this->linkedType == 0){
					return true;
				}
				$this->linkedType = 0;
				$pk = new SetEntityLinkPacket();
				$pk->from = $entity->getId();
				$pk->to = $this->getId();
				$pk->type = 3;
				$this->server->broadcastPacket($this->level->getPlayers(), $pk);
				if($this instanceof Player){
					$pk = new SetEntityLinkPacket();
					$pk->from = $entity->getId();
					$pk->to = 0;
					$pk->type = 3;
					$this->dataPacket($pk);
				}
				if($this->linkedEntity->getLinkedType()){
					$this->linkedEntity->setLinked(0, $this);
				}
				$this->linkedEntity = null;

				return true;
			case 1:
				if(!$entity->isAlive()){
					return false;
				}
				$this->linkedEntity = $entity;
				$this->linkedType = 1;
				$entity->linkedEntity = $this;
				$entity->linkedType = 1;
				$pk = new SetEntityLinkPacket();
				$pk->from = $entity->getId();
				$pk->to = $this->getId();
				$pk->type = 2;
				$this->server->broadcastPacket($this->level->getPlayers(), $pk);
				if($this instanceof Player){
					$pk = new SetEntityLinkPacket();
					$pk->from = $entity->getId();
					$pk->to = 0;
					$pk->type = 2;
					$this->dataPacket($pk);
				}

				return true;
			case 2:
				if(!$entity->isAlive()){
					return false;
				}
				if($entity->getLinkedEntity() !== $this){
					return $entity->linkEntity($this);
				}
				$this->linkedEntity = $entity;
				$this->linkedType = 2;

				return true;
			default:
				return false;
		}
	}

	/**
	 * @return Entity
	 */
	public function getLinkedEntity(){
		return $this->linkedEntity;
	}

	/**
	 * @return null
	 */
	public function getLinkedType(){
		return $this->linkedType;
	}

	/**
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function getDataProperty($id){
		return isset($this->dataProperties[$id]) ? $this->dataProperties[$id][1] : null;
	}

	/**
	 * @param int $id
	 *
	 * @return int
	 */
	public function getDataPropertyType($id){
		return isset($this->dataProperties[$id]) ? $this->dataProperties[$id][0] : null;
	}

	/**
	 * @param      $propertyId
	 * @param      $id
	 * @param bool $value
	 * @param int  $type
	 */
	public function setDataFlag($propertyId, $id, $value = true, $type = self::DATA_TYPE_LONG){
		if($this->getDataFlag($propertyId, $id) !== $value){
			$flags = (int) $this->getDataProperty($propertyId);
			$flags ^= 1 << $id;
			$this->setDataProperty($propertyId, $type, $flags);
		}
	}

	/**
	 * @param int $propertyId
	 * @param int $id
	 *
	 * @return bool
	 */
	public function getDataFlag($propertyId, $id){
		return (((int) $this->getDataProperty($propertyId)) & (1 << $id)) > 0;
	}

	/**
	 * Wrapper around {@link Entity#getDataFlag} for generic data flag reading.
	 */
	public function getGenericFlag(int $flagId) : bool{
		return $this->getDataFlag($flagId >= 64 ? self::DATA_FLAGS2 : self::DATA_FLAGS, $flagId % 64);
	}

	/**
	 * Wrapper around {@link Entity#setDataFlag} for generic data flag setting.
	 */
	public function setGenericFlag(int $flagId, bool $value = true) : void{
		$this->setDataFlag($flagId >= 64 ? self::DATA_FLAGS2 : self::DATA_FLAGS, $flagId % 64, $value, self::DATA_TYPE_LONG);
	}

	public function __destruct(){
		$this->close();
	}

	public function setMetadata(string $metadataKey, MetadataValue $metadataValue){
		$this->server->getEntityMetadata()->setMetadata($this, $metadataKey, $metadataValue);
	}

	public function getMetadata(string $metadataKey){
		return $this->server->getEntityMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata(string $metadataKey) : bool{
		return $this->server->getEntityMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata(string $metadataKey, Plugin $plugin){
		$this->server->getEntityMetadata()->removeMetadata($this, $metadataKey, $plugin);
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return (new \ReflectionClass($this))->getShortName() . "(" . $this->getId() . ")";
	}
}