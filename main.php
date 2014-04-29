<?php
include("commands.php");
include("config.php");

try
{
	$conn = @fsockopen($config["server_ip"], $config["server_queryport"], $errno, $errstr, 2);
}
catch (Exception $err) { }

$started = false;
$serverinfo = array();
$playerlist = array();

$data = pack("VV",1,03).$config["server_rcon"].chr(0).''.chr(0);
$data = pack("V",strlen($data)).$data;
fwrite($conn, $data, strlen($data));

while ($conn > 0) 
{
	$receive = false;
	$size = @fread($conn, 4);
	if(connection_aborted())
	{
		echo "disconnected";
		break;
	}
	if(strlen($size)>=4)
	  $size = unpack('V1Size',$size);
	if(isset($size) && isset($size["Size"]))
	{
	  if ($size["Size"] > 4096)
		$packet = "\x00\x00\x00\x00\x00\x00\x00\x00".fread($conn, 4096);
	  elseif ($size["Size"]>0)
		$packet = fread($conn, $size["Size"]);
	}
	if(strlen($packet)>=4)
	{
	  $toret = unpack("V1ID/V1Reponse/a*S1/a*S2",$packet);
	  if(isset($toret["S1"]) && ($toret["Reponse"]==0)) $receive = $toret["S1"];
	  echo $receive."<br>";
	  $line = explode("\n",$receive);
	  // CHAT COMMAND HOOK
	  if(preg_match('/\W[C][H][A][T]\W\W\W(.*?)\W\W\W\/(.*?)\W$/',$receive,$output))
	  {
		  chat_cmd($output[1],$output[2]);
	  }
	  // CONNECTION HOOK
	  elseif(preg_match('/^[A-Za-z]{4}\W[A-Za-z]{9}\W\W(.*?)\W\W([0-9]{17})\W$/',$receive,$output))
	  {
		  onuserconnect($output[1],$output[2]);
	  }
	  elseif(strpos($receive,"User Disconnected:")===0)
	  {
		  onuserdisconnect(substr($receive,19));  
	  }
	  elseif(isset($line) && isset($line[0]) && (strpos($line[0],"hostname:")===0))
	  {
		parsestatus($line);
	  }
	  elseif($receive == "OnDestroy")
	  {
		print("Server Shutdown");
		break;  
	  }
	}
	if(!$started)
	{
		sendcmd("status");
		$started = true;
	}
	$packet = false;
	$line = array();
	//usleep(50000);
}
function parsestatus($data)
{
	global $playerlist,$admins;
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
	$playerlistarray = array_slice($data,6,count($data)-8);
	$playerlist = array();
	foreach($playerlistarray as $pid => $playerdata)
	{
		$tempname = false;
		$playerlist[substr($playerdata,0,17)] = array(
		"name" => substr($playerdata,19,36),
		"ping"=>substr($playerdata,56,4),
		"connected"=>substr($playerdata,62,11),
		"ip"=>substr($playerdata,74,15));
		$tempname = $playerlist[substr($playerdata,0,17)]["name"];
		$tempname = substr($tempname,0,strrpos($tempname,'"'));
		$playerlist[substr($playerdata,0,17)]["name"] = $tempname;
		if(isset($admins[substr($playerdata,0,17)]) && $admins[substr($playerdata,0,17)])
			$playerlist[substr($playerdata,0,17)]["isadmin"] = true;
		else
			$playerlist[substr($playerdata,0,17)]["isadmin"] = false;
			
	}
}
function onuserconnect($name,$steamid)
{
	global $admins,$playerlist;
	$playerlist[$steamid] = array();
	if($admins[$steamid])
		$playerlist[$steamid]["isadmin"]=true;
	else
		$playerlist[$steamid]["isadmin"]=false;
	sendcmd("say \"". $name. " has joined the game\"");	
	$playerlist[$steamid]["name"] = $name;
}
function onuserdisconnect($name)
{
	global $playerlist;
	$found = findplayer($name);
	if(!is_array($found))
		sendcmd("status");	
	else
	{	
		$playerlist[$found["steam"]] = NULL;
	}
	sendcmd("say \"". $name. " has left the game\"");	
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
function isadmin($name)
{
	global $playerlist,$admins;
	$found = 0;
	$foundsteam = false;
	print_r($playerlist);
	print($name);
	foreach($playerlist as $steam => $info)
	{
		print_r($info["name"]);
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
				print("found exact with case sensibility");
			}
			elseif((strtolower($info["name"])==strtolower($name)) and (!$foundexactcase))
			{
				$found++;
				$foundexactnocase = true;
				$foundsteam = $steam;
				$foundname = $info["name"];
				print("found exact with NO case sensibility");
			}
			elseif((strpos($info["name"],$name)!==false) and (!$foundexactcase) and (!$foundexactnocase))
			{
				$found++;
				$foundsteam = $steam;
				$foundname = $info["name"];
				print("found partial name");
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
?>
