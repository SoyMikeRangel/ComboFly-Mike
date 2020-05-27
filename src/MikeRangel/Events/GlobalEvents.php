<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 2.0.0
 * Status: Publico
*/
namespace MikeRangel\Events;
use MikeRangel\{Loader, Form\Form, Entity\types\HumanEntity, Entity\types\TopsEntity};
use pocketmine\{Server, Player, event\Listener, math\Vector3, event\server\DataPacketReceiveEvent, utils\TextFormat as Color, level\Position, item\Item};
use pocketmine\event\{player\PlayerDropItemEvent, player\PlayerItemHeldEvent, player\PlayerRespawnEvent, player\PlayerQuitEvent, player\PlayerDeathEvent, entity\EntityDamageByEntityEvent};
use pocketmine\item\enchantment\{Enchantment, EnchantmentInstance};
use pocketmine\level\sound\{EndermanTeleportSound, ClickSound};
use pocketmine\network\mcpe\protocol\{LevelSoundEventPacket, InventoryTransactionPacket};

class GlobalEvents implements Listener {

    public static function addCPS(Player $player) {
        if (!isset(Loader::$data['cps'][$player->getLowerCaseName()])) {
			Loader::$data['cps'][$player->getLowerCaseName()] = [time(), 0];
        } 
        $time = Loader::$data['cps'][$player->getLowerCaseName()][0];
		$cps = Loader::$data['cps'][$player->getLowerCaseName()][1];
        if ($time !== time()) {
			$time = time();
			$cps = 0;
		}
		$cps++;
		Loader::$data['cps'][$player->getLowerCaseName()] = [$time, $cps];
    }

    public static function getCPS(Player $player) {
        if (!isset(Loader::$data['cps'][$player->getLowerCaseName()])) {
			return 0;
        }
        $time = Loader::$data['cps'][$player->getLowerCaseName()][0];
		$cps = Loader::$data['cps'][$player->getLowerCaseName()][1];
        if ($time !== time()) {
			unset(Loader::$data['cps'][$player->getLowerCaseName()]);
			return 0;
		}
		return $cps;
    }

    public function onData(DataPacketReceiveEvent $event){
		$player = $event->getPlayer();
		$packet = $event->getPacket();
		if($packet instanceof InventoryTransactionPacket){
			$type = $packet->transactionType;
			if($type === InventoryTransactionPacket::TYPE_USE_ITEM || $type === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY){
				self::addCPS($player);
			}
		}
    }
    
