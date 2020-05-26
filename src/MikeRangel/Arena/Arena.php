<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 2.0.0
 * Status: Publico
*/
namespace MikeRangel\Arena;
use MikeRangel\{Loader, Events\GlobalEvents, Entity\types\HumanEntity, Entity\types\TopsEntity};
use pocketmine\{Server, Player, event\Listener};
use pocketmine\event\{block\BlockBreakEvent, block\BlockPlaceEvent, entity\EntityDamageEvent, entity\EntityDamageByEntityEvent, entity\EntityLevelChangeEvent};

class Arena implements Listener {

    public function onBreak(BlockBreakEvent $event) {
        $config = Loader::getConfigs('config');
        $player = $event->getPlayer();
        if ($config->get('arena') != null) {
            if ($player->getLevel() === Server::getInstance()->getLevelByName($config->get('arena'))) {
                $event->setCancelled(true);
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event) {
        $config = Loader::getConfigs('config');
        $player = $event->getPlayer();
        if ($config->get('arena') != null) {
            if ($player->getLevel() === Server::getInstance()->getLevelByName($config->get('arena'))) {
                $event->setCancelled(true);
            }
        }
    }

    public function onChange(EntityLevelChangeEvent $event) {
        $config = Loader::getConfigs('config');
        $player = $event->getEntity();
        if ($config->get('arena') != null) {
            if ($player->getLevel() === Server::getInstance()->getLevelByName($config->get('arena'))) {
                $api = Loader::getScore();
                $api->remove($player);
                $player->removeAllEffects();
                $player->setGamemode(0);
                $player->setHealth(20);
                $player->setFood(20);
                $player->getInventory()->clearAll();
                $player->getArmorInventory()->clearAll();
            }
        }
    }

    public function onDamage(EntityDamageEvent $event) {
        $config = Loader::getConfigs('config');
        $player = $event->getEntity();
        if ($player instanceof Player) {
            if ($config->get('arena') != null) {
                if ($player->getLevel() === Server::getInstance()->getLevelByName($config->get('arena'))) {
                    if ($event->getCause() === EntityDamageEvent::CAUSE_FALL || $event->getCause() === EntityDamageEvent::CAUSE_VOID) {
                        $event->setCancelled(true);
                    }
                }
            }
        }
    }

    public function onDamageTops(EntityDamageByEntityEvent $event) {
		if ($event->getEntity() instanceof TopsEntity) {
			$player = $event->getDamager();
			if ($player instanceof Player) {
				$event->setCancelled(true);
			}
		}
    }

    public function onDamageHuman(EntityDamageByEntityEvent $event) {
		if ($event->getEntity() instanceof HumanEntity) {
			$player = $event->getDamager();
			if ($player instanceof Player) {
                $event->setCancelled(true);
                GlobalEvents::joinGame($player);
			}
		}
	}

    public function onKnockBack(EntityDamageByEntityEvent $event) {
        $config = Loader::getConfigs('config');
        $player = $event->getEntity();
        if ($event instanceof EntityDamageByEntityEvent) {
            if ($event->getEntity() instanceof Player && $event->getDamager() instanceof Player) {
                if ($config->get('arena') != null) {
                    if ($player->getLevel() === Server::getInstance()->getLevelByName($config->get('arena'))) {
                        $event->setKnockBack(0.25);
                        $event->setCancelled(false);
                    }
                }
            }
        }
    }
}
?>