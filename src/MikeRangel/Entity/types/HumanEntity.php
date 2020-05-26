<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 2.0.0
 * Status: Publico
*/
namespace MikeRangel\Entity\types;
use pocketmine\entity\Human;

class HumanEntity extends Human {

	public function getName(): string {
		return '';
	}
}
?>