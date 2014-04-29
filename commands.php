<?php

function chat_cmd($name,$text)
{
	global $playerlist;
	$chr = explode(" ",$text);
	$apos = false;
	$args = array();
	$i = 0;
	print_r($chr);
	foreach($chr as $cid => $val)
	{
		if($cid == 0) $args[0] = $val;
		else
		{
			if($apos)
			{
				
				$args[$i] = $args[$i]." ".$val;
				//print($args[$i]);
			}
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
			if( ((strpos($val,'\"',strlen($val)-2)) || ($val == '\"')) && ($apos))
			{
				$apos = false;
				$args[$i] = substr($args[$i],0,strlen($args[$i])-2);
			}
		}		
	}
	switch($args[0])
	{
		case "day":
			if(isadmin($name))
				sendcmd("env.time 12");
		break;
		case "night":
			if(isadmin($name))
				sendcmd("env.time 24");
		break;
		case "time":
			if(isadmin($name))
			{
				if(isset($args[1]) && is_numeric($args[1]))
					sendcmd("env.time ".$args[1]);
			}
		break;
		case "kick":
			if(isadmin($name))
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
			if(isadmin($name))
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
			if(count($playerlist)>1)
				sendcmd("say \"There is currently ".count($playerlist)." players\"");
			else
				sendcmd("say \"You are the only player online at the moment\"");
		
		break;
		case "tp":
			if(isadmin($name))
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
			if(isadmin($name))
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
		case "scream":
			if(isadmin($name))
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
		case "help":
			sendcmd("say \"This server is not modded, no commands will work here\"");
		
		break;
		default:
			sendcmd("say \"Command not found\"");
		break;
		
	}
}


?>
