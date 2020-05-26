<?php
declare(strict_types=1);
/*
 * Author: Mike Rangel
 * Version: 2.0.0
 * Status: Publico
*/
namespace MikeRangel;
use MikeRangel\{Arena\Arena, API\ScoreAPI, Events\GlobalEvents, Executor\Commands, Tasks\EntityUpdate, Tasks\ScoreUpdate, Entity\types\HumanEntity, Entity\types\TopsEntity};
use pocketmine\{Server, Player, plugin\PluginBase, entity\Entity, utils\TextFormat as Color, utils\Config};
use onebone\economyapi\EconomyAPI;

class Loader extends PluginBase {

	public static $instance;
	public static $economy;
	public static $score;
	public static $data = [
		'prefix' => Color::GOLD . 'ComboFly' . Color::GRAY . ' » ',
		'cps' => []
	];

	public function onLoad() : void {
		self::$instance = $this;
		self::$score = new ScoreAPI($this);
	}

	public function onEnable() : void {
		$config = self::getConfigs('config');
		if ($config->get('arena') != null) {
			$this->getServer()->loadLevel($config->get('arena'));
		}
		if ($this->getServer()->getPluginManager()->getPlugin('EconomyAPI') != null) {
			self::$economy = EconomyAPI::getInstance();
			$this->getLogger()->info(Color::GREEN . 'La dependencia EconomyAPI ha sido encontrada con exito.');
		} else {
			self::$economy = null;
			$this->getLogger()->info(Color::RED . 'No se ha encontrado la dependencia EconomyAPI.');
		}
		$this->getLogger()->info(Color::GREEN . 'Plugin activado con exito.');
		$this->saveResource('kills.yml');
		$this->loadEntitys();
		$this->loadCommands();
		$this->loadEvents();
		$this->loadTasks();
	}

	public static function getConfigs(string $value) {
		return new Config(self::getInstance()->getDataFolder() . "{$value}.yml", Config::YAML);
	}

	public static function getInstance() : Loader {
		return self::$instance;
	}

	public static function getEconomy() : EconomyAPI {
		return self::$economy;
	}

	public static function getScore() : ScoreAPI {
		return self::$score;
	}
		
	public static function getPrefix() : string {
		return self::$data['prefix'];
	}

	public function loadEntitys() : void {
		$values = [HumanEntity::class, TopsEntity::class];
		foreach ($values as $entitys) {
			Entity::registerEntity($entitys, true);
		}
		unset ($values);
	}

	public function loadCommands() : void {
		$values = [new Commands($this)];
		foreach ($values as $commands) {
			$this->getServer()->getCommandMap()->register('_cmd', $commands);
		}
		unset($values);
	}

	public function loadEvents() : void {
		$values = [new GlobalEvents(), new Arena()];
		foreach ($values as $events) {
			$this->getServer()->getPluginManager()->registerEvents($events, $this);
		}
		unset($values);
	}

	public function loadTasks() : void {
		$values = [new EntityUpdate(), new ScoreUpdate()];
		foreach ($values as $tasks) {
			$this->getScheduler()->scheduleRepeatingTask($tasks, 10);
		}
		unset($values);
	}

	public function onDisable() : void {
		$this->getLogger()->info(Color::RED . 'Plugin desactivado.');
	}
}