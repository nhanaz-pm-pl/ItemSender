<?php

declare(strict_types=1);

namespace NhanAZ\ItemSender;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\utils\TextFormat;

class Main extends PluginBase {

	private function playSound(Player $player, string $soundName) {
		$playerPos = $player->getPosition();
		$player->getNetworkSession()->sendDataPacket(
			PlaySoundPacket::create(
				soundName: $soundName,
				x: $playerPos->getX(),
				y: $playerPos->getY(),
				z: $playerPos->getZ(),
				volume: 1.0,
				pitch: 1.0
			)
		);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
		if ($command->getName() === "itemsender") {
			if (!$sender instanceof Player) {
				$sender->sendMessage(TextFormat::colorize($this->getConfig()->get("cmdInTerminal")));
				return true;
			}
			if (!isset($args[0]) || !isset($args[1])) {
				$sender->sendMessage(TextFormat::colorize($this->getConfig()->get("argumentsBlank")));
				$this->playSound($sender, "mob.villager.no");
				return false;
			}
			$target = $this->getServer()->getPlayerByPrefix($args[0]);
			if (!$target) {
				$sender->sendMessage(TextFormat::colorize(str_replace("{target}", $args[0], $this->getConfig()->get("invalidTarget"))));
				$this->playSound($sender, "mob.villager.no");
				return true;
			}
			// TODO: Support <amount> > 64
			if (!ctype_digit($args[1]) || $args[1] < 1 || $args[1] > 64) {
				$sender->sendMessage(TextFormat::colorize($this->getConfig()->get("invalidInput")));
				$this->playSound($sender, "mob.villager.no");
				return true;
			}
			if ($sender == $target) {
				$sender->sendMessage(TextFormat::colorize($this->getConfig()->get("sendYourself")));
				$this->playSound($sender, "mob.villager.no");
				return true;
			}
			$targetName = $target->getName();
			$senderName = $sender->getName();
			$item = $sender->getInventory()->getItemInHand();
			$itemName = $item->getName();
			if ($item->isNull()) {
				$sender->sendMessage(TextFormat::colorize(str_replace(["{air}", "{target}"], [$itemName, $targetName], $this->getConfig()->get("sendAir"))));
				$this->playSound($sender, "mob.villager.no");
				return true;
			}
			if ($item->getCount() < $args[1]) {
				$sender->sendMessage(TextFormat::colorize(str_replace(["{itemCount}", "{itemName}", "{target}"], [$args[1], $itemName, $targetName], $this->getConfig()->get("notEnoughItems"))));
				$this->playSound($sender, "mob.villager.no");
				return true;
			}
			if (!$target->getInventory()->canAddItem($item->setCount((int) $args[1]))) {
				$sender->sendMessage(TextFormat::colorize(str_replace("{target}", $targetName, $this->getConfig()->get("notEnoughSpace"))));
				$this->playSound($sender, "mob.villager.no");
				return true;
			}
			$sender->getInventory()->removeItem($item->setCount((int) $args[1]));
			$target->getInventory()->addItem($item->setCount((int) $args[1]));
			$sender->sendMessage(TextFormat::colorize(str_replace(["{itemCount}", "{itemName}", "{target}"], [$args[1], $itemName, $targetName], $this->getConfig()->get("sentSuccessfully"))));
			$this->playSound($sender, "mob.villager.yes");
			$target->sendMessage(TextFormat::colorize(str_replace(["{itemCount}", "{itemName}", "{target}"], [$args[1], $itemName, $senderName], $this->getConfig()->get("receivedSuccessfully"))));
			$this->playSound($target, "mob.villager.yes");
			return true;
		}
		return false;
	}
}
