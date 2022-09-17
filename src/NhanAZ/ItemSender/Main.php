<?php

declare(strict_types=1);

namespace NhanAZ\ItemSender;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
USE pocketmine\item\VanillaItems;

class Main extends PluginBase {

	// TODO: Stop hardcode strings

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
		if ($command->getName() === "itemsender") {
			if (!$sender instanceof Player) {
				$sender->sendMessage("§cYou can't use this command in the terminal!");
				return true;
			}
			$senderPos = $sender->getPosition();
			if (!isset($args[0]) || !isset($args[1])) {
				$sender->sendMessage("§cPlease do not leave arguments blank!");
				$sender->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("mob.villager.no", $senderPos->getX(), $senderPos->getY(), $senderPos->getZ(), 1, 1));
				return false;
			}
			$player = $this->getServer()->getPlayerByPrefix($args[0]);
			if (!$player) {
				$sender->sendMessage("§cThe player with the name §b$args[0] §adoes not exist or is not online.");
				$sender->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("mob.villager.no", $senderPos->getX(), $senderPos->getY(), $senderPos->getZ(), 1, 1));
				return true;
			}
			if (!is_numeric($args[1]) || $args[1] < 1) {
				$sender->sendMessage("§b<amount>§c must be numeric and greater than §b0");
				$sender->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("mob.villager.no", $senderPos->getX(), $senderPos->getY(), $senderPos->getZ(), 1, 1));
				return true;
			}
			if ($sender == $player) {
				$sender->sendMessage("§cYou can't send things to yourself");
				$sender->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("mob.villager.no", $senderPos->getX(), $senderPos->getY(), $senderPos->getZ(), 1, 1));
				return true;
			}
			$playerName = $player->getName();
			$item = $sender->getInventory()->getItemInHand();
			$itemName = $item->getName();
			$itemCount = $item->getCount();
			if ($item->equals(VanillaItems::AIR())) {
				// Call to an undefined static method pocketmine\item\VanillaItems::AIR().
				$sender->sendMessage("§cYou can't send §b$itemName §cto §b$playerName");
				$sender->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("mob.villager.no", $senderPos->getX(), $senderPos->getY(), $senderPos->getZ(), 1, 1));
				return true;
			}
			if ($item->getCount() < $args[1]) {
				$sender->sendMessage("§cYou don't have enough §b$args[1] $itemName §cin hand to send §b$playerName");
				$sender->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("mob.villager.no", $senderPos->getX(), $senderPos->getY(), $senderPos->getZ(), 1, 1));
				return true;
			}
			if (!$player->getInventory()->canAddItem($item->setCount((int) $args[1]))) {
				$sender->sendMessage("§b$playerName's §cinventory doesn't have enough space!");
				$sender->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("mob.villager.no", $senderPos->getX(), $senderPos->getY(), $senderPos->getZ(), 1, 1));
				return true;
			}
			$sender->getInventory()->removeItem($item->setCount((int) $args[1]));
			$player->getInventory()->addItem($item->setCount((int) $args[1]));
			$sender->sendMessage("§aSent §b$itemCount $itemName §ato §b$playerName");
			$sender->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("mob.villager.yes", $senderPos->getX(), $senderPos->getY(), $senderPos->getZ(), 1, 1));
			$player->sendMessage("§aYou received §b$itemCount $itemName §afrom §b$playerName");
			$playerPos = $player->getPosition();
			$player->getNetworkSession()->sendDataPacket(PlaySoundPacket::create("mob.villager.yes", $playerPos->getX(), $playerPos->getY(), $playerPos->getZ(), 1, 1));
			return true;
		}
		return false;
	}
}
