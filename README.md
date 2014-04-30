Rust-Unmodded-Addon 0.1
===================

Php Addon for Unmodded Rust servers
 <br>
You can launch it with any platform that uses php, it doesn't need to be on the same host as the server. <br>
 <br>
Edit config.php with the required informations <br>
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
