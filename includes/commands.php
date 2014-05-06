<?php

function chat_cmd($name,$text)
{
	global $playerlist,$event;
	$chr = explode(" ",$text);
	$apos = false;
	$args = array();
	$i = 0;
	$debug = findplayer($name);
	if(isset($debug[2]))
	{
		var_dump(findplayer($debug[2]));
	}
	foreach($chr as $cid => $val)
	{
		if($cid == 0) $args[0] = $val;
		else
		{
			if($apos)
				$args[$i] = $args[$i]." ".$val;
			else
			{
				$i++;
				$args[$i] = $val;
			}
			if((strpos($val,'\"') === 0) && (!$apos)) 
			{
				$apos = true;
				$args[$i] = substr($val,2);
			}
			if( strlen($val)>1 && ((strpos($val,'\"',strlen($val)-2)) || ($val == '\"')) && ($apos))
			{
				$apos = false;
				$args[$i] = substr($args[$i],0,strlen($args[$i])-2);
			}
		}		
	}
	switch($args[0])
	{
		case "day":
			if(isallowed($name,GetVar("day")))
				sendcmd("env.time 12");
		break;
		case "night":
			if(isallowed($name,GetVar("night")))
				sendcmd("env.time 24");
		break;
		case "time":
			if(isallowed($name,GetVar("time")))
			{
				if(isset($args[1]) && is_numeric($args[1]))
					sendcmd("env.time ".$args[1]);
			}
		break;
		case "kick":
		case "k":
			if(isallowed($name,GetVar("kick")))
			{
				$reason = "";
				if(isset($args[1]))
				{
					$found = findplayer($args[1]);
					if(!is_array($found))
					{
						sendcmd("say \"".$found."\"");
					}
					else
					{
						sendcmd("kick \"".$found["steam"]."\"");
						sendcmd("notice.popupall \"".$found["name"]." was kicked");
					}
				}
			}
		break;
		case "ban":
		case "b":
			if(isallowed($name,GetVar("ban")))
			{
				$reason = "";
				if(isset($args[1]))
				{
					$found = findplayer($args[1]);
					if(!is_array($found))
					{
						sendcmd("say \"".$found."\"");
					}
					else
					{
						if(isset($args[2])) $reason = "(".implode(" ",array_slice($args,2)).")";
						//sendcmd("kick \"".$steam."\"");
						sendcmd("banid \"".$found["steam"]."\" \"".$reason."\"");
						sendcmd("kick \"".$found["steam"]."\"");
						sendcmd("notice.popupall \"".$found["name"]." was banned ".$reason."\"");
					}
				}
			}
		break;
		case "who":
			if(isallowed($name,GetVar("who")))
			{
				if(count($playerlist)>1)
					sendcmd("say \"There is currently ".count($playerlist)." players\"");
				else
					sendcmd("say \"You are the only player online at the moment\"");
			}
		
		break;
		case "tp":
			if(isallowed($name,GetVar("tp")))
			{
				$reason = "";
				$found2 = false;
				if(isset($args[1]))
				{
					$found = findplayer($args[1]);
					if(isset($args[2]))
					{
						$found2 = findplayer($args[2]);
					}
					if(!is_array($found))
					{
						sendcmd("say \"".$found."\"");
						return;
					}
					if($found2!==false && !is_array($found2))
					{
						sendcmd("say \"".$found."\"");
						return;
					}
					if(!$found2)
						sendcmd("teleport.toplayer \"".$name."\" \"".$found["steam"]."\"");
					else
						sendcmd("teleport.toplayer \"".$found["steam"]."\" \"".$found2["steam"]."\"");					
				}
			}
		break;
		case "bring":
			if(isallowed($name,GetVar("bring")))
			{
				$reason = "";
				if(isset($args[1]))
				{
					$found = findplayer($args[1]);
					if(!is_array($found))
					{
						sendcmd("say \"".$found."\"");
						return;
					}
					sendcmd("teleport.toplayer \"".$found["steam"]."\" \"".$name."\"");					
				}
			}
		break;
		case "slay":
		case "kill":
		if(isallowed($name,GetVar("slay")))
		{
			$reason = "";
			if(isset($args[1]))
			{
				$found = findplayer($args[1]);
				if(!is_array($found))
				{
					sendcmd("say \"".$found."\"");
					return;
				}
				if((GetVar("slay.x") != 0) && (GetVar("slay.y") != 0) && (GetVar("slay.z") != 0))
				{
					sendcmd("teleport.topos \"".$found["steam"]."\" \"".GetVar("slay.x")."\" \"".GetVar("slay.y")."\" \"".GetVar("slay.z")."\"");
					sendcmd("say \"".$found["name"]." was [color #FF3333]slayed[color #FFFFFF] by the admin\"");
				}	
				else sendcmd("say \"But no slay ground was found\"");			
			}
		}
		break;
		case "jail":
			if(isallowed($name,GetVar("jail")))
			{
				$reason = "";
				if(isset($args[1]))
				{
					$found = findplayer($args[1]);
					if(!is_array($found))
					{
						sendcmd("say \"".$found."\"");
						return;
					}
					$searchjailed = GetJailed($found["steam"]);
					print($searchjailed);
					if($searchjailed === 0)
					{
						sendcmd("say \"".$found['name']." has been [color #FF3333]arrested[color #FFFFFF] by an admin\"");
						file_put_contents("jailed.ini",$found['steam']." = ".$found['name']."\n",FILE_APPEND);
						if((GetVar("jail.x") != 0) && (GetVar("jail.y") != 0) && (GetVar("jail.z") != 0))
						{
							sendcmd("teleport.topos \"".$found["steam"]."\" \"".GetVar("jail.x")."\" \"".GetVar("jail.y")."\" \"".GetVar("jail.z")."\"");
						}
						else sendcmd("say \"But no jail was found\"");
					}
					else
					{
						$jailtext = file_get_contents("jailed.ini");
						$jail_ = explode("\n",$jailtext);
						$newjail = array();
						foreach($jail_ as $jl => $j)
						{
							if(strpos($j,$found["steam"])===0) ;
							else
								$newjail[] = $j;
						}
						file_put_contents("jailed.ini",implode("\n",$newjail));
						if((GetVar("free.x") != 0) && (GetVar("free.y") != 0) && (GetVar("free.z") != 0))
						{
							sendcmd("teleport.topos \"".$found["steam"]."\" \"".GetVar("free.x")."\" \"".GetVar("free.y")."\" \"".GetVar("free.z")."\"");
						}	
						sendcmd("say \"".$found['name']." has been [color #00FF33]freed[color #FFFFFF] from jail\"");
					}
				}
			}
		break;
		case "scream":
			if(isallowed($name,GetVar("scream")))
			{
				$text = implode(" ",array_slice($args,1));
				sendcmd("say \"[color #6293E8]".$text."\"");
				sendcmd("say \"[color #EE5151]".$text."\"");
				sendcmd("say \"[color #B93CA8]".$text."\"");
				sendcmd("say \"[color #3CB946]".$text."\"");
				sendcmd("say \"".$text."\"");
				sendcmd("notice.popupall \"".$text."\"");
			}
		break;
		case "give":
		case "reward":
			if(isallowed($name,GetVar("give")))
			{
				if(!isset($args[1]) or !isset($args[2]))
					return;
				if(!isset($args[3]))
				{
					$found = array("name"=>$name);	
					$item = $args[1];
					$num = $args[2];
				}
				else
				{
					$found = findplayer($args[1]);
					if(!is_array($found))
					{
						sendcmd("say \"".$found."\"");
						return;
					}
					$item = $args[2];
					$num = $args[3];
				}
				sendcmd("inv.giveplayer \"".$found["name"]."\" \"".$item."\" \"".$num."\"");
			}
		break;
		case "pvp":
			if(isallowed($name,GetVar("pvp")))
			{
				if(isset($args[1]))
				{
					if(($args[1] == "on") or ($args[1] == "true"))
					{
						sendcmd("server.pvp true");
						sendcmd("say \"PVP has been activated\"");
					}
					elseif(($args[1] == "off") or ($args[1] == "false"))
					{
						sendcmd("server.pvp false");
						sendcmd("say \"PVP has been deactivated\"");
					}
					else
					{
						sendcmd("say \"Usage: /pvp on/off\"");
					}
				}
			}
		break;
		case "a":
		case "admintest":
			if(isadmin($name))
				sendcmd("say \"".$name." you are an admin\"");
			else
			{
				sendcmd("say \"".$name." you are an not an admin, using admin commands will result in a warning\"");
			}
		break;
		case "help":
			sendcmd("say \"This server is not modded, no commands will work here\"");
		break;
		case "event":
			if(isadmin($name))
			{
				if(!isset($args[1])) return;
				if($args[1]=="start")
				{
					if(isset($event["opened"]) && $event["opened"])
					{	
						$event["opened"] = false;
						$event["started"] = true;
						sendcmd("say \"[color #488FAD][EVENT] ".$event["name"]." - ".$event["description"]." [color #FFFFFF]Is now starting, you can not join any more.\"");
						if($event["killonstart"])
						{
							sendcmd("say \"[color #488FAD][EVENT] ".$event["name"]."[color #FFFFFF]You will all die, respawn at camp to start playing!\"");
							foreach($event["players"] as $pi => $player)
								sendcmd("teleport.topos \"".$player."\" \"".GetVar("slay.x")."\" \"".GetVar("slay.y")."\" \"".GetVar("slay.z")."\"");
						}
						sendcmd("say \"[color #488FAD][EVENT] ".$event["name"]." Goal: [color #FFFFFF]".$event["goal"]."\"");
						
					}
				}
				elseif(($args[1]=="stop") || ($args[1]=="end"))
				{
					if(isset($event["started"]) && $event["started"])
					{	
						$event["opened"] = false;
						$event["started"] = false;
						sendcmd("say \"[color #488FAD][EVENT] ".$event["name"]." [color #FFFFFF]Is now over.\"");
						$event = array();
					}
					elseif(isset($event["opened"]) && $event["opened"])
					{
						$event["opened"] = false;
						$event["started"] = false;
						sendcmd("say \"[color #488FAD][EVENT] ".$event["name"]." [color #FFFFFF]Was Canceled.\"");
						foreach($event["players"] as $pi => $player)
						{
							sendcmd("teleport.topos \"".$player."\" \"".GetVar("free.x")."\" \"".GetVar("free.y")."\" \"".GetVar("free.z")."\"");
						}
						$event = array();
					}
				}
				else
				{
					if(!isset($args[2]))
					{
						 sendcmd("say \"Please set a max number of players\"");
						 return;
					}
					if(!is_numeric($args[2]))
					{
						sendcmd("say \"/event EVENT MAXPLAYERS\"");
						return;
					}
					$data = file_get_contents("events/".$args[1].".txt");
					if($data !== false)
					{
						$event = array();
						$event["started"] = false;
						$event["opened"] = true;
						$event["name"] = "Unknown";
						$event["description"] = "Unknown";
						$event["goal"] = "Unknown";
						$event["spawns"] = array();
						$event["players"] = array();
						$event["killonstart"] = false;
						$event["lastspawn"] = -1;
						$event["maxplayers"] = $args[2];
						$d_ = explode("\n",$data);
						foreach($d_ as $l => $line)
						{
							$l_ = explode("=",$line);
							switch($l_[0])
							{
								case "name":
								case "description":
								case "goal":
								case "killonstart":
									$event[$l_[0]] = $l_[1];
								break;	
								case "spawn":
									$event["spawns"][] = $l_[1];
								break;
							}
						}
						sendcmd("say \"[color #488FAD][EVENT][color #FFFFFF] ".$event["name"]." is about to start, say /join to join the event, empty your inventory\"");
						sendcmd("say \"Please do not start fighting until the admin says that you can\"");
						sendcmd("notice.popupall \"/join to join the event\"");
					}
				}
			}	
		break;
		case "j":
		case "join":
			if(isset($event["opened"]) && $event["opened"])
			{
				if(!in_array($name,$event["players"]))
				{
					if(count($event["players"])>= $event["maxplayers"])
					{
						sendcmd("say \"Event is full, you may no longer join\"");
						sendcmd("chat.enabled false");
						$timers[] = array("time"=>time()+5,"function"=>"sendcmd","isarray"=>false,"repeat"=>false,"args"=>"chat.enabled true");
					}
					else
					{
						$event["players"][] = $name;
						if($event["lastspawn"]>=(count($event["spawns"])-1))
							$event["lastspawn"] = 0;
						else $event["lastspawn"]++;
						sendcmd("teleport.topos \"".$name."\" ".$event["spawns"][$event["lastspawn"]]);
					}
				}
			}
		break;
		case "leave":
		case "l":
		if(in_array($name,$event["players"]))
		{
			if((GetVar("slay.x") != 0) && (GetVar("slay.y") != 0) && (GetVar("slay.z") != 0))
			{
				foreach($event["players"] as $ei => $playername)
				{
					if($name == $playername) unset($event["players"][$ei]);	
				}
				sendcmd("teleport.topos \"".$player."\" \"".GetVar("free.x")."\" \"".GetVar("free.y")."\" \"".GetVar("free.z")."\"");
			}	
		}
		break;
		default:
			//sendcmd("say \"Command not found\"");
		break;
		
	}
}


?>
