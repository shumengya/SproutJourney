<?php

namespace Riovsky;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;

class FixedBow extends PluginBase implements Listener
{
	public function onEnable(){ //включение
	    $this->getServer()->getPluginManager()->registerEvents($this, $this); //регистрация эвентов
	}

	public function onDamage(EntityDamageEvent $ev): void{ //функция дамага
		if($ev instanceof EntityDamageByChildEntityEvent){ //эвент попадание стрелы
			$flame = Item::get(261, 0, 1); //лук
			$flame->hasEnchantment(21); //пламя

			$punch = Item::get(261, 0, 1); //лук
			$punch->hasEnchantment(20); //отбрасывание

			$damager = $ev->getDamager(); //игрок 1
			$entity = $ev->getEntity(); //игрок 2

			if($damager->getInventory()->getItemInHand($flame)){ //проверка предмета в руке игрока 1
				$entity->setOnFire(6); //поджег игрока 2
			}

			if($damager->getInventory()->getItemInHand($punch)){ //проверка предмета в руке игрока 1
				$ev->setKnockback(10); //отбрасывание игрока 2
			}
		}
	}
}
