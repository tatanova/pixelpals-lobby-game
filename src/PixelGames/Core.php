<?php
namespace PixelGames;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\tile\Sign;
class Core extends PluginBase implements Listener {
  private $claimed, $marks;
  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this,$this);
    @mkdir($this->getDataFolder());
    if(!file_exists($this->getDataFolder()."marks.game")){
      file_put_contents($this->getDataFolder()."marks.game", "[]");
    }
    $this->marks = json_decode(file_get_contents($this->getDataFolder()."marks.game"), true);
    if(!file_exists($this->getDataFolder()."claimed.ppl")){
      file_put_contents($this->getDataFolder()."claimed.ppl", "[]");
    }
    $this->claimed = json_decode(file_get_contents($this->getDataFolder()."claimed.ppl"), true);
  }
  public function onSignChange(SignChangeEvent $e){
    if($e->getLine(0) == "coin-add"){
      $p = $e->getPlayer();
      if(!$p->hasPermission("new.event")){
        $e->setLine(0, "§6* §ePixelPals §6*");
        $e->setLine(1, "§cNo permission!");
        return;
      }
      $b = $e->getBlock();
      $id = $b->getX().":".$b->getY().":".$b->getZ();
      $this->marks[$id] = 1;
      file_put_contents($this->getDataFolder()."marks.game", json_encode($this->marks));
      $e->setLine(0, "§6* §ePixelPals §6*");
      $e->setLine(1, "§a§lCwick meh! :3");
      $p->sendTip("§6* §aCreated §6*");
    }
  }
  public function onPlayerInteract(PlayerInteractEvent $e){
    if($e->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK){
      return;
    }
    $p = $e->getPlayer();
    $b = $e->getBlock();
    $index = $b->getX().":".$b->getY().":".$b->getZ();   
    if(!isset($this->marks[$index])){
      return;
    }
    if(isset($this->claimed[$p->getName()][$index])){
      $p->sendTip("§6* §cYou already collected this §6*");
      return;
    }
    $eco = $this->getServer()->getPluginManager()->getPlugin("PixelPals-Economy-Manager");
    $rand = rand(70, 170);
    $eco->addPlayerMoney($p->getName(), $rand);
    $p->sendMessage("§6* §ePixelPals §a|§f Congratulations, you found a gift! and you get §e".$rand."§f coins!");
    $this->claimed[$p->getName()][$index] = 1;
    file_put_contents($this->getDataFolder()."claimed.ppl", json_encode($this->claimed));
  }
  public function onBlockBreak(BlockBreakEvent $e){
    $b = $e->getBlock();
    $p = $e->getPlayer();
    $id = $b->getX().":".$b->getY().":".$b->getZ();
    if(isset($this->marks[$id])){
      if(!$p->hasPermission("new.event")){
        $e->setCancelled(true);
        return;
      }
      unset($this->marks[$id]);
      $p->sendTip("§6* §aRemoved §6*");
      file_put_contents($this->getDataFolder()."marks.game", json_encode($this->marks));
      $this->reloadClaimed($id);
    }
  }
  private function reloadClaimed($index){
    foreach($this->claimed as &$p){
      if(isset($p[$index])){
        unset($p[$index]);
      }
    }
    file_put_contents($this->getDataFolder()."claimed.ppl", json_encode($this->claimed));
  }
}
