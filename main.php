<?php
include("includes/commands.php");
include("includes/functions.php");
include("settings/config.php");
include("settings/restricted_names.php");
include("settings/automessages.php");

try
{
	$conn = @fsockopen($config["server_ip"], $config["server_queryport"], $errno, $errstr, 2);
}
catch (Exception $err) { }

$started = false;
$serverinfo = array();
$playerlist = array();
$timers = array();
$parsingstatus = -1;
$cutline = false;
$lastmsg = 0;
$checkmsg = false;
$nummsg = 0;
$playerlistarray = array();
$spam = array();
$data = pack("VV",1,03).$config["server_rcon"].chr(0).''.chr(0);
$data = pack("V",strlen($data)).$data;
$mysql = false;
$dbb = false;
fwrite($conn, $data, strlen($data));
sendcmd("say \"[color #9999FF]Addon was successfully started\"");	

if(GetVar("MYSQL") == 1)
	$mysql = true;
if(GetVar("automessages")==1)
	$timers[] = array("time"=>time()+GetVar("automessages_timers"),"repeat_time"=>GetVar("automessages_timers"),"function"=>"sendautomessage","isarray"=>false,"repeat"=>true,"args"=>"");

if($mysql)
	$dbb = DBB($config["mysql_host"],$config["mysql_login"],$config["mysql_pass"],$config["mysql_database"],$config["mysql_table"]);

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
	  if(strlen($receive)>5)
		  echo $receive."\n";
	  $line = explode("\n",$receive);
	  // CHAT LOGS
	  // CHAT COMMAND HOOK
	  if(preg_match('/\W[C][H][A][T]\W\W\W(.*?)\W\W\W(.*?)\W$/',$receive,$output))
	  {
		  if(GetVar("LOGCHAT")==1)
		  {
		 	 file_put_contents($config["logs_folder"]."/CHAT-".date("d-m-y",time()).".log",date("H:i:s",time()).":".substr($receive,7)."\n",FILE_APPEND);
		 	 if($mysql)
			 	put_in_mysql($dbb,"CHAT",$output[1],$output[2]);
		  }
		  {
			  if(($lastmsg == time()) && !$checkmsg)
			  {
				 $nummsg = 0;
				 $checkmsg = time();
			  }
			  if(time() - $checkmsg < 10)
				  $nummsg++;
			  else
				$checkmsg = false;
			  if($nummsg > 10)
			  {
				  if(GetVar("antiglobalspam"))
				  {
					  $nummsg = 0;
					  $checkmsg = false;
					  sendcmd("say \"Spam detected, please calm down\"");
					  sendcmd("chat.enabled false");
					  $timers[] = array("time"=>time()+5,"function"=>"sendcmd","isarray"=>false,"repeat"=>false,"args"=>"chat.enabled true");
				  }
			  }
			  $lastmsg = time();
		  }		
		  if(preg_match('/\W[C][H][A][T]\W\W\W(.*?)\W\W\W\/(.*?)\W$/',$receive,$output))
		  {
			  chat_cmd($output[1],$output[2]);
		  }  
	  }
	  // CONNECTION HOOK
	  elseif(preg_match('/^[A-Za-z]{4}\W[A-Za-z]{9}\W\W(.*?)\W\W([0-9]{17})\W$/',$receive,$output))
	  {
		  onuserconnect($output[1],$output[2]);
		  if(GetVar("LOGCONNECTIONS")==1)
		  {
			  file_put_contents($config["logs_folder"]."/CONNECTIONS-".date("d-m-y",time()).".log",date("H:i:s",time()).":CONNECTED:".$output[1].":".$output[2]."\n",FILE_APPEND);   
			  if($mysql)
			 	put_in_mysql($dbb,"CONNECT",$output[1],$output[2]);  
		  }
	  }
	  elseif(strpos($receive,"User Disconnected:")===0)
	  {
		onuserdisconnect(substr($receive,19)); 
		if(GetVar("LOGCONNECTIONS")==1)
		{
			file_put_contents($config["logs_folder"]."/CONNECTIONS-".date("d-m-y",time()).".log",date("H:i:s",time()).":DISCONNECTED:".substr($receive,19)."\n",FILE_APPEND);
			if($mysql)
			put_in_mysql($dbb,"DISCONNECT",substr($receive,19),"");  
		}
	  }
	  elseif(isset($line) && isset($line[0]) && (strpos($line[0],"hostname:")===0))
	  {
		  $parsingstatus = time();
		parsestatus(str_replace(array("�","�"),"",$line));
	  }
	  elseif($parsingstatus == time())
	  {
		parsestatus_continue(str_replace(array("�","�"),"",$line));  
	  }
	  elseif($receive == "OnDestroy")
	  {
		print("Server Shutdown");
		put_in_mysql($dbb,"SHUTDOWN","SERVER SHUTDOWN","");  
		break;  
	  }
	  elseif(preg_match("/^(.*?)\W[h][a][s]\W[s][u][i][c][i][d][e][d]$/",$receive,$output))
	  {
		  onusersuicide($output[1]);
	  }
	  elseif(($receive!="") && ($receive !="END") && ($mysql))
	  {
		  put_in_mysql($dbb,"CONSOLE","CONSOLE",$receive); 
	  }
	}
	if(!$started)
	{
		sendcmd("status");
		$started = true;
	}
	$packet = false;
	$line = array();
	
	foreach($timers as $tid => $intel)
	{
		if(time() >= $intel["time"])
		{
			//if(isset($playerlist[$intel["steam"]]))
			//{
				if($intel["isarray"])
					call_user_func_array($intel["function"],$intel["args"]);
				else
					call_user_func($intel["function"],$intel["args"]);
				if(!$intel["repeat"]) unset($timers[$tid]);
				else $timers[$tid]["time"] = time() + $intel["repeat_time"];
			//}
			//else unset($timers[$tid]);
		}
	}
	//flush;
	//ob_flush();
}

?>
