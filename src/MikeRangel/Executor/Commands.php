<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 2.0.0
 * Status: Publico
*/
namespace MikeRangel\Executor;
use MikeRangel\{Loader, Events\GlobalEvents, Entity\EntityManager, Entity\types\HumanEntity, Entity\types\TopsEntity};
use pocketmine\{Server, Player, utils\TextFormat as Color};
use pocketmine\command\{PluginCommand, CommandSender};

class Commands extends PluginCommand {

    public function __construct(Loader $plugin) {
        parent::__construct('cf', $plugin);
        $this->setDescription('Mira los comandos disponibles.');
    }

    public function execute(CommandSender $player, $label, array $args) {
        if (!isset($args[0])) {
            $player->sendMessage(Color::RED . 'Usage: /cf help');
            return false;
        }
        switch ($args[0]) {
            case 'help':
                $date = [
                    '/cf help: Help commands.',
                    '/cf create <arena>: Create an arena.',
                    '/cf setspawn: Set the place of appearance.',
                    '/cf entity <stats|game|remove>: Entities command.',
                    '/cf join: Enter the arena.',
                    '/cf leave: Get out of the arena.',
                    '/cf credits: View author.'
                ];
                $player->sendMessage(Color::GOLD . 'ComboFly Commands:');
                foreach ($date as $help) {
                    $player->sendMessage(Color::GREEN . $help);
                }
            break;
            case 'create':
                if ($player->isOp()) {
                    if (!empty($args[1])) {
                        if (file_exists(Server::getInstance()->getDataPath() . 'worlds/' . $args[1])) {
                            Server::getInstance()->loadLevel($args[1]);
                            Server::getInstance()->getLevelByName($args[1])->loadChunk(Server::getInstance()->getLevelByName($args[1])->getSafeSpawn()->getFloorX(), Server::getInstance()->getLevelByName($args[1])->getSafeSpawn()->getFloorZ());
                            $player->teleport(Server::getInstance()->getLevelByName($args[1])->getSafeSpawn(), 0, 0);
                            $config = Loader::getConfigs('config');
                            $config->set('arena', $args[1]);
                            $config->set('serverip', 'honorgames.com.mx');
                            $config->save();
                            $player->sendMessage(Loader::getPrefix() . Color::GREEN . 'Se ha establecido esta arena con exito.');
                        } else {
                            $player->sendMessage(Loader::getPrefix() . Color::RED . 'Este mundo no existe.');
                        }
                    } else {
                        $player->sendMessage(Loader::getPrefix() . Color::RED . 'Usage: /cf create <arena>');
                    }
                } else {
                    $player->sendMessage(Loader::getPrefix() . Color::RED . 'No tienes permisos para ejecutar este comando.');
                }
            break;
            case 'setspawn':
                if ($player->isOp()) {
                    $config = Loader::getConfigs('config');
                    if ($player->getLevel() === Server::getInstance()->getLevelByName($config->get('arena'))) {
                        $config->set('x', $player->getX());
                        $config->set('y', $player->getY());
                        $config->set('z', $player->getZ());
                        $config->save();
                        $player->sendMessage(Loader::getPrefix() . Color::GREEN . 'Spawn establecido con exito.');
                    } else {
                        $player->sendMessage(Loader::getPrefix() . Color::RED . 'Esta arena no ha sido registrada.');
                    }
                }
            break;
            case 'entity':
                if ($player->isOp()) {
                    if (!empty($args[1])) {
                        switch ($args[1]) {
                            case 'game':
                                $entity = new EntityManager();
                                $entity->setGame($player);
                                $player->sendMessage(Loader::getPrefix() . Color::GREEN . 'Entidad establecida con exito.');
                            break;
                            case 'stats':
                                $entity = new EntityManager();
                                $entity->setTops($player);
                                $player->sendMessage(Loader::getPrefix() . Color::GREEN . 'Entidad establecida con exito.');
                            break;
                            case 'remove':
                                foreach ($player->getLevel()->getEntities() as $entity) {
                                    if ($entity instanceof HumanEntity) {
                                        $entity->kill();
                                    } else if ($entity instanceof TopsEntity) {
                                        $entity->kill();
                                    }
                                }
                            break;
                        }
                    } else {
                        $player->sendMessage(Loader::getPrefix() . Color::RED . 'Usage: /cf entity <stats|game>');
                    }
                } else {
                    $player->sendMessage(Loader::getPrefix() . Color::RED . 'No tienes permisos para ejecutar este comando.');
                }
            break;
            case 'join':
                if ($player->getLevel() === Server::getInstance()->getDefaultLevel()) {
                    GlobalEvents::joinGame($player);
                } else {
                    $player->sendMessage(Loader::getPrefix() . Color::RED . 'No puedes ejecutar este comando fuera del Lobby.');
                }
            break;
            case 'leave':
                $config = Loader::getConfigs('config');
                if ($player->getLevel() === Server::getInstance()->getLevelByName($config->get('arena'))) {
                    $api = Loader::getScore();
                    $api->remove($player);
                    $player->getInventory()->clearAll();
                    $player->getArmorInventory()->clearAll();
                    $player->setHealth(20);
                    $player->setFood(20);
                    $player->setGamemode(0);
                    $player->teleport(Server::getInstance()->getDefaultLevel()->getSafeSpawn());
                } else {
                    $player->sendMessage(Loader::getPrefix() . Color::RED . 'No puedes ejecutar este comando fuera de la arena.');
                }
            break;
            case 'credits':
                $description = [
                    'Author: ' . Color::GRAY . '@MikeRangelMR',
                    'Status: ' . Color::GREEN . 'ComboFly is public.'
                ];
                foreach ($description as $credits) {
                    $player->sendMessage(Color::GOLD . $credits);
                }
            break;
        }
    }
}
?>