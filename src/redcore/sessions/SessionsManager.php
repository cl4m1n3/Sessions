<?php

namespace redcore\sessions;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerLoginEvent, PlayerQuitEvent};
use pocketmine\command\{Command, CommandSender};
use pocketmine\player\Player;

class SessionsManager extends PluginBase implements Listener{
	
	public $player;
	
	protected function onEnable() : void
    {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getScheduler()->scheduleRepeatingTask(new Counter($this), 20);
	}
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool
    {
    	if($sender instanceof Player){
    	    switch($command->getName()){
    	         case "mysessions":
                      $this->mySessionsForm($sender);
                      return true;
                 break;
                 case "getsessions":
                     if(isset($args[0])){
                     	if(is_file($this->getDataFolder() . "{$args[0]}.yml")){
                     	    $this->player = $args[0];
                     	    $this->getSessionsForm($sender);
                             return true;
                     	}else{
                     	    $sender->sendMessage("§cPlayer {$args[0]} has never played on the server. Check the correctness of the entered nickname!");
                             return true;
                     	}
                     }else{
                     	$sender->sendMessage("§cUse it correctly: /getsessions <player>");
                         return true;
                     }
                 return true;
                 break;
    	    }
        }
    }
	public function onLogin(PlayerLoginEvent $event)
    {
    	$cfg = new Config($this->getDataFolder() . "{$event->getPlayer()->getName()}.yml", Config::YAML, array("seconds" => 0, "countsessions" => 0, "joindate" => "null"));
        $date = date("d.m.20y-h:i:s");
        $cfg->set("joindate", $date);
        $cfg->save();
	}
	public function onQuit(PlayerQuitEvent $event)
    {
    	$cfg = new Config($this->getDataFolder() . "{$event->getPlayer()->getName()}.yml", Config::YAML, array("seconds" => 0, "countsessions" => 0, "joindate" => "null"));
        $date = date("d.m.20y-h:i:s");
        $cfg->set("countsessions", $cfg->get("countsessions") + 1);
        $cfg->set($cfg->get("countsessions"), "§7[§b{$event->getPlayer()->getNetworkSession()->getIp()}§7]§f: Joined: §e{$cfg->get("joindate")}§f; Quited: §e{$date}§f; Played per session: §a{$cfg->get("seconds")} §fsec.");
        $cfg->set("joindate", "null");
        $cfg->set("seconds", 0);
        $cfg->save();
	}
	public function onTask()
    {
		foreach($this->getServer()->getOnlinePlayers() as $players){
			$cfg = new Config($this->getDataFolder() . "{$players->getName()}.yml", Config::YAML, array("seconds" => 0, "countsessions" => 0, "joindate" => "null"));
			$cfg->set("seconds", (int) $cfg->get("seconds") + 1);
			$cfg->save();
		}
	}
	public function mySessionsForm(Player $player)
    {
    	if($this->getServer()->getPluginManager()->getPlugin("FormAPI")->isEnabled()){
    	    $form = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(function (Player $player, array $data = null){
    	         if($data === null){
    	             return true;
    	         }
    	     });
             $cfg = new Config($this->getDataFolder() . "{$player->getName()}.yml", Config::YAML, array("seconds" => 0, "countsessions" => 0, "joindate" => "null"));
             $form->setTitle("§8Your Sessions");
             $form->addLabel("§fTotal sessions: §e{$cfg->get("countsessions")}\n§fThis session: §e{$cfg->get("seconds")} §7(from {$cfg->get("joindate")}) §fsec.");
             if($cfg->get("countsessions") > 0){
             	for($i = $cfg->get("countsessions"); $i >= 1; $i--){
                   	$form->addLabel($cfg->get($i));
                 }
             }else{
             	$form->addLabel("§cYou haven't finished any sessions yet.");
             }
             $form->sendToPlayer($player);
             return $form;
    	}else{
		    $player->sendMessage("§cThe FormAPI plugin is required to work.");
		}
	}
	public function getSessionsForm(Player $player)
    {
		if($this->getServer()->getPluginManager()->getPlugin("FormAPI")->isEnabled()){
    	    $form = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(function (Player $player, array $data = null){
    	         if($data === null){
    	             return true;
    	         }
    	     });
             $cfg = new Config($this->getDataFolder() . "{$this->player}.yml", Config::YAML, array("seconds" => 0, "countsessions" => 0, "joindate" => "null"));
             $form->setTitle("§8{$this->player} player sessions");
             if($this->getServer()->getPlayerByPrefix($this->player)->isOnline()){
             	$status = "§l§aOnline§r";
             }else{
             	$status = "§l§cOffline§r";
             }
             $form->addLabel("§fTotal sessions: §e{$cfg->get("countsessions")}\n§fThis session: §e{$cfg->get("seconds")} §7(from {$cfg->get("joindate")}) §fsec.\n§fOnline status: {$status}");
             if($cfg->get("countsessions") > 0){
                 for($i = $cfg->get("countsessions"); $i >= 1; $i--){
                   	$form->addLabel($cfg->get($i));
                 }
             }else{
             	$form->addLabel("§cThis player has not finished any session yet.");
             }
             $form->sendToPlayer($player);
             return $form;
    	}else{
            $player->sendMessage("§cThe FormAPI plugin is required to work.");
        }
	}
}