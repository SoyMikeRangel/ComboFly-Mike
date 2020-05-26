<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 2.0.0
 * Status: Publico
*/
namespace MikeRangel\Entity;
use MikeRangel\{Entity\types\HumanEntity, Entity\types\TopsEntity};
use pocketmine\{Server, Player, utils\TextFormat, level\Level, entity\Skin, entity\Entity, math\Vector3};

class EntityManager {
	
	public function setGame(Player $player) {
		$nbt = Entity::createBaseNBT(new Vector3((float)$player->getX(), (float)$player->getY(), (float)$player->getZ()));
		$nbt->setTag(clone $player->namedtag->getCompoundTag('Skin'));
		$human = new HumanEntity($player->getLevel(), $nbt);
		$human->setNameTag('');
		$human->setNameTagVisible(true);
		$human->setNameTagAlwaysVisible(true);
		$human->yaw = $player->getYaw();
		$human->pitch = $player->getPitch();
		$human->spawnToAll();
	}
	
	public function setTops(Player $player) {
		$nbt = Entity::createBaseNBT(new Vector3((float)$player->getX(), (float)$player->getY(), (float)$player->getZ()));
		$nbt->setTag($player->namedtag->getTag('Skin'));
		$human = new TopsEntity($player->getLevel(), $nbt);
		$human->setSkin(new Skin('textfloat', $human->getInvisibleSkin()));
		$human->setNameTagVisible(true);
		$human->setNameTagAlwaysVisible(true);
		$human->spawnToAll();
	}
}