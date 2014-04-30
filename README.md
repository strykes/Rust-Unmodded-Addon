Rust-Unmodded-Addon 0.1
===================

Php Addon for Unmodded Rust servers

You can launch it with any platform that uses php, it doesn't need to be on the same host as the server.

Edit config.php with the required informations
for admins you need to use there steamcommunityid, it has 17 numbers in it.
just go to your profile page and it is the link showed in the bar, ex for me it's:
http://steamcommunity.com/profiles/76561197961481118
so my community id is 76561197961481118

for LINUX users:
Just use the linux_addon, to start/restart/stop the addon automatically
you need screen and php installed on the machine.

for WINDOWS users:
You need to install PHP5
Then edit: win_addon.cmd with any text editor
replace the first part with the link to the php.exe file
and the second part with the full link where main.php is located
then double click on win_addon.cmd to start it.
The console will then show on your screen.
I haven't found a way to make it launch in background yet (if anybody has any ideas they are welcomed)


Current commands:
all player names can be partial

/help
/who => shows how many players are on
/tp PLAYER/STEAMCOMMUNITYID (to teleport to a player)
/bring PLAYER/STEAMCOMMUNITYID (to bring someone to you)
/kick PLAYER/STEAMCOMMUNITYID (to kick someone)
/ban PLAYER/STEAMCOMMUNITYID REASON (to ban and kick someone)
/day (changes time to day)
/night (changes time to night)
/time XX (sets the time to any time you want)
/scream TEXT (screams and spams the chat)
