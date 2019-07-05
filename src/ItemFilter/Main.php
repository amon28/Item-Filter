<?php
namespace ItemFilter;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\inventory\Inventory;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\utils\TextFormat as C;
use pocketmine\utils\Config;
use pocketmine\block\Block;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;

class Main extends PluginBase implements Listener{
    
    public $pcfg;
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
     if(!is_dir($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		}
		if(!file_exists($this->getDataFolder() . "Config.yml")){
			$this->saveDefaultConfig();
		}
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, string $label,array $args) : bool {
    $player = $sender->getPlayer();
	if(strtolower(($cmd->getName())) == "ifilter" or "if"){
	if(!isset($args[0])){
	$sender->sendMessage(C::RED.C::UNDERLINE."/ifilter add/del/list");
	return true;
	} 
	$this->playerConfig($player->getName());
	switch($args[0]){
	case "add":
	 $cfg = $this->pcfg->get("Filters");
	 if($player->getInventory()->getItemInHand()->getId() == 0){
	 $sender->sendMessage(C::RED.C::UNDERLINE."You do not have any item in your hand");    
	return true;
	 }
	 $item = ($player->getInventory()->getItemInHand()->getId().":".$player->getInventory()->getItemInHand()->getDamage());
	 array_push($cfg,$item);
	 $this->pcfg->set("Filters",$cfg);
	 $this->pcfg->save();
	 $sender->sendMessage(C::YELLOW.C::UNDERLINE."Succesfully Added Item");
	 
	break;
	
	case "del":
	 $cfg = $this->pcfg->get("Filters");
	 if(!isset($args[1])){
	 $sender->sendMessage(C::RED.C::UNDERLINE."Add Item Id");
	 return true;
	 }else{
	 if(array_search($args[1],$cfg) == null){
	     //For element 0
	 if(in_array($args[1],$cfg)){
	 unset($cfg[0]);
	 $this->pcfg->set("Filters",$cfg);
	 $this->pcfg->save(); 
	 $sender->sendMessage(C::YELLOW.C::UNDERLINE."Succesfully Deleted");
	 return true;
	 }else{
	 $sender->sendMessage(C::RED.C::UNDERLINE."No ItemID found");
	 return true;   
	 }
	 }
	 }
	 unset($cfg[(int)array_search($args[1],$cfg)]);
	 $this->pcfg->set("Filters",$cfg);
	 $this->pcfg->save();
	 $sender->sendMessage(C::YELLOW.C::UNDERLINE."Succesfully Deleted");
	break;
	
	case "list":
	$cfg = $this->pcfg->get("Filters");
	$i = [];
	foreach($cfg as $id){
	$item = explode(":",$id);
	$iname = ($id."=".Item::get($item[0],$item[1],0)->getName());     
	array_push($i,$iname);
	}
	$sender->sendMessage(C::YELLOW.C::UNDERLINE."Filters: ".implode(", ",$i));
	break;
	}
	
	}
	return true;
    }
    
    public function playerConfig(String $name){
    if(!file_exists($this->getDataFolder().$name.".yml")){
			$this->pcfg = new Config($this->getDataFolder() .$name.".yml");
			$this->pcfg->set("Filters",[]);
			$this->pcfg->save();
    }else{
    $this->pcfg = new Config($this->getDataFolder() .$name.".yml");
    }
    }
    
    public function onPickupItem(InventoryPickupItemEvent $ev){
   $player = $ev->getInventory()->getHolder();
   if(!isset($this->pcfg)){
   $this->playerConfig($player->getName());
   }
   $id = $ev->getItem()->getItem()->getId();
   $damage = $ev->getItem()->getItem()->getDamage();
   $item = $id.":".$damage;
   $cfg = $this->pcfg->get("Filters");
   if(in_array(strval($item),$cfg)){
   $ev->setCancelled();
   }
}

    public function onDisable(){
     $this->getLogger()->info("§cOffline");
    }
}
