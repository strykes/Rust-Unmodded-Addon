<?php
function DBB($host,$login,$pass,$db,$table)
{
	try
	{
	$pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
	$bdd = new PDO("mysql:host=".$host.";dbname=".$db,$login,$pass, $pdo_options);
	}
	catch (Exception $e)
	{
		die('Error : ' . $e->getMessage());
		return false;
	}		
	return $bdd;
	
}
function put_in_mysql($dbb,$type,$header,$txt)
{
	global $config;
	$header = addslashes($header);
	$txt = addslashes($txt);
	$dbb->exec("INSERT INTO ".$config["mysql_table"]."(type,header,text)	
               VALUES('$type','$header','$txt')");
	
}
function GetVar($var)
{  
  $file = fopen("settings/settings.php",'r');

  if(!$file)
  return 0;

  $contents = "";
  while(!feof($file))
  $contents .= fgets($file,256);

  $line = explode("\n",$contents); 
  

  
  for($i=0;$i<count($line);$i++)
  {
   if(substr($line[$i],0,1) != '#')   
  { 
    $lineEx = explode(' ',$line[$i]);
    
    if(strcmp($lineEx[0],$var) == 0)
    {
     fclose($file);
     return trim($lineEx[2]);
    }   
   }
  }  
  
  fclose($file);
}
function GetJailed($var)
{  
  $file = fopen("jailed.ini",'r');

  if(!$file)
  return 0;

  $contents = "";
  while(!feof($file))
  $contents .= fgets($file,256);

  $line = explode("\n",$contents); 
  
  for($i=0;$i<count($line);$i++)
  {
   if(substr($line[$i],0,1) != '#')   
  { 
    $lineEx = explode(' ',$line[$i]);
    
    if(strcmp($lineEx[0],$var) == 0)
    {
     fclose($file);
     return $lineEx[0];
    }   
   }
  }  
  
  fclose($file);
  return 0;
}
function sendcmd($cmd)
{
	global $conn,$config;
	$data = pack("VV",1,02).$cmd.chr(0).''.chr(0);
	$data = pack("V",strlen($data)).$data;
	fwrite($conn, $data, strlen($data));
	
	$data = pack("VV",1,03).$config["server_rcon"].chr(0).''.chr(0);
	$data = pack("V",strlen($data)).$data;
	fwrite($conn, $data, strlen($data));
}
function isallowed($name,$lvl)
{
	if($lvl == 0) return false;
	elseif($lvl == 1) return true;
	elseif($lvl == 2)
		if(isadmin($name))
			return true;
	return false;
}
function isadmin($name)
{
	global $playerlist,$admins;
	$found = 0;
	$foundsteam = false;
	foreach($playerlist as $steam => $info)
	{
		if($info["name"] == $name)
		{
			$found++;
			$foundsteam = $steam;
		}
	}
	if($found>1)
	{
		return false;
		//if the admin share the same name as another user => ignore
	}
	elseif($found == 0)
		return false;
	print($foundsteam);
	if($playerlist[$foundsteam]["isadmin"])
		return true;
	return false;
}
function findplayer($name)
{
	global $playerlist;
	$found = 0;
	$foundsteam = false;
	$foundname = false;
	$foundexactcase = false;
	$foundexactnocase = false;
	$foundnotexactcase = false;
	if(is_numeric($name) && strlen($name==17))
	{
		$found = 1;
		$foundsteam = $name;	
	}
	else
	{
		foreach($playerlist as $steam => $info)
		{
			if($info["name"]===$name)
			{
				$found++;
				$foundexactcase = true;
				$foundsteam = $steam;
				$foundname = $info["name"];
			}
			elseif((strtolower($info["name"])==strtolower($name)) and (!$foundexactcase))
			{
				$found++;
				$foundexactnocase = true;
				$foundsteam = $steam;
				$foundname = $info["name"];
			}
			elseif((strpos($info["name"],$name)!==false) and (!$foundexactcase) and (!$foundexactnocase))
			{
				$found++;
				$foundsteam = $steam;
				$foundname = $info["name"];
				$foundnotexactcase = true;
			}
			elseif((strpos(strtolower($info["name"]),strtolower($name))!==false) and (!$foundexactcase) and (!$foundexactnocase) and (!$foundnotexactcase))
			{
				$found++;
				$foundsteam = $steam;
				$foundname = $info["name"];
			}
		}
	}
	if($found>1)
	{
		return "Multiple Match for ".$name;	
	}
	if($found == 0)
	{
		return "No Match found for ".$name;	
	}
	return array("steam"=>$foundsteam,"name"=>$foundname);
}
function parsestatus_continue($data)
{
	global $playerlist,$admins,$cutline;
	if($cutline !== false)
	{
		if(!preg_match("/([0-9]{17})\W(.{36})/",$data[0],$output))
		{
			$data[0] = $cutline.$data[0];
		}
	}
	foreach($data as $pid => $playerdata)
	{
		if(preg_match("/([0-9]{17})\W(.{36})/",$playerdata,$output))
		{
			$playerlist[$output[1]] = array("name"=>cleanname($output[2]));
			$playerlist[$output[1]]["isadmin"] = false;
			if(isset($admins[$output[1]]))
				if($admins[$output[1]])
					$playerlist[$output[1]]["isadmin"] = true;
		}
		else
		{
			//print("ERROR WHILE PARSING 1 PLAYER\n");
			$cutline = $playerdata;
			//print('<<'.$playerdata.'>>');
		}
	}
}
function cleanname($name)
{
	$tempname = $name;
	if(strpos($name,'"')===0)
		$tempname = substr($tempname,1);
		
	$tempname = substr($tempname,0,strrpos($tempname,'"'));
	$name = $tempname;
	return $name;
}
function parsestatus($data)
{
	global $playerlist,$admins,$playerlistarray,$cutline;
	foreach($data as $did => $line)
	{
		if(strpos($line,"hostname")===0)
			$serverinfo["hostname"] = substr($line,10);
		if(strpos($line,"version")===0)
			$serverinfo["version"] = substr($line,10);
		if(strpos($line,"map")===0)
			$serverinfo["map"] = substr($line,10);
		if(strpos($line,"players")===0)
			$serverinfo["players"] = substr($line,10);
	}
	$playerlistarray = array_slice($data,6);
	$playerlist = array();
	foreach($playerlistarray as $pid => $playerdata)
	{
		//print($playerdata);
		if(preg_match("/([0-9]{17})\W(.{36})/",$playerdata,$output))
		{
			$playerlist[$output[1]] = array("name"=>cleanname($output[2]));
			$playerlist[$output[1]]["isadmin"] = false;
			if(isset($admins[$output[1]]))
				if($admins[$output[1]])
					$playerlist[$output[1]]["isadmin"] = true;
		}
		else
		{
			$cutline = $playerdata;
		}
	}
	
}

function onusersuicide($name)
{
	print($name." has commited suicide");
	$found = findplayer($name);
	if(!is_array($found))
	{
		return;
	}
	$searchjailed = GetJailed($found["steam"]);
	if($searchjailed !== 0)
	{
		sendcmd("say \"".$name." tried to suicide out of the jail\"");
		sendcmd("kick \"".$found["steam"]."\"");
	}
}

function onuserconnect($name,$steamid)
{
	global $admins,$playerlist,$config,$timers;
	$playerlist[$steamid] = array();
	if(isset($admins[$steamid]))
		if($admins[$steamid])
			$playerlist[$steamid]["isadmin"]=true;
		else
		$playerlist[$steamid]["isadmin"]=false;
	else
		$playerlist[$steamid]["isadmin"]=false;

		
	$playerlist[$steamid]["name"] = $name;
	
	if(GetVar("broadcast_connections") == 1)
		sendcmd("say \"". $name. " has joined the game\"");	
	if(GetVar("restricted_names") == 1)
		if(isset($config["restricted_names"]) && (!$playerlist[$steamid]["isadmin"]))
			foreach($config["restricted_names"] as $nid => $restricted_name)
				if(strpos($name,$restricted_name)!==false)
				{
					sendcmd("say \"". $name. " has restricted characters in his name (Auto kick)\"");
					sendcmd("kick ".$steamid."");
					return;	
				}
				
	if(GetVar("restricted_dual_names") == 1)
	{
		$dual = false;
		foreach($playerlist as $playersteam => $info)
		{
			if(($name == $info["name"]) && ($steamid != $playersteam))
				$dual = $playersteam;
			
		}
		if($dual != false)
		{
			if($playerlist[$steamid]["isadmin"])
			{
				if(!$playerlist[$dual]["isadmin"])
				{
					sendcmd("say \"". $name. " has stolen an admin name (Auto kick)\"");
					sendcmd("kick ".$dual."");
				}
			}
			else
			{
				sendcmd("say \"". $name. " is already connected, dual names are not allowed (Auto kick)\"");
				sendcmd("kick ".$steamid."");
			}
			return;	
		}
	}
	print($steamid);
	print_r($playerlist[$steamid]);
	if(GetJailed($steamid) !== 0)
		$timers[] = array("steam"=>$steamid,"time"=>time()+20,"function"=>"sendcmd","isarray"=>false,"repeat"=>false,"args"=>"teleport.topos \"".$steamid."\" \"".GetVar("jail.x")."\" \"".GetVar("jail.y")."\" \"".GetVar("jail.z")."\"");
}
function onuserdisconnect($name)
{
	global $playerlist;
	$found = findplayer($name);
	if(!is_array($found))
		sendcmd("status");	
	else
		$playerlist[$found["steam"]] = NULL;

	if(GetVar("broadcast_connections") == 1)
		sendcmd("say \"". $name. " has left the game\"");	
}
function sendautomessage()
{
	global $config;
	$tot = count($config["automessages"])-1;
	$rand = rand(0,$tot);
	sendcmd("say \"".addslashes($config["automessages"][$rand])."\"");
}


?>
