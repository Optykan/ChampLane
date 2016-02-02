<?php

/*
exhaust is 3
heal is 7
smite is 11
tp is 12
ignite is 14
barrier is 21


rLane returns the lane determined
distTeam finds Undetermined lanes and puts the remaining lane in
probability returns an array of probability
lanes should arrange one team
laneMaster should arrange the game

*/
function translate($id){
  switch ($id) {
    case 0:
      return "Top";
    case 1:
      return "Jungle";
    case 2:
      return "Mid";
    case 3:
      return "Support";
    case 4:
      return "Marksman";
    default:
      return "Undetermined";
  }
}
function rLane ($prob, $read=true){
  $max=0;
  $id=-1;
  for ($i=0;$i<5;$i++){
    if ($prob[$i]>$max){
      $max=$prob[$i];
      $id=$i;
    }else if($prob[$i]==$max && $max!=0){
      if($read){
        return "Undetermined";
      }
      else {
        return -1;
      }
    }
  }
  if ($read){
    switch ($id) {
      case 0:
        return "Top";
      case 1:
        return "Jungle";
      case 2:
        return "Mid";
      case 3:
        return "Support";
      case 4:
        return "Marksman";
      default:
        return "Undetermined";
    }
  }
  else
    return $id;
}
function confidence($prob){
  $sum=0;
  $lane=rLane($prob, false);
  for($i=0;$i<5;$i++){
    $sum+=$prob[$i];
  }
  if($lane==-1){
    return 0;
  }
  return $prob[$lane]/$sum;
}

function distTeam($preArr){
  $arranged=$preArr;

  #search for -1
  $unset=array_search(-1,$arranged);

  if($unset>=0){
    for($i=0;$i<5;$i++){
      $index=array_search($i, $arranged);
      if($index===false){
        $arranged[$unset]=$i;
        break;
      }
    }
  }
  return $arranged;
}

function probability($c, $s1, $s2){
  $champions = json_decode(file_get_contents("json/champions.json"), true);
  $tags = json_decode(file_get_contents("json/tags.json"), true);
  //returns the probability of where a champion will go (in a 5 point array)
  //$ignore is an array containing lanes to ignore
  $probability=array (0,0,0,0,0);
  $spells=array($s1, $s2);
  $ctags = $tags['data'][$champions[$c]['key']]['tags'];
  $pref=$tags['data'][$champions[$c]['key']]['pref'];

  //should test the tags first
  //then summoner spells

  //top
  if(in_array("Tank", $ctags)){
    if(in_array("Fighter", $ctags)){
      if(in_array(12, $spells)){
        $probability[0]+=10;
        if(in_array(0, $pref)){
          $probability[0]+=-2*(array_search(0, $pref))+7;
        }
      }else{
        $probability[0]+=5;
        if(in_array(0, $pref)){
          $probability[0]+=-2*(array_search(0, $pref))+7;
        }
      }
    }else{
      $probability[0]+=3;
      if(in_array(0, $pref)){
        $probability[0]+=-2*(array_search(0, $pref))+7;
      }
    }
  }
  if($probability[0]==0 && in_array(0, $pref)){
    $probability[0]+=-2*(array_search(0, $pref))+7;
  }


  //jungle
  if(in_array(11, $spells)){
    $probability[1]+=20;
    if(in_array(1, $pref)){
      $probability[1]+=-2*(array_search(1, $pref))+7;
    }
  }
  if($probability[1]==0 && in_array(1, $pref)){
    $probability[1]+=-2*(array_search(1, $pref))+7;
  }

  //mid
  if(in_array("Mage", $ctags) || in_array("Assassin", $ctags)){
    if(in_array(14, $spells) || (in_array(21, $spells) && in_array("Mage", $ctags)) || (in_array(12, $spells) && in_array("Assassin", $ctags))){
      # so we can have a mage/assassin with ignite
      # a mage with barrier (thinking orianna here)
      # or an assassin with tp (maybe riven went mid idunno)
      $probability[2]+=10;
      if(in_array(2, $pref)){
        $probability[2]+=-2*(array_search(2, $pref))+7;
      }
    }else{
      $probability[2]+=5;
      if(in_array(2, $pref)){
        $probability[2]+=-2*(array_search(2, $pref))+7;
      }
    }
  }
  if($probability[2]==0 && in_array(2, $pref)){
    $probability[2]+=-2*(array_search(2, $pref))+7;
  }

  //support
  if($ctags[0]=="Support"){
    if(in_array(3, $spells) || in_array(14, $spells)){
      $probability[3]+=10;
      if(in_array(3, $pref)){
        $probability[3]+=-2*(array_search(3, $pref))+7;
      }
    }else{
      $probability[3]+=5;
      if(in_array(3, $pref)){
        $probability[3]+=-2*(array_search(3, $pref))+7;
      }
    }
  }else if($ctags[1]=="Support"){
    $probability[3]+=3;
    if(in_array(3, $pref)){
      $probability[3]+=-2*(array_search(3, $pref))+7;
    }
  }
  if($probability[3]==0 && in_array(3, $pref)){
    $probability[3]+=-2*(array_search(3, $pref))+7;
  }


  //bot
  if(in_array("Marksman", $ctags)){
    if(in_array(7, $spells)){
      $probability[4]+=10;
      if($probability[4]==0 && in_array(4, $pref)){
        $probability[4]+=-2*(array_search(4, $pref))+7;
      }
    }else {
      $probability[4]+=5;
      if($probability[4]==0 && in_array(4, $pref)){
        $probability[4]+=-2*(array_search(4, $pref))+7;
      }
    }
  }
  if($probability[4]==0 && in_array(4, $pref)){
    $probability[4]+=-2*(array_search(4, $pref))+7;
  }
  return $probability;

}
function lanes($c, $s, $conf){
  //acceptes the same conditions,
  //$c contains 5 champions
  //$s is an array containing 10 spells
  $arrangement=array(0,0,0,0,0);
  for($i=0;$i<5;$i++){
    # set the arrangement by calculating the probability of each champion
    $arrangement[$i]=rLane(probability($c[$i], $s[(2*$i)], $s[2*$i+1]), false);
    $conf[$i]=confidence(probability($c[$i], $s[(2*$i)], $s[2*$i+1]));
  }
  return array_merge(distTeam($arrangement), $conf);
}

