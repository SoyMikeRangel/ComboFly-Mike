<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 2.0.0
 * Status: Publico
*/
namespace MikeRangel\Tasks;
use MikeRangel\{Loader, Events\GlobalEvents};
use pocketmine\{Server, Player, utils\TextFormat as Color, level\Level, scheduler\Task};

class ScoreUpdate extends Task {

    public function onRun(int $currentTick) {
        $config = Loader::getConfigs('config');
        $kills = Loader::getConfigs('kills');
        if ($config->get('arena') != null) {
            $arena = Server::getInstance()->getLevelByName($config->get('arena'));
            if ($arena instanceof Level) {
                foreach ($arena->getPlayers() as $player) {
                    $api = Loader::getScore();
                    $api->new($player, $player->getName(), Color::YELLOW . Color::BOLD . 'COMBOFLY');
                    $api->setLine($player, 1, Color::WHITE . 'Arena: ' . Color::GOLD . $config->get('arena'));
                    $api->setLine($player, 2, Color::RED . '  ');
                    $api->setLine($player, 3, Color::WHITE . 'Nick: ' . Color::DARK_AQUA . $player->getName());
                    $api->setLine($player, 4, Color::WHITE . 'Players: ' . Color::DARK_AQUA . EntityUpdate::getPlayers());
                    $api->setLine($player, 5, Color::BLUE . '  ');
                    $api->setLine($player, 6, Color::WHITE . 'CPS: ' . Color::DARK_AQUA . GlobalEvents::getCPS($player));
                    $api->setLine($player, 7, Color::WHITE . 'Kills: ' . Color::DARK_AQUA . $kills->get($player->getName()));
                    $api->setLine($player, 8, Color::GOLD . '  ');
                    $api->setLine($player, 9, Color::WHITE . 'Ping: ' . Color::GRAY . $player->getPing());
                    $api->setLine($player, 10, Color::YELLOW . $config->get('serverip'));
                    $api->getObjectiveName($player);
                }
            }
        }
    }
}
?>