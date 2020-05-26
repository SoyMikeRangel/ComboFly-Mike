<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 2.0.0
 * Status: Publico
*/
namespace MikeRangel\Tasks;
use MikeRangel\{Loader, Entity\types\HumanEntity, Entity\types\TopsEntity};
use pocketmine\{Server, Player, utils\TextFormat as Color, math\Vector2, entity\Effect, entity\EffectInstance, scheduler\Task};
use pocketmine\network\mcpe\protocol\{MovePlayerPacket};

class EntityUpdate extends Task {

	public function onRun(int $currentTick) {
		foreach (Server::getInstance()->getDefaultLevel()->getEntities() as $entity) {
			if ($entity instanceof HumanEntity) {
				$entity->addEffect(new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE), 999));
				$entity->setNameTag(self::setName());
				$entity->setNameTagAlwaysVisible(true);
				$entity->setScale(1);
				self::setRotation($entity);
			} else if ($entity instanceof TopsEntity) {
				$entity->setNameTag($this->setTops());
				$entity->setNameTagAlwaysVisible(true);
			}
		}
	}

	public static function getPlayers() : int {
		$config = Loader::getConfigs('config');
		if ($config->get('arena') != null) {
			return count(Server::getInstance()->getLevelByName($config->get('arena'))->getPlayers());
		} else {
			return 0;
		}
	}

	public static function setName() : string {
		$comment = Color::GRAY . '[' . Color::GREEN . 'PRACTICE' . Color::GRAY . ']' . "\n";
		$title = Color::YELLOW . Color::BOLD . 'ComboFly' . "\n";
		$subtitle = Color::GRAY . 'Players: ' . Color::AQUA . self::getPlayers() . "\n";
		$tap = Color::YELLOW . 'Tap to join';
		return $comment . $title . $subtitle . $tap;
	}

	public static function setTops() : string {
		$kills = Loader::getConfigs('kills');
		$tops = [];
		$title = Color::WHITE . Color::BOLD . '✘' . Color::RESET . Color::YELLOW . 'Leaderboard ComboFly' . Color::WHITE . Color::BOLD . '✘' . Color::RESET . "\n";
		foreach ($kills->getAll() as $key => $top) {
			array_push($tops, $top);
		}
		natsort($tops);
		$player = array_reverse($tops);
		if (max($tops) != null) {
			$top1 = array_search(max($tops), $kills->getAll());
			$subtitle1 = Color::GOLD . '#1 ' . Color::WHITE . $top1 . Color::GRAY . ' - ' . Color::AQUA . max($tops) . Color::GOLD . ' kills' . "\n";
		} else {
			$subtitle1 = '';
		}
		if ($player[1] != null) {
			$top2 = array_search($player[1], $kills->getAll());
			$subtitle2 = Color::YELLOW . '#2 ' . Color::WHITE . $top2 . Color::GRAY . ' - ' . Color::AQUA . $player[1] . Color::YELLOW . ' kills' . "\n";
		} else {
			$subtitle2 = '';
		}
		if ($player[2] != null) {
			$top3 = array_search($player[2], $kills->getAll());
			$subtitle3 = Color::YELLOW . '#3 ' . Color::WHITE . $top3 . Color::GRAY . ' - ' . Color::AQUA . $player[2] . Color::YELLOW . ' kills' . "\n";
		} else {
			$subtitle3 = '';
		}
		if ($player[3] != null) {
			$top4 = array_search($player[3], $kills->getAll());
			$subtitle4 = Color::YELLOW . '#4 ' . Color::WHITE . $top4 . Color::GRAY . ' - ' . Color::AQUA . $player[3] . Color::YELLOW . ' kills' . "\n";
		} else {
			$subtitle4 = '';
		}
		if ($player[4] != null) {
			$top5 = array_search($player[4], $kills->getAll());
			$subtitle5 = Color::YELLOW . '#5 ' . Color::WHITE . $top5 . Color::GRAY . ' - ' . Color::AQUA . $player[4] . Color::YELLOW . ' kills' . "\n";
		} else {
			$subtitle5 = '';
		}
		if ($player[5] != null) {
			$top6 = array_search($player[5], $kills->getAll());
			$subtitle6 = Color::YELLOW . '#6 ' . Color::WHITE . $top6 . Color::GRAY . ' - ' . Color::AQUA . $player[5] . Color::YELLOW . ' kills' . "\n";
		} else {
			$subtitle6 = '';
		}
		if ($player[6] != null) {
			$top7 = array_search($player[6], $kills->getAll());
			$subtitle7 = Color::YELLOW . '#7 ' . Color::WHITE . $top7 . Color::GRAY . ' - ' . Color::AQUA . $player[6] . Color::YELLOW . ' kills' . "\n";
		} else {
			$subtitle7 = '';
		}
		if ($player[7] != null) {
			$top8 = array_search($player[7], $kills->getAll());
			$subtitle8 = Color::YELLOW . '#8 ' . Color::WHITE . $top8 . Color::GRAY . ' - ' . Color::AQUA . $player[7] . Color::YELLOW . ' kills' . "\n";
		} else {
			$subtitle8 = '';
		}
		if ($player[8] != null) {
			$top9 = array_search($player[8], $kills->getAll());
			$subtitle9 = Color::YELLOW . '#9 ' . Color::WHITE . $top9 . Color::GRAY . ' - ' . Color::AQUA . $player[8] . Color::YELLOW . ' kills' . "\n";
		} else {
			$subtitle9 = '';
		}
		if ($player[9] != null) {
			$top10 = array_search($player[9], $kills->getAll());
			$subtitle10 = Color::YELLOW . '#10 ' . Color::WHITE . $top10 . Color::GRAY . ' - ' . Color::AQUA . $player[9] . Color::YELLOW . ' kills' . "\n";
		} else {
			$subtitle10 = '';
		}
        return $title . $subtitle1 . $subtitle2 . $subtitle3 . $subtitle4 . $subtitle5 . $subtitle6 . $subtitle7 . $subtitle8 . $subtitle9 . $subtitle10;
	}

	public static function setRotation($entity) {
		foreach ($entity->getLevel()->getNearbyEntities($entity->getBoundingBox()->expandedCopy(15, 15, 15), $entity) as $player) {
			if ($player instanceof Player) {
				$xdiff = $player->x - $entity->x;
				$zdiff = $player->z - $entity->z;
				$angle = atan2($zdiff, $xdiff);
				$yaw = (($angle * 180) / M_PI) - 90;
				$ydiff = $player->y - $entity->y;
				$v = new Vector2($entity->x, $entity->z);
				$dist = $v->distance($player->x, $player->z);
				$angle = atan2($dist, $ydiff);
				$pitch = (($angle * 180) / M_PI) - 90;
				$pk = new MovePlayerPacket();
				$pk->entityRuntimeId = $entity->getId();
				$pk->position = $entity->asVector3()->add(0, $entity->getEyeHeight(), 0);
				$pk->yaw = $yaw;
				$pk->pitch = $pitch;
				$pk->headYaw = $yaw;
				$pk->onGround = $entity->onGround;
				$player->dataPacket($pk);
			}
		}
	}
}