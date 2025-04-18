<?php

namespace EnderStructure;

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\{Player, Server};
use pocketmine\math\Vector3;
use pocketmine\tile\Tile;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\event\level\ChunkPopulateEvent;
use pocketmine\nbt\tag\{CompoundTag, IntTag, StringTag, ShortTag};
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentLevelTable;

class EnderStructure extends PluginBase implements Listener{

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onChunk(ChunkPopulateEvent $e){
	$level = $e->getLevel();
	$ship = mt_rand(1, 300); //шанс спавна корабля
			//СПАВН ХОРУСОВ
			if($level->getName() == "ender"){
			$x = ($e->getChunk()->getX() << 4) + mt_rand(-5, 5);
			$z = ($e->getChunk()->getZ() << 4) + mt_rand(-5, 5);
			$y = $level->getHighestBlockAt($x, $z) + 1;
			if($level->getBlock(new Vector3($x, $y - 1, $z))->getId() == 121){
			$level->setBlockIdAt($x, $y, $z, 240);
			//ствол из хорусов -----------------------
			for($i = 1; $i <= 7; $i++){
				$level->setBlockIdAt($x, $y + $i, $z, 240);
			}
			$level->setBlockIdAt($x, $y + 8, $z, 200);
			//ветки хорусов -----------------------------

			$level->setBlockIdAt($x + 1, $y + 5, $z, 240);
			$level->setBlockIdAt($x + 2, $y + 5, $z, 240);
			$level->setBlockIdAt($x + 2, $y + 6, $z, 200);

			$level->setBlockIdAt($x - 1, $y + 6, $z, 240);
			$level->setBlockIdAt($x - 2, $y + 6, $z, 240);
			$level->setBlockIdAt($x - 2, $y + 7, $z, 200);

			$level->setBlockIdAt($x, $y + 3, $z + 1, 240);
			$level->setBlockIdAt($x, $y + 3, $z + 2, 240);
			$level->setBlockIdAt($x, $y + 4, $z + 2, 200);

			$level->setBlockIdAt($x, $y + 4, $z - 1, 240);
			$level->setBlockIdAt($x, $y + 4, $z - 2, 240);
			$level->setBlockIdAt($x, $y + 5, $z - 2, 200);
				}		
			}
		if($ship == 300){
			//СПАВН КОРАБЛЯ
			if($level->getName() == "ender"){
			$pillar_left = Block::get(201, 6);
			$x = ($e->getChunk()->getX() << 4) + mt_rand(-5, 5);
			$z = ($e->getChunk()->getZ() << 4) + mt_rand(-5, 5);
			$rand = mt_rand(40, 90);
			$y = $level->getHighestBlockAt($x, $z) + $rand;
			if($level->getBlock(new Vector3($x, $y - $rand, $z))->getId() !== 121) return;
			//нижняя платформа -----------------------------------------------
			$level->setBlock(new Vector3($x, $y, $z), Block::get(201, 6)); //самый первый блок
			for($i = 1; $i <= 12; $i++){
				$level->setBlock(new Vector3($x + $i, $y, $z), Block::get(201, 6));
			}
			for($i = 1; $i <= 11; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 1, $z), Block::get(201, 0));
			}
			for($i = 1; $i <= 13; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 1, $z - 1), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 1, $z + 1), Block::get(201, 0));

				$level->setBlock(new Vector3($x + $i, $y + 2, $z + 1), Block::get(49, 0)); // обса с двух сторон
				$level->setBlock(new Vector3($x + $i, $y + 2, $z - 1), Block::get(49, 0));
			}
			for($i = -1; $i <= 14; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 2, $z + 2), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 2, $z - 2), Block::get(201, 0)); //стенки возле обсы
			}
			for($i = 1; $i <= 11; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 2, $z), Block::get(201, 6));
			}
			//столб вверх -------------------------------------------------------
			$level->setBlock(new Vector3($x + 3, $y + 2, $z), Block::get(201, 2));
			for($i = 2; $i<=20; $i++){
				$level->setBlock(new Vector3($x + 3, $y + $i, $z), Block::get(201, 2));
			}
			$level->setBlock(new Vector3($x + 3, $y + 21, $z), Block::get(201, 0)); // самый высокий блок на столбе
			//платформа на самом верху -----------------------------------------------
			for($i = 2; $i<=4; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 18, $z - 1), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 18, $z + 1), Block::get(201, 0));
			}
			//жопа корабля -------------------------------------------------------------------
			for($i = 0; $i <= 3; $i++){
				$level->setBlock(new Vector3($x + 14, $y + 4, $z - $i), Block::get(201, 0));
				$level->setBlock(new Vector3($x + 14, $y + 4, $z + $i), Block::get(201, 0));
			}
			for($i = 0; $i <= 3; $i++){
				$level->setBlock(new Vector3($x + 14, $y + 3, $z - $i), Block::get(201, 0));
				$level->setBlock(new Vector3($x + 14, $y + 3, $z + $i), Block::get(201, 0));
			}
			for($i = 0; $i <= 1; $i++){
				$level->setBlock(new Vector3($x + 14, $y + 2, $z - $i), Block::get(201, 0));
				$level->setBlock(new Vector3($x + 14, $y + 2, $z + $i), Block::get(201, 0));
			}
			//всякие хрени по бокам внутри ------------------------------------------- 117
			for($i = -1; $i <= 13; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 3, $z - 3), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 3, $z + 3), Block::get(201, 0));
			}
			//ступеньки на платформе --------------------------------------------------
			for($i = 1; $i <= 5; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 19, $z + 2), Block::get(203, 7));
				$level->setBlock(new Vector3($x + $i, $y + 19, $z - 2), Block::get(203, 6));
			}
			for($i = -1; $i <= 2; $i++){
				$level->setBlock(new Vector3($x + 5, $y + 19, $z - $i), Block::get(203, 5));
				$level->setBlock(new Vector3($x + 1, $y + 19, $z - $i), Block::get(203, 12));
			}
			//по горизонтали и вертикали вверх сзади ---------------------------------
			$level->setBlock(new Vector3($x + 12, $y + 1, $z), Block::get(201, 6));
			$level->setBlock(new Vector3($x + 13, $y + 1, $z), Block::get(201, 6));
			for($i = 12; $i <= 14; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 2, $z), Block::get(201, 6));
			}
			//по горизонтали и вертикали вверх спереди ---------------------------------------
			for($i = 0; $i <= 3; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 1, $z), Block::get(201, 6));
			}
			for($i = 0; $i <= 5; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 2, $z), Block::get(201, 6));
			}

			$level->setBlock(new Vector3($x - 5, $y + 3, $z), Block::get(201, 6));
			$level->setBlock(new Vector3($x - 6, $y + 3, $z), Block::get(201, 6));

			$level->setBlock(new Vector3($x - 6, $y + 5, $z), Block::get(201, 6));
			$level->setBlock(new Vector3($x - 7, $y + 5, $z), Block::get(201, 6));
			$level->setBlock(new Vector3($x - 7, $y + 6, $z), Block::get(201, 6));
			$level->setBlock(new Vector3($x - 8, $y + 6, $z), Block::get(201, 6));

			for($i = 7; $i <= 10; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 7, $z), Block::get(201, 6));
			}

			//голова дракона и ступенька возле нее ---------------------------------------------
			$level->setBlock(new Vector3($x - 10, $y + 8, $z), Block::get(203, 1)); // ступенька
			$level->setBlock(new Vector3($x - 11, $y + 8, $z), Block::get(144, 5)); // голова дракона
			//хуйня над головой дракона и ступенькой --------------------------------------------
			$level->setBlock(new Vector3($x - 10, $y + 12, $z), Block::get(201, 0));
			for($i = 1; $i <= 2; $i++){
				$level->setBlock(new Vector3($x - 7 - $i, $y + 12, $z), Block::get(201, 6));
				$level->setBlock(new Vector3($x - 5 - $i, $y + 11, $z), Block::get(201, 6));
				$level->setBlock(new Vector3($x - 3 - $i, $y + 10, $z), Block::get(201, 6));
				$level->setBlock(new Vector3($x - 1 - $i, $y + 9, $z), Block::get(201, 6));
				$level->setBlock(new Vector3($x + 1 - $i, $y + 8, $z), Block::get(201, 6));
			}
			//ступеньки над этой хуйней которая выше --------------------------------------------
			$level->setBlock(new Vector3($x + 1, $y + 8, $z), Block::get(203, 1));
			$level->setBlock(new Vector3($x - 1, $y + 9, $z), Block::get(203, 1));
			$level->setBlock(new Vector3($x - 3, $y + 10, $z), Block::get(203, 1));
			$level->setBlock(new Vector3($x - 5, $y + 11, $z), Block::get(203, 1));
			$level->setBlock(new Vector3($x - 7, $y + 12, $z), Block::get(203, 1));
			//пол второго этажа -------------------------------------------------------------------
			$level->setBlock(new Vector3($x + 2, $y + 7, $z), Block::get(20, 0));
			$level->setBlock(new Vector3($x + 1, $y + 7, $z), Block::get(201, 6));
			for($i = 0; $i <= 6; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 7, $z), Block::get(201, 0));
			}
			for($i = 4; $i <= 9; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 7, $z), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 7, $z - 1), Block::get(201, 0));
			}
			//cтупеньки спуск со второго этажа на средний ---------------------------------------------
			for($i = 1; $i <= 2; $i++){
				$level->setBlock(new Vector3($x + 8, $y + 7, $z + $i), Block::get(203, 1));
				$level->setBlock(new Vector3($x + 9, $y + 6, $z + $i), Block::get(203, 1));
			}
			//пол среднего этажа ----------------------------------------------------------------------
			for($i = 9; $i <= 15; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 5, $z + 4), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 5, $z + 3), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 5, $z + 2), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 5, $z + 1), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 5, $z + 0), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 5, $z - 1), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 5, $z - 2), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 5, $z - 3), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 5, $z - 4), Block::get(201, 0));
			}
			//хрень слева
			for($i = 6; $i <= 8; $i++){
				$level->setBlock(new Vector3($x + 9, $y + $i, $z + 3), Block::get(201, 0));
				$level->setBlock(new Vector3($x + 9, $y + $i, $z + 4), Block::get(201, 0));
			}
			//хрени справа
			for($i = 6; $i <= 7; $i++){
				$level->setBlock(new Vector3($x + 9, $y + $i, $z - 0), Block::get(201, 0));
				$level->setBlock(new Vector3($x + 9, $y + $i, $z - 1), Block::get(201, 0));
			}
			for($i = 6; $i <= 8; $i++){
				$level->setBlock(new Vector3($x + 10, $y + $i, $z - 0), Block::get(201, 0));
				$level->setBlock(new Vector3($x + 10, $y + $i, $z - 1), Block::get(201, 0));
			}
			//слева говно пол
			for($i = -7; $i <= 6; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 7, $z + 1), Block::get(201, 0));
			}
			for($i = -7; $i <= 5; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 7, $z + 2), Block::get(201, 0));
			}
			for($i = -9; $i <= 3; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 7, $z + 3), Block::get(201, 0));
			}
			for($i = -9; $i <= 1; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 7, $z + 4), Block::get(201, 0));
			}
			//справа говно пол
			for($i = -7; $i <= 6; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 7, $z - 1), Block::get(201, 0));
			}
			for($i = -7; $i <= 5; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 7, $z - 2), Block::get(201, 0));
			}
			for($i = -7; $i <= 3; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 7, $z - 3), Block::get(201, 0));
			}
			for($i = -9; $i <= 1; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 7, $z - 4), Block::get(201, 0));
			}
			//колоны на среднем этаже -------------------------------------------------------------
			for($i = 6; $i <= 8; $i++){
				$level->setBlock(new Vector3($x + 10, $y + $i, $z - 4), Block::get(201, 2));
				$level->setBlock(new Vector3($x + 10, $y + $i, $z + 4), Block::get(201, 2));
				$level->setBlock(new Vector3($x + 15, $y + $i, $z + 4), Block::get(201, 2));
				$level->setBlock(new Vector3($x + 15, $y + $i, $z - 4), Block::get(201, 2));
				$level->setBlock(new Vector3($x + 15, $y + $i, $z), Block::get(201, 2));
			}
			//стенки из эндерняка -------------------------------------------------------------
			for($i = 6; $i <= 8; $i++){
				for($g = 1; $g <= 3; $g++){
					$level->setBlock(new Vector3($x + 15, $y + $i, $z - $g), Block::get(206, 0));
					$level->setBlock(new Vector3($x + 15, $y + $i, $z + $g), Block::get(206, 0));
				}
				for($r = 11; $r <= 14; $r++){
					$level->setBlock(new Vector3($x + $r, $y + $i, $z - 4), Block::get(206, 0));
					$level->setBlock(new Vector3($x + $r, $y + $i, $z + 4), Block::get(206, 0));
				}
			}
			//стекло в стенах эндерняка -------------------------------------------------
			$level->setBlock(new Vector3($x + 15, $y + 7, $z + 2), Block::get(20, 0));
			$level->setBlock(new Vector3($x + 15, $y + 7, $z - 2), Block::get(20, 0));
			for($i = 12; $i <= 13; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 7, $z + 4), Block::get(20, 0));
				$level->setBlock(new Vector3($x + $i, $y + 7, $z - 4), Block::get(20, 0));
			}
			//блок и зельеварка над ним ---------------------------------------------------
			$level->setBlock(new Vector3($x + 14, $y + 6, $z), Block::get(201, 2));
			$level->setBlock(new Vector3($x + 14, $y + 7, $z), Block::get(117, 0));
			//крыша среднего этажа --------------------------------------------------------
			for($i = 9; $i <= 16; $i++){
				for($r = 0; $r <= 5; $r++){
					$level->setBlock(new Vector3($x + $i, $y + 9, $z + $r), Block::get(201, 0));
					$level->setBlock(new Vector3($x + $i, $y + 9, $z - $r), Block::get(201, 0));
				}
			}
			//убираем блоки над ступеньками к среднему этажу ------------------------------
			$level->setBlock(new Vector3($x + 9, $y + 9, $z + 1), Block::get(0, 0));
			$level->setBlock(new Vector3($x + 9, $y + 9, $z + 2), Block::get(0, 0));
			//ступеньки подьем со второго этажа на крышу --------------------------------------------
			for($i = 2; $i <= 3; $i++){
				$level->setBlock(new Vector3($x + 9, $y + 9, $z - $i), Block::get(203, 0));
				$level->setBlock(new Vector3($x + 8, $y + 8, $z - $i), Block::get(203, 0));
			}
			$level->setBlock(new Vector3($x + 9, $y + 8, $z - 2), Block::get(201, 0));
			$level->setBlock(new Vector3($x + 9, $y + 8, $z - 3), Block::get(201, 0));
			//ступеньки под подьемом на крышу
			$level->setBlock(new Vector3($x + 10, $y + 8, $z - 2), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 10, $y + 8, $z - 3), Block::get(203, 5));

			//стены слева ---------------------------------------------------------------------------
			for($i = 4; $i <= 7; $i++){
				for($r = 0; $r <= 9; $r++){
					$level->setBlock(new Vector3($x + $r, $y + $i, $z - 4), Block::get(201, 0));
				}
				$level->setBlock(new Vector3($x - 1, $y + $i, $z + 3), Block::get(201, 0));
				$level->setBlock(new Vector3($x - 2, $y + $i, $z + 3), Block::get(201, 0));
				$level->setBlock(new Vector3($x - 3, $y + $i, $z + 2), Block::get(201, 0));
				$level->setBlock(new Vector3($x - 4, $y + $i, $z + 2), Block::get(201, 0));
				$level->setBlock(new Vector3($x - 5, $y + $i, $z + 1), Block::get(201, 0));
			}
			//стены справа
			for($i = 4; $i <= 7; $i++){
				for($r = 0; $r <= 9; $r++){
					$level->setBlock(new Vector3($x + $r, $y + $i, $z + 4), Block::get(201, 0));
				}
				$level->setBlock(new Vector3($x - 1, $y + $i, $z - 3), Block::get(201, 0));
				$level->setBlock(new Vector3($x - 2, $y + $i, $z - 3), Block::get(201, 0));
				$level->setBlock(new Vector3($x - 3, $y + $i, $z - 2), Block::get(201, 0));
				$level->setBlock(new Vector3($x - 4, $y + $i, $z - 2), Block::get(201, 0));
				$level->setBlock(new Vector3($x - 5, $y + $i, $z - 1), Block::get(201, 0));
			}
			for($i = 9; $i <= 14; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 4, $z - 4), Block::get(201, 0));
				$level->setBlock(new Vector3($x + $i, $y + 4, $z + 4), Block::get(201, 0));
			}
			$level->setBlock(new Vector3($x - 6, $y + 4, $z), Block::get(201, 0));
			$level->setBlock(new Vector3($x - 6, $y + 6, $z), Block::get(201, 0));
			//хрени по бокам + ступенька
			$level->setBlock(new Vector3($x + 4, $y + 3, $z - 2), Block::get(203, 0));
			for($i = 5; $i <= 8; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 3, $z - 2), Block::get(201, 0));
			}
			for($i = 8; $i <= 13; $i++){
				for($t = 3; $t <= 4; $t++){
					$level->setBlock(new Vector3($x + $i, $y + $t, $z - 2), Block::get(201, 0));
				}
			}
			//хуйня в самом низу
			for($i = 0; $i <= 4; $i++){
				$level->setBlock(new Vector3($x - $i, $y + 2, $z + 1), Block::get(201, 0));
				$level->setBlock(new Vector3($x - $i, $y + 2, $z - 1), Block::get(201, 0));
			}
			//одинарные блоки снизу
			$level->setBlock(new Vector3($x - 2, $y + 3, $z + 2), Block::get(201, 0));
			$level->setBlock(new Vector3($x - 2, $y + 3, $z - 2), Block::get(201, 0));
			for($i = -1; $i <= 1; $i++){
				$level->setBlock(new Vector3($x - 4, $y + 3, $z + $i), Block::get(201, 0));
			}
			$level->setBlock(new Vector3($x - 3, $y + 3, $z + 1), Block::get(201, 0));
			$level->setBlock(new Vector3($x - 3, $y + 3, $z - 1), Block::get(201, 0));
			for($i = 4; $i <= 6; $i++){
				$level->setBlock(new Vector3($x - 5, $y + $i, $z), Block::get(201, 0));
			}
			//ступеньки под ступеньками выше
			$level->setBlock(new Vector3($x + 9, $y + 5, $z - 3), Block::get(203, 0));
			$level->setBlock(new Vector3($x + 9, $y + 5, $z - 2), Block::get(203, 0));
			$level->setBlock(new Vector3($x + 8, $y + 4, $z - 2), Block::get(203, 0));
			$level->setBlock(new Vector3($x + 8, $y + 4, $z - 3), Block::get(203, 0));
			//ДЕКОР НАХУЙ -----------------------------------------------------------
			//ступеньки по бокам
			$level->setBlock(new Vector3($x + 10, $y + 1, $z + 2), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 10, $y + 2, $z + 3), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 10, $y + 3, $z + 4), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 10, $y + 1, $z - 2), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 10, $y + 2, $z - 3), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 10, $y + 3, $z - 4), Block::get(203, 6));

			$level->setBlock(new Vector3($x + 6, $y + 1, $z + 2), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 6, $y + 2, $z + 3), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 6, $y + 3, $z + 4), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 6, $y + 1, $z - 2), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 6, $y + 2, $z - 3), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 6, $y + 3, $z - 4), Block::get(203, 6));

			$level->setBlock(new Vector3($x + 2, $y + 1, $z + 2), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 2, $y + 2, $z + 3), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 2, $y + 3, $z + 4), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 2, $y + 1, $z - 2), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 2, $y + 2, $z - 3), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 2, $y + 3, $z - 4), Block::get(203, 6));

			$level->setBlock(new Vector3($x + 14, $y + 2, $z + 3), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 14, $y + 3, $z + 4), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 14, $y + 2, $z - 3), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 14, $y + 3, $z - 4), Block::get(203, 6));
			//окна в корабле
			$level->setBlock(new Vector3($x + 2, $y + 6, $z - 4), Block::get(0, 0));
			$level->setBlock(new Vector3($x + 2, $y + 6, $z + 4), Block::get(0, 0));
			$level->setBlock(new Vector3($x + 5, $y + 6, $z - 4), Block::get(0, 0));
			$level->setBlock(new Vector3($x + 5, $y + 6, $z + 4), Block::get(0, 0));
			//ступеньки под окнами
			$level->setBlock(new Vector3($x + 5, $y + 5, $z + 5), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 5, $y + 5, $z - 5), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 2, $y + 5, $z + 5), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 2, $y + 5, $z - 5), Block::get(203, 6));
			//ступеньки под средней комнатой снаружи
			$level->setBlock(new Vector3($x + 9, $y + 5, $z + 5), Block::get(203, 4));
			$level->setBlock(new Vector3($x + 10, $y + 5, $z + 5), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 11, $y + 5, $z + 5), Block::get(203, 3));
			$level->setBlock(new Vector3($x + 12, $y + 5, $z + 5), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 13, $y + 5, $z + 5), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 14, $y + 5, $z + 5), Block::get(203, 3));
			$level->setBlock(new Vector3($x + 15, $y + 5, $z + 5), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 16, $y + 5, $z + 5), Block::get(203, 7));

			$level->setBlock(new Vector3($x + 9, $y + 5, $z - 5), Block::get(203, 4));
			$level->setBlock(new Vector3($x + 10, $y + 5, $z - 5), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 11, $y + 5, $z - 5), Block::get(203, 2));
			$level->setBlock(new Vector3($x + 12, $y + 5, $z - 5), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 13, $y + 5, $z - 5), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 14, $y + 5, $z - 5), Block::get(203, 2));
			$level->setBlock(new Vector3($x + 15, $y + 5, $z - 5), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 16, $y + 5, $z - 5), Block::get(203, 6));

			$level->setBlock(new Vector3($x + 16, $y + 5, $z - 4), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 16, $y + 5, $z - 3), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 16, $y + 5, $z - 2), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 16, $y + 5, $z - 1), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 16, $y + 5, $z - 0), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 16, $y + 5, $z + 1), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 16, $y + 5, $z + 2), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 16, $y + 5, $z + 3), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 16, $y + 5, $z + 4), Block::get(203, 5));

			$level->setBlock(new Vector3($x + 15, $y + 4, $z + 4), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 15, $y + 4, $z - 4), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 15, $y + 4, $z + 0), Block::get(203, 5));

			$level->setBlock(new Vector3($x + 17, $y + 9, $z + 0), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 17, $y + 9, $z + 1), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 17, $y + 9, $z + 2), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 17, $y + 9, $z + 3), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 17, $y + 9, $z + 4), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 17, $y + 9, $z + 5), Block::get(203, 5));

			$level->setBlock(new Vector3($x + 17, $y + 9, $z - 0), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 17, $y + 9, $z - 1), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 17, $y + 9, $z - 2), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 17, $y + 9, $z - 3), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 17, $y + 9, $z - 4), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 17, $y + 9, $z - 5), Block::get(203, 5));

			$level->setBlock(new Vector3($x + 17, $y + 9, $z + 6), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 16, $y + 9, $z + 6), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 16, $y + 9, $z + 6), Block::get(203, 3));
			$level->setBlock(new Vector3($x + 15, $y + 9, $z + 6), Block::get(203, 3));
			$level->setBlock(new Vector3($x + 14, $y + 9, $z + 6), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 13, $y + 9, $z + 6), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 12, $y + 9, $z + 6), Block::get(203, 3));
			$level->setBlock(new Vector3($x + 11, $y + 9, $z + 6), Block::get(203, 3));
			$level->setBlock(new Vector3($x + 10, $y + 9, $z + 6), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 9, $y + 9, $z + 6), Block::get(203, 7));
			$level->setBlock(new Vector3($x + 8, $y + 9, $z + 6), Block::get(203, 7));

			$level->setBlock(new Vector3($x + 17, $y + 9, $z - 6), Block::get(203, 5));
			$level->setBlock(new Vector3($x + 16, $y + 9, $z - 6), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 16, $y + 9, $z - 6), Block::get(203, 2));
			$level->setBlock(new Vector3($x + 15, $y + 9, $z - 6), Block::get(203, 2));
			$level->setBlock(new Vector3($x + 14, $y + 9, $z - 6), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 13, $y + 9, $z - 6), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 12, $y + 9, $z - 6), Block::get(203, 2));
			$level->setBlock(new Vector3($x + 11, $y + 9, $z - 6), Block::get(203, 2));
			$level->setBlock(new Vector3($x + 10, $y + 9, $z - 6), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 9, $y + 9, $z - 6), Block::get(203, 6));
			$level->setBlock(new Vector3($x + 8, $y + 9, $z - 6), Block::get(203, 6));

			$level->setBlock(new Vector3($x + 8, $y + 9, $z + 5), Block::get(203, 4));
			$level->setBlock(new Vector3($x + 8, $y + 9, $z + 4), Block::get(203, 4));
			$level->setBlock(new Vector3($x + 8, $y + 9, $z + 3), Block::get(203, 4));

			$level->setBlock(new Vector3($x + 8, $y + 9, $z - 5), Block::get(203, 4));
			$level->setBlock(new Vector3($x + 8, $y + 9, $z - 4), Block::get(203, 4));
			
			//энд палки
			$level->setBlock(new Vector3($x + 16, $y + 6, $z - 5), Block::get(208, 1));
			$level->setBlock(new Vector3($x + 16, $y + 6, $z + 5), Block::get(208, 1));
			//декор на крыше
			$level->setBlock(new Vector3($x + 9, $y + 10, $z - 5), Block::get(201, 0));
			$level->setBlock(new Vector3($x + 9, $y + 10, $z + 5), Block::get(201, 0));
			for($i = 10; $i <= 15; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 10, $z + 5), Block::get(203, 3));
			}
			$level->setBlock(new Vector3($x + 16, $y + 10, $z + 5), Block::get(201, 0));
			for($i = 10; $i <= 15; $i++){
				$level->setBlock(new Vector3($x + $i, $y + 10, $z - 5), Block::get(203, 2));
			}
			$level->setBlock(new Vector3($x + 16, $y + 10, $z - 5), Block::get(201, 0));

			$level->setBlock(new Vector3($x + 16, $y + 10, $z + 4), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 16, $y + 10, $z + 3), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 16, $y + 10, $z + 2), Block::get(201, 0));

			$level->setBlock(new Vector3($x + 16, $y + 10, $z - 4), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 16, $y + 10, $z - 3), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 16, $y + 10, $z - 2), Block::get(201, 0));

			$level->setBlock(new Vector3($x + 16, $y + 10, $z - 1), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 16, $y + 10, $z - 0), Block::get(203, 1));
			$level->setBlock(new Vector3($x + 16, $y + 10, $z + 1), Block::get(203, 1));

			$level->setBlock(new Vector3($x + 9, $y + 10, $z - 0), Block::get(201, 0));
			$level->setBlock(new Vector3($x + 9, $y + 10, $z - 1), Block::get(201, 0));
			$level->setBlock(new Vector3($x + 9, $y + 10, $z - 4), Block::get(201, 0));

			$level->setBlock(new Vector3($x + 9, $y + 10, $z + 4), Block::get(203, 0));
			$level->setBlock(new Vector3($x + 9, $y + 10, $z + 3), Block::get(201, 0));

			$level->setBlock(new Vector3($x + 10, $y + 10, $z + 3), Block::get(203, 2));
			$level->setBlock(new Vector3($x + 10, $y + 10, $z + 2), Block::get(203, 0));
			$level->setBlock(new Vector3($x + 10, $y + 10, $z + 1), Block::get(203, 0));
			$level->setBlock(new Vector3($x + 10, $y + 10, $z + 0), Block::get(203, 3));

			$level->setBlock(new Vector3($x + 11, $y + 10, $z + 2), Block::get(201, 2));

			$level->setBlock(new Vector3($x + 15, $y + 10, $z + 2), Block::get(203, 3));
			$level->setBlock(new Vector3($x + 15, $y + 10, $z + 1), Block::get(203, 0));
			$level->setBlock(new Vector3($x + 15, $y + 10, $z + 0), Block::get(203, 0));
			$level->setBlock(new Vector3($x + 15, $y + 10, $z - 1), Block::get(203, 0));
			$level->setBlock(new Vector3($x + 15, $y + 10, $z - 2), Block::get(203, 2));

			//пиздец блять нахуй почти конец (стенки на верхнем этаже)
			$level->setBlock(new Vector3($x + 9, $y + 8, $z - 4), Block::get(201, 0));

			$level->setBlock(new Vector3($x + 8, $y + 8, $z + 4), Block::get(201, 0));
			$level->setBlock(new Vector3($x + 7, $y + 8, $z + 4), Block::get(203, 3));
			$level->setBlock(new Vector3($x + 6, $y + 8, $z + 4), Block::get(203, 3));
			$level->setBlock(new Vector3($x + 5, $y + 8, $z + 4), Block::get(201, 0));
			$level->setBlock(new Vector3($x + 4, $y + 8, $z + 4), Block::get(203, 3));
			$level->setBlock(new Vector3($x + 3, $y + 8, $z + 4), Block::get(203, 3));
			$level->setBlock(new Vector3($x + 2, $y + 8, $z + 4), Block::get(201, 0));
			$level->setBlock(new Vector3($x + 1, $y + 8, $z + 4), Block::get(203, 3));
			$level->setBlock(new Vector3($x + 0, $y + 8, $z + 4), Block::get(203, 3));
			$level->setBlock(new Vector3($x - 1, $y + 8, $z + 4), Block::get(201, 0));
			$level->setBlock(new Vector3($x - 1, $y + 9, $z + 4), Block::get(208, 1));
			$level->setBlock(new Vector3($x - 2, $y + 8, $z + 3), Block::get(203, 0));
			$level->setBlock(new Vector3($x - 3, $y + 8, $z + 3), Block::get(203, 3));
			$level->setBlock(new Vector3($x - 4, $y + 8, $z + 3), Block::get(201, 0));
			$level->setBlock(new Vector3($x - 4, $y + 8, $z + 2), Block::get(203, 0));
			$level->setBlock(new Vector3($x - 5, $y + 8, $z + 2), Block::get(203, 3));
			$level->setBlock(new Vector3($x - 6, $y + 8, $z + 2), Block::get(201, 0));
			$level->setBlock(new Vector3($x - 7, $y + 8, $z + 1), Block::get(201, 0));

			$level->setBlock(new Vector3($x + 8, $y + 8, $z - 4), Block::get(201, 0));
			$level->setBlock(new Vector3($x + 7, $y + 8, $z - 4), Block::get(203, 2));
			$level->setBlock(new Vector3($x + 6, $y + 8, $z - 4), Block::get(203, 2));
			$level->setBlock(new Vector3($x + 5, $y + 8, $z - 4), Block::get(201, 0));
			$level->setBlock(new Vector3($x + 4, $y + 8, $z - 4), Block::get(203, 2));
			$level->setBlock(new Vector3($x + 3, $y + 8, $z - 4), Block::get(203, 2));
			$level->setBlock(new Vector3($x + 2, $y + 8, $z - 4), Block::get(201, 0));
			$level->setBlock(new Vector3($x + 1, $y + 8, $z - 4), Block::get(203, 2));
			$level->setBlock(new Vector3($x + 0, $y + 8, $z - 4), Block::get(203, 2));
			$level->setBlock(new Vector3($x - 1, $y + 8, $z - 4), Block::get(201, 0));
			$level->setBlock(new Vector3($x - 1, $y + 9, $z - 4), Block::get(208, 1));
			$level->setBlock(new Vector3($x - 2, $y + 8, $z - 3), Block::get(203, 0));
			$level->setBlock(new Vector3($x - 3, $y + 8, $z - 3), Block::get(203, 2));
			$level->setBlock(new Vector3($x - 4, $y + 8, $z - 3), Block::get(201, 0));
			$level->setBlock(new Vector3($x - 4, $y + 8, $z - 2), Block::get(203, 0));
			$level->setBlock(new Vector3($x - 5, $y + 8, $z - 2), Block::get(203, 2));
			$level->setBlock(new Vector3($x - 6, $y + 8, $z - 2), Block::get(201, 0));
			$level->setBlock(new Vector3($x - 7, $y + 8, $z - 1), Block::get(201, 0));

			$level->setBlock(new Vector3($x - 8, $y + 8, $z - 0), Block::get(201, 0));
			$level->setBlock(new Vector3($x - 8, $y + 9, $z - 0), Block::get(208, 1));
			$level->setBlock(new Vector3($x - 5, $y + 6, $z), Block::get(208, 1));
			$level->setBlock(new Vector3($x - 4, $y + 5, $z), Block::get(199, 0));

			$level->setBlock(new Vector3($x - 4, $y + 4, $z - 1), Block::get(54, 5));
			$level->setBlock(new Vector3($x - 4, $y + 4, $z + 1), Block::get(54, 5));
			$chest1 = Tile::createTile("Chest", $level, new CompoundTag("", [new StringTag("id", "Chest"), new IntTag("x", $x - 4), new IntTag("y", $y + 4), new IntTag("z", $z + 1)]));
			$chest2 = Tile::createTile("Chest", $level, new CompoundTag("", [new StringTag("id", "Chest"), new IntTag("x", $x - 4), new IntTag("y", $y + 4), new IntTag("z", $z - 1)]));
			$chest1->getInventory()->setItem(mt_rand(0, 11), Item::get(266, 0, mt_rand(0, 2)));
			$chest1->getInventory()->setItem(mt_rand(17, 27), Item::get(266, 0, mt_rand(0, 2)));
			$chest1->getInventory()->setItem(mt_rand(0, 12), Item::get(445, 0, mt_rand(0, 3)));
			$rand276 = mt_rand(1, 7);
			$rand256 = mt_rand(1, 10);
			if($rand256 == 10){
			$chest1->getInventory()->setItem(mt_rand(1, 27), Item::get(264, 0, mt_rand(1, 2)));
			$chest1->getInventory()->setItem(mt_rand(1, 27), Item::get(264, 0, mt_rand(1, 2)));
			$chest1->getInventory()->setItem(mt_rand(1, 27), Item::get(264, 0, mt_rand(1, 2)));
			$chest1->getInventory()->setItem(mt_rand(1, 27), Item::get(265, 0, mt_rand(1, 2)));
			}else{
				$chest1->getInventory()->setItem(mt_rand(1, 27), Item::get(265, 0, mt_rand(1, 2)));
				$chest1->getInventory()->setItem(mt_rand(1, 27), Item::get(264, 0, mt_rand(1, 2)));
			}
			if($rand276 == 7){
				$sword = Item::get(276, 0, 1);
				$sword->addEnchantment(Enchantment::getEnchantment(9)->setLevel(mt_rand(3, 5)));
				$sword->addEnchantment(Enchantment::getEnchantment(14)->setLevel(mt_rand(1, 3)));
				$chest1->getInventory()->setItem(mt_rand(0, 27), $sword);
				$rand277 = mt_rand(1, 2);
				if($rand277 == 2){
					$sword->addEnchantment(Enchantment::getEnchantment(9)->setLevel(mt_rand(4, 5)));
					$sword->addEnchantment(Enchantment::getEnchantment(14)->setLevel(mt_rand(2, 3)));
					$sword->addEnchantment(Enchantment::getEnchantment(13)->setLevel(mt_rand(1, 2)));
					$sword->addEnchantment(Enchantment::getEnchantment(13)->setLevel(mt_rand(1, 2)));
					$sword->addEnchantment(Enchantment::getEnchantment(26)->setLevel(1));
					$chest1->getInventory()->setItem(mt_rand(0, 27), $sword);
				}
			}elseif($rand276 == 6){
				$sword = Item::get(276, 0, 1);
				$sword->addEnchantment(Enchantment::getEnchantment(9)->setLevel(mt_rand(2, 4)));
				$sword->addEnchantment(Enchantment::getEnchantment(14)->setLevel(1));
				$sword->addEnchantment(Enchantment::getEnchantment(17)->setLevel(mt_rand(1, 3)));
				$chest1->getInventory()->setItem(mt_rand(0, 27), $sword);
			}
			$chest1->getInventory()->setItem(13, Item::get(444, 0, 1));
			$chest2->getInventory()->setItem(mt_rand(0, 8), Item::get(458, 0, mt_rand(0, 3)));
			$chest2->getInventory()->setItem(mt_rand(8, 17), Item::get(458, 0, mt_rand(0, 3)));
			$chest2->getInventory()->setItem(mt_rand(0, 27), Item::get(264, 0, mt_rand(0, 2)));
			$chest2->getInventory()->setItem(mt_rand(0, 27), Item::get(264, 0, mt_rand(0, 2)));
			$chest2->getInventory()->setItem(mt_rand(0, 27), Item::get(264, 0, mt_rand(0, 2)));
			$chest2->getInventory()->setItem(mt_rand(0, 27), Item::get(265, 0, mt_rand(0, 2)));
			$chest2->getInventory()->setItem(mt_rand(0, 27), Item::get(266, 0, mt_rand(0, 2)));
			$chest2->getInventory()->setItem(mt_rand(0, 27), Item::get(266, 0, mt_rand(0, 2)));
			$armor = mt_rand(310, 313);
			$armor2 = Item::get($armor, 0, 1);
			$armor2->addEnchantment(Enchantment::getEnchantment(0)->setLevel(mt_rand(3, 4)));
			$mendingrand = mt_rand(1, 3);
			if($mendingrand == 3){
				$armor2->addEnchantment(Enchantment::getEnchantment(26)->setLevel(1));
			}
			$chest2->getInventory()->setItem(mt_rand(0, 27), $armor2);
			$randpickaxe = mt_rand(277, 279);
			$picakxechance = mt_rand(1, 5);
			$pickaxe = Item::get($randpickaxe, 0, 1);
			if($picakxechance == 4){
				$pickaxe->addEnchantment(Enchantment::getEnchantment(mt_rand(15, 18))->setLevel(mt_rand(1, 3)));
				$chest2->getInventory()->setItem(mt_rand(0, 27), $pickaxe);
			}elseif($picakxechance == 5){
				$pickaxe->addEnchantment(Enchantment::getEnchantment(26)->setLevel(1));
				$pickaxe->addEnchantment(Enchantment::getEnchantment(15)->setLevel(mt_rand(3, 5)));
				$chest2->getInventory()->setItem(mt_rand(0, 27), $pickaxe);
			}
			}
		}
	}
}