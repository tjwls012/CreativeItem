<?php

/**
 * @name CreativeItem
 * @main tjwls012\creativeitem\CreativeItem
 * @author ["tjwls012"]
 * @version 0.1
 * @api 3.14.0
 * @description License : LGPL 3.0
 */
 
namespace tjwls012\creativeitem;
 
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

use pocketmine\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\item\Item;

use pocketmine\utils\Config;

class CreativeItem extends PluginBase implements Listener{

  public static $instance;
  
  public static function getInstance(){
  
    return self::$instance;
  }
  public function onLoad(){
  
    self::$instance = $this;
  }
  public function onEnable(){
  
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    
    $a = new PluginCommand("creativeitem", $this);
    $a->setPermission("op");
    $a->setUsage("/creativeitem");
    $a->setDescription("manage creative inventory items");
    $this->getServer()->getCommandMap()->register($this->getDescription()->getName(), $a);
    
    @mkdir($this->getDataFolder());
    $this->ItemData = new Config($this->getDataFolder()."ItemData.yml", Config::YAML);
    $this->data = $this->ItemData->getAll();
    
    $this->enableItems();
  }
  public function onCommand(CommandSender $sender, Command $command, string $label, array $array) : bool{
  
    if(!$sender instanceof Player) return true;
    
    $command = $command->getName();
    
    $player = $sender;
    
    if(count($array) == 2){
    
      if($array[0] === "add"){
      
        if(!isset($this->data[$array[1]])){
        
          $inventory = $player->getInventory();
          
          $item = $inventory->getItemInHand();
          $nbt = $this->getNBT($item);
          
          $this->data[$array[1]] = $nbt;
          
          $this->save();
          
          $player->sendMessage("you added ".$array[0]." creative item data");
        }
        else{
        
          $player->sendMessage("there is already ".$array[0]." creative item data");
        }
      }
      elseif($array[0] === "remove"){
      
        if(isset($this->data[$array[0]])){
        
          unset($this->data[$array[1]]);
          
          $this->save();
          
          $player->sendMessage("you removed ".$array[0]." creative item data");
        }
        else{
        
          $player->sendMessage("there is no ".$array[0]." creative item data");
        }
      }
      else{
      
        $this->message($player);
      }
    }
    elseif(count($array) == 1){
    
      if($array[0] === "lists"){
      
        $this->sendLists($player);
      }
      else{
      
        $this->message($player);
      }
    }
    else{
    
      $this->message($player);
    }
    
    return true;
  }
  public function message(Player $player){
  
    $player->sendMessage("/creativeitem add <name>");
    $player->sendMessage("/creativeitem remove <name>");
    $player->sendMessage("/creativeitem lists");
  }
  public function sendLists(Player $player){
  
    if(count($this->data) > 0){
    
      foreach($this->data as $name => $nbt){
      
        $player->sendMessage($name);
      }
    }
    else{
    
      $player->sendMessage("there is no creative item data");
    }
  }
  public function enableItems(){
  
    if(count($this->data) > 0){
    
      foreach($this->data as $name => $nbt){
      
        $item = $this->getItem($nbt);
        
        Item::addCreativeItem($item);
      }
    }
  }
  public function getNBT($item){
  
    return $item->jsonSerialize();
  }
  public function getItem($item){
  
    return Item::jsonDeserialize($item);
  }
  public function save(){
  
    $this->ItemData->setAll($this->data);
    $this->ItemData->save();
  }
}