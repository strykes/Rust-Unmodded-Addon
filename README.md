Rust-Unmodded-Addon 0.3
===================

Php Addon for Unmodded Rust servers

You can launch it with any platform that uses php, it doesn't need to be on the same host as the server. <br>
 <br>
Edit config.php and settings.php with the required informations <br>
for admins you need to use there steamcommunityid, it has 17 numbers in it. <br>
just go to your profile page and it is the link showed in the bar, ex for me it's: <br>
http://steamcommunity.com/profiles/76561197961481118 <br>
so my community id is 76561197961481118 <br>
 <br>
for LINUX users: <br>
Just use the linux_addon, to start/restart/stop the addon automatically <br>
you need screen and php installed on the machine. <br>
 <br>
for WINDOWS users: <br>
You need to install PHP5 <br>
Then edit: win_addon.cmd with any text editor <br>
replace the first part with the link to the php.exe file <br>
and the second part with the full link where main.php is located <br>
then double click on win_addon.cmd to start it. <br>
The console will then show on your screen. <br>
I haven't found a way to make it launch in background yet (if anybody has any ideas they are welcomed) <br>
 <br>
 <br>
Current commands: <br>
all player names can be partial <br>
 <br>
/help <br>
/who => shows how many players are on <br>
/tp PLAYER/STEAMCOMMUNITYID (to teleport to a player) <br>
/bring PLAYER/STEAMCOMMUNITYID (to bring someone to you) <br>
/kick PLAYER/STEAMCOMMUNITYID (to kick someone) <br>
/ban PLAYER/STEAMCOMMUNITYID REASON (to ban and kick someone) <br>
/day (changes time to day) <br>
/night (changes time to night) <br>
/time XX (sets the time to any time you want) <br>
/scream TEXT (screams and spams the chat) <br>

NEW Version 0.2<br>
Commands:<br>
- /jail PLAYER/STEAMCOMMUNITYID (Put someone in jail, need to set in settings.php) <br>
- /slay PLAYER/STEAMCOMMUNITYID (Put teleport that person into the bottom of the sea, killing him, need to set in settings.php) <br>
- /give <optional: PLAYER/STEAMCOMMUNITYID> "Item" "Quantity" (Give yourself or someone some stuff) <br>
- /admintest (Check if you are an admin) <br>

Modules:<br>
- Broadcast connections (choose in settings to activate/deactivate the broadcast of connections/disconnections)<br>
- Restricted names (Choose what names or characters should be restricted, in settings/restricted_names.php)<br>
- Restricted dual names (Auto kick players who uses the same names, admins are immuned, it will kick the connected player if that player stole the admins name)<br>

Logger:<br>
- Log Chat (choose to log chat in a file)
- Log Connections (choose to log connections in a file)

NEW Version 0.2.1<br>
Modules:<br>
- Auto-messages (Automatic messages every X seconds, see settings to set everything)

NEW Version 0.3<br>
Modules:<br>
- Events:
Create an event in events/ , name it to what ever you want as a .txt, ex: arena.txt
in it you must have this template:
name=NAME
description=DESCRIPTION
goal=GOAL
killonstart=true/false => this is made so you can empty the inventory of the people, you MUST use sleeping bags if you use this!
spawn=X Y Z
spawn=X Y Z
spawn=X Y Z
When people spawn it will be equilibrated, so if you have 9 players, there will be 3 in each (depending on how many spawns you made), this way you can easily make teams, or just put 10 spawns for 10 players if you want a free for all.

Commands:<br>
- /reward (same as give)
- /event NAME/start/stop
NAME = the name of the event you want to load. NAME.txt (in events/)
- /join (Join event)
- /leave (Leave event)

Fixes:<br>
- Fixed all the errors that i could find in the script.
