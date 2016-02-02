<?php
/*exhaust is 3
heal is 7
smite is 11
tp is 12
ignite is 14
barrier is 21*/
function distribute($team){
  $dist=$team;
  for($i=0;$i<5;$i++){
    if(!in_array($i, $dist)){
      #if the lane is not in the array then look for the index of the -1
      # and set it to the lane that doesn't exist
      $dist[array_search(-1, $dist)]=$i;
    }
  }
  return $dist;
}
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
function pLane ($p){
  $lane=-1;
  $max=0;
  for($i=0;$i<5;$i++){
    if($p[$i]>$max){
      $max=$p[$i];
      $lane=$i;
    }else if($max==$p[$i] && $max!=0){
      return -1;
    }
  }
  return $lane;
}
function multiple($p, $fix){
  $lane=-1;
  $max=0;
  for($i=0;$i<5;$i++){
    if($p[$i]>$max && $i!=$fix){
      $max=$p[$i];
      $lane=$i;
    }else if($max==$p[$i] && $max!=0){
      return -1;
    }
  }
  return $lane;
}
function probability($c, $s){
  $champnames = json_decode(file_get_contents("json/champions.json"), true);
  $tagfile = json_decode(file_get_contents("json/tags.json"), true);
  $prob=array(0,0,0,0,0);

  $champion=$champnames[$c]["key"];

  $tags=$tagfile["data"]["$champion"]["tags"];
  $pref=$tagfile["data"]["$champion"]["pref"];

  //top
  if(in_array(0, $pref)){
    $prob[0]+=-2*(array_search(0, $pref))+7;
  }
  if(in_array(12, $s) && $prob[0]>0){
    # if the champ is eligible for top and has tp
    # multiply by 2
    $prob[0]*=2;
  }

  //jungle
  if(in_array(1, $pref)){
    $prob[1]+=-2*(array_search(1, $pref))+7;
  }
  if(in_array(11, $s) && $prob[1]>0){
    $prob[1]*=4;
  }

  //mid
  if(in_array(2, $pref)){
    $prob[2]+=-2*(array_search(2, $pref))+7;
  }
  if($prob[2]>0){
    if(in_array(14, $s) || in_array(21, $s)){
      $prob[2]*=2;
    }
  }
  //support
  if(in_array(3, $pref)){
    $prob[3]+=-2*(array_search(3, $pref))+7;
  }
  if($prob[3]>0){
    if(in_array(3, $s)){
      #can play support and has exhaust
      $prob[3]*=2;
    }else if ($tags[0]=="Support" && in_array(14, $s) || ($tags[1]=="Support" && $tags[0]=="Tank" && in_array(14, $s))){
      #if the first tag is support and they have ignite
      # or its tank, support with ignite then
      $prob[3]*=2;
    }
  }
  //Marksman
  if(in_array(4, $pref)){
    $prob[4]+=-2*(array_search(4, $pref))+7;
  }
  if($prob[4]>0){
    if(in_array(7, $s)){
      $prob[4]*=2;
    }
  }
  return $prob;

}
function topFix($team, $spells){
  $arrangement=$team;
  $conflict=array(-1, -1);
  $fix=-1;
  $key=0;
  for($i=0;$i<5;$i++){
    if($team[$i]==0){
      $conflict[$key]=$i;
      if ($key==2)
        break;
      $key++;
    }
  }
  if($key==0){
    return $arrangement;
  }
  if($spells[$conflict[0]*2]==12 || $spells[($conflict[0]*2)+1]==12){
    # got tp?
    # you're goin top
    $arrangement[$conflict[0]]==1;
    $fix=$conflict[1];
  }else {
    $arrangement[$conflict[1]]==1;
    $fix=$conflict[0];
  }
  //one of them is fixed
  // the other is stored in fix
  $probability=probability($arrangement[$fix], [$spells[$fix*2],$spells[$fix*2+1]]);
  $arrangement[$fix]=multiple($probability, 0);
  return $arrangement;

}

function team($c, $s){
  $arrangement=array(-1,-1,-1,-1,-1);
  for ($i=0;$i<5;$i++){
    $arrangement[$i]=pLane(probability($c[$i], array_slice($s, $i*2, 2)));
  }
  if(array_count_values($arrangement)[0]>1){
    $arrangement=topFix($arrangement, $s);
  }
  if(array_count_values($arrangement)[-1]>1){
    # figure this mess out later
  }else if (array_count_values($arrangement)[-1]==1){
    $arrangement=distribute($arrangement);
  }
  return $arrangement;
}

$region="NA1";
$id=$_GET['id'];
//$data = json_decode(file_get_contents("https://na.api.pvp.net/observer-mode/rest/consumer/getSpectatorGameInfo/$region/$id?api_key=$key"), true);
$data=json_decode(file_get_contents("json/testfile.json"), true);
$champname = json_decode(file_get_contents("json/champions.json"), true);
$spellname = json_decode(file_get_contents("json/spells.json"), true);

$champions=array(0,0,0,0,0,0,0,0,0,0);
$spells=array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);

foreach ($data['participants'] as $key=>$player){
  $champions[$key]=$player['championId'];
  $spells[$key*2]=$player['spell1Id'];
  $spells[$key*2+1]=$player['spell2Id'];
}
for($i=0;$i<10;$i++){
  echo $champname[$champions[$i]]['name']." ".$spellname[$spells[$i*2]]['name']." ".$spellname[$spells[$i*2+1]]['name']."</br>";
}
print_r(team(array_slice($champions, 0, 5), array_slice($spells, 0, 10)));

?>