function laneMaster ($c, $s, $conf){
  //$c is an array containing all 10 players
  //$s contains 20 spells
  $blue[] = lanes(array_slice($c, 0, 5), array_slice($s, 0, 10), array_slice($conf, 0, 5));
  $red[] = lanes(array_slice($c, 5, 5), array_slice($s, 10, 10), array_slice($conf, 5, 5));

  return array_merge($blue, $red);


  //this function should return (in order that they were accepted)
  // the lane that each champion is going in
  //top, jungle, mid, support, adc
}
  $region='NA1';
  $id=$_GET['id'];

  //$data = json_decode(file_get_contents("https://na.api.pvp.net/observer-mode/rest/consumer/getSpectatorGameInfo/$region/$id?api_key=$key"), true);
  $data =json_decode(file_get_contents("json/testfile.json"), true);
  $skins =json_decode(file_get_contents("json/skins.json"), true);
  $champname = json_decode(file_get_contents("json/champions.json"), true);
  $spellname = json_decode(file_get_contents("json/spells.json"), true);

  //$ranked=json_decode(file_get_contents("https://na.api.pvp.net/api/lol/na/v2.5/league/by-summoner/$summonerIdRequest/entry?api_key=$key"), true);
  //$ranked=json_decode(file_get_contents("json/testranked.json"), true);

  $champions=array(0,0,0,0,0,0,0,0,0,0);
  $spells=array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
  $confidence=array(0,0,0,0,0,0,0,0,0,0);
  foreach ($data['participants'] as $key=>$player){
    $champions[$key]=$player['championId'];
    $spells[$key*2]=$player['spell1Id'];
    $spells[$key*2+1]=$player['spell2Id'];
  }
  $team=laneMaster($champions, $spells, $confidence);
  for($i=0;$i<5;$i++){
    echo translate($team[0][$i]);
    echo $champname[$champions[$i]]['name']." - ";
    echo $spellname[$spells[2*$i]]['name']." - ";
    echo $spellname[$spells[2*$i+1]]['name']." - ";
    echo round((float)$team[0][$i+5] * 100 )."% confident";
    echo "</br>";
  }
  echo "</br>";
  for($i=0;$i<5;$i++){
    echo translate($team[1][$i]);
    echo $champname[$champions[$i+5]]['name']." - ";
    echo $spellname[$spells[2*($i+5)]]['name']." - ";
    echo $spellname[$spells[2*($i+5)+1]]['name']." - ";
    echo round((float)$team[0][$i+5] * 100 )."% confident";
    echo "</br>";
  }

?>