    public function onQuit(PlayerQuitEvent $event) {
        $config = Loader::getConfigs('config');
        $player = $event->getPlayer();
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

    public function onDrop(PlayerDropItemEvent $event) {
        $config = Loader::getConfigs('config');
        $player = $event->getPlayer();
        if ($config->get('arena') != null) {
            if ($player->getLevel() === Server::getInstance()->getLevelByName($config->get('arena'))) {
                $event->setCancelled(true);
            }
        }
    }

    public function onHeld(PlayerItemHeldEvent $event) {
        $config = Loader::getConfigs('config');
    	$player = $event->getPlayer();
        $arena = $player->getLevel()->getFolderName();
        $item = $event->getItem()->getCustomName();
        if ($config->get('arena') != null) {
            if ($player->getLevel() === Server::getInstance()->getLevelByName($config->get('arena'))) {
                if ($player->getGamemode() == 3) {
                    if ($item == Color::BLUE . "Selector\n§o§7CLICK TO VIEW") {
                        $form = new Form(function (Player $player, int $data = null) {
                            switch ($data) {
                                case 0:
                                    $player->sendMessage(Color::GRAY . '!Expecting!');
                                    $player->getInventory()->setItem(0, Item::get(399, 0, 1)->setCustomName(Color::BLUE . "Selector\n§o§7CLICK TO VIEW"));
                                break;
                                case 1:
                                    self::joinGame($player);
                                break;
                                case 2:
                                    Server::getInstance()->dispatchCommand($player, 'cf leave');
                                break;
                            }
                        });
                        $form->setTitle(Color::BOLD . Color::GOLD . 'Wath would you like to do?');
                        $form->addButton('Spect');
                        $form->addButton('Respawn');
                        $form->addButton('Go to hub');
                        $player->sendForm($form);        	    
                    }
                }
            }
        }
    }

    public function onRespawn(PlayerRespawnEvent $event) {
        $config = Loader::getConfigs('config');
        $player = $event->getPlayer();
        if ($config->get('arena') != null) {
            if ($player->getLevel() === Server::getInstance()->getLevelByName($config->get('arena'))) {
                $player->setGamemode(3);
                $event->setRespawnPosition(new Position($config->get('x'), $config->get('y'), $config->get('z'), Server::getInstance()->getLevelByName($config->get('arena'))));
                $form = new Form(function (Player $player, int $data = null) {
                    switch ($data) {
                        case 0:
                            $player->sendMessage(Color::GRAY . '!Expecting!');
                            $player->getInventory()->setItem(4, Item::get(399, 0, 1)->setCustomName(Color::BLUE . "Selector\n§o§7CLICK TO VIEW"));
                        break;
                        case 1:
                            self::joinGame($player);
                        break;
                        case 2:
                            Server::getInstance()->dispatchCommand($player, 'cf leave');
                        break;
                    }
                });
                $form->setTitle(Color::BOLD . Color::GOLD . 'Wath would you like to do?');
                $form->addButton('Spect');
                $form->addButton('Respawn');
                $form->addButton('Go to hub');
                $player->sendForm($form);
            }
        }
    }

    public function onDeath(PlayerDeathEvent $event) {
        $config = Loader::getConfigs('config');
        $player = $event->getPlayer();
        if ($config->get('arena') != null) {
            if ($player->getLevel() === Server::getInstance()->getLevelByName($config->get('arena'))) {
                $event->setDrops([]);
                $causa = $event->getEntity()->getLastDamageCause();
                if ($causa instanceof EntityDamageByEntityEvent) {
                    $damager = $causa->getDamager();
                    if ($damager instanceof Player) {
                        if (Loader::getEconomy() != null) {
                            Loader::getEconomy()->addMoney($damager, 100);
                        }
                        $kills = Loader::getConfigs('kills');
                        $kills->set($damager->getName(), $kills->get($damager->getName()) + 1);
                        $kills->save();
                        foreach ($damager->getLevel()->getPlayers() as $players) {
                            $players->sendMessage(Loader::getPrefix() . Color::GRAY . $player->getName() .Color::WHITE . ' fue asesinado por ' . Color::GOLD . $damager->getName());
                            $players->getLevel()->addSound(new ClickSound($player));
                        }
                        Server::getInstance()->getLevelByName($damager->getLevel()->getFolderName())->broadcastLevelSoundEvent(new Vector3($damager->getX(), $damager->getY(), $damager->getZ()), LevelSoundEventPacket::SOUND_NOTE);
                        $damager->sendMessage(Color::GREEN . 'Has recibido ' . Color::GRAY . '100 ' . Color::GREEN . 'coins!');
                        $damager->setHealth(20);
                        $damager->setFood(20);
                    }
                }
            }
        }
    }

    public static function joinGame(Player $player) {
        $config = Loader::getConfigs('config');
        if ($config->get('arena') != null) {
            $player->teleport(new Position($config->get('x'), $config->get('y'), $config->get('z'), Server::getInstance()->getLevelByName($config->get('arena'))));
            if ($player->getGamemode() != 3) {
                $player->sendMessage(Loader::getPrefix() . Color::GRAY . 'You have joined, remember to use /cf leave to exit.');
            }
            foreach ($player->getLevel()->getPlayers() as $players) {
                if ($player->getGamemode() != 3) {
                    $players->sendMessage(Loader::getPrefix() . Color::GRAY . $player->getName() . Color::DARK_AQUA . ' entered ComboFly.');
                    $players->getLevel()->addSound(new EndermanTeleportSound($players));
                }
            }
            $player->addTitle(Color::GOLD . 'ComboFly', Color::GRAY . 'Author: @MikeRangelMR');
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();
            $player->removeAllEffects();
            $player->setAllowFlight(false);
            $player->setFlying(false);
            $player->setHealth(20);
            $player->setFood(20);
            $player->setGamemode(0);
            $player->setScale(1);
            $player->getInventory()->setItem(0, Item::get(276, 0, 1));
            $player->getInventory()->setItem(1, Item::get(466, 0, 32));
            $player->getInventory()->setItem(2, Item::get(368, 0, 16));
            $player->getInventory()->setItem(3, Item::get(438, 16, 1));
            $player->getInventory()->setItem(4, Item::get(438, 16, 1));
            $player->getInventory()->setItem(5, Item::get(438, 16, 1));
            $player->getInventory()->setItem(6, Item::get(438, 16, 1));
            $player->getInventory()->setItem(7, Item::get(438, 16, 1));
            $armor = $player->getArmorInventory();
            $protection = Enchantment::getEnchantment(Enchantment::PROTECTION);
		    $unbreaking = Enchantment::getEnchantment(Enchantment::UNBREAKING);
            $helmet = Item::get(Item::DIAMOND_HELMET, 0, 1);
		    $helmet->addEnchantment(new EnchantmentInstance($protection));
		    $helmet->addEnchantment(new EnchantmentInstance($unbreaking));
		    $chestplate = Item::get(Item::DIAMOND_CHESTPLATE, 0, 1);
		    $chestplate->addEnchantment(new EnchantmentInstance($protection));
            $chestplate->addEnchantment(new EnchantmentInstance($unbreaking));
		    $leggings = Item::get(Item::DIAMOND_LEGGINGS, 0, 1);
		    $leggings->addEnchantment(new EnchantmentInstance($protection));
		    $leggings->addEnchantment(new EnchantmentInstance($unbreaking));
		    $boots = Item::get(Item::DIAMOND_BOOTS, 0, 1);
		    $boots->addEnchantment(new EnchantmentInstance($protection));
		    $boots->addEnchantment(new EnchantmentInstance($unbreaking));
		    $armor->setHelmet($helmet);
		    $armor->setBoots($boots);
		    $armor->setChestplate($chestplate);
		    $armor->setLeggings($leggings);
        } else {
            $player->sendMessage(Loader::getPrefix() . Color::RED . 'No se ha establecido una arena aun.');
        }
    }
}
?>
