<?php
declare(strict_types = 1);
namespace repairandfix\Commands;
use repairandfix\BaseFiles\BaseAPI;
use repairandfix\BaseFiles\BaseCommand;
use repairandfix\command\CommandSender;
use repairandfix\Player;
use repairandfix\utils\TextFormat;
class Repair extends BaseCommand{
    /**
     * @param BaseAPI $api
     */
    public function __construct(BaseAPI $api){
        parent::__construct($api, "repair", "Repair Items In Your Inventory", "[all|hand]", false, ["fix"]);
        $this->setPermission("repair.use");
    }
    /**
     * @param CommandSender $sender
     * @param string $alias
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        if(!$sender instanceof Player || count($args) > 1){
            $this->sendUsage($sender, $alias);
            return false;
        }
        $a = "hand";
        if(isset($args[0])) {
            $a = strtolower($args[0]);
        }
        if(!($a === "hand" || $a === "all")){
            $this->sendUsage($sender, $alias);
            return false;
        }
        if($a === "all"){
            if(!$sender->hasPermission("repair.all")){
                $sender->sendMessage(TextFormat::RED . $this->getPermissionMessage());
                return false;
            }
            foreach($sender->getInventory()->getContents() as $index => $item){
                if($this->getAPI()->isRepairable($item)){
                    if($item->getDamage() > 0){
                        $sender->getInventory()->setItem($index, $item->setDamage(0));
                    }
                }
            }
            $m = TextFormat::GREEN . "All The Tools In Your Inventory Were Repaired!!!";
            if($sender->hasPermission("repair.armor")){
                foreach($sender->getArmorInventory()->getContents() as $index => $item){
                    if($this->getAPI()->isRepairable($item)){
                        if($item->getDamage() > 0){
                            $sender->getArmorInventory()->setItem($index, $item->setDamage(0));
                        }
                    }
                }
                $m .= TextFormat::AQUA . " (Including The Equipped Armor)";
            }
        }else{
            $index = $sender->getInventory()->getHeldItemIndex();
            $item = $sender->getInventory()->getItem($index);
            if(!$this->getAPI()->isRepairable($item)){
                $sender->sendMessage(TextFormat::RED . "This Item Can Not Be Repaired!!!");
                return false;
            }
            if($item->getDamage() > 0){
                $sender->getInventory()->setItem($index, $item->setDamage(0));
            }else{
                $sender->sendMessage(TextFormat::RED . "Item Does Not Have Any Damage!!!");
            }
            $m = TextFormat::GREEN . "Item Successfully Repaired!!!";
        }
        $sender->sendMessage($m);
        return true;
    }
}
