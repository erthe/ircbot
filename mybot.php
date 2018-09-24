 <?php
$dirname = dirname(__FILE__);
require_once ("$dirname/mybot.class.php");
require_once ("$dirname/SmartIRC/defines.php");

define('IRC_AUTORETRYMAX', 5);
define('IRC_TIMER_MINUTE', 60*1000);
define('IRC_CONFIG', CUR_DIR.DS.'ircbot.ini');

exit(main());

declare(ticks = 1);

// シグナルハンドラ関数
// SIGUSR1 をカレントのプロセス ID に送信します
// posix_kill(posix_getpid(), SIGUSR1);
function sig_handler($signo)
{
     switch ($signo) {
         case SIGTERM:
             // シャットダウンの処理
             fwrite(STDERR, "SIGTERM を受け取りました...\n");
             break;
         case SIGHUP:
             // 再起動の処理
             fwrite(STDERR, "SIGHUP を受け取りました...\n");
             break;
         case SIGUSR1:
             fwrite(STDERR, "SIGUSR1 を受け取りました...\n");
             break;
         default:
             // それ以外のシグナルの処理
             fwrite(STDERR, "$signo を受け取りました...\n");
             break;
     }
     exit($signo);
}

function main(){
	mb_internal_encoding('UTF-8');
	mb_http_input('pass');
	mb_http_output('pass');

	// シグナルハンドラを設定します
	pcntl_signal(SIGTERM, "sig_handler");
	pcntl_signal(SIGHUP, "sig_handler");
	pcntl_signal(SIGUSR1, "sig_handler");

	$bot = new MyBot();
	$irc = new Net_SmartIRC();

	$channel_list = $bot->channel_list(CHANNEL_LIST_CONF);

	$irc->setDebug(SMARTIRC_DEBUG_ALL);
	$irc->setUseSockets(1);
	//$irc->setAutoReconnect(true);
	$irc->setChannelSyncing(true);

	$irc->registerTimehandler(60000, $bot, 'timer');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^trash$', $bot, 'trash');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^sakko$', $bot, 'sakko');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^road$', $bot, 'road');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^sot$', $bot, 'sot');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^razgriz$', $bot, 'razgriz');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^sokko$', $bot, 'sokko');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^sat$', $bot, 'sat');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^trazgriz$', $bot, 'trazgriz');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^satrash$', $bot, 'satrash');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^satrashr$', $bot, 'satrashr');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^satrazgriz$', $bot, 'satrazgriz');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^trashtrash$', $bot, 'trashtrash');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^sakko-i$', $bot, 'sakkoi');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^sots$', $bot, 'sots');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^hdjp\s([\[\]-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#\-ぁ-んァ-ヶー一-龠]+)$', $bot, 'player_info');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^hdata\s([\[\]-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#\-ぁ-んァ-ヶー一-龠]+)\s(vs|VS|and|AND)\s([\[\]-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#\-ぁ-んァ-ヶー一-龠]+)$', $bot, 'vs_player');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^game$', $bot, 'game_info');	
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^stream$', $bot, 'stream');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^civ$', $bot, 'civ');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^civrr$', $bot, 'civrr');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^help$', $bot, 'help');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^https?:\/\/([-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)', $bot, 'url');
	$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'naruto');
	$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'auth_ch');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^naruto$', $bot, 'naruto_said');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^auth$', $bot, 'auth_info');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^callauth$', $bot, 'call_auth');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '^!quit$', $bot, 'quit');

	// ログ
	$irc->registerActionhandler(SMARTIRC_TYPE_JOIN, '.*', $bot, 'getlog');
	$irc->registerActionhandler(SMARTIRC_TYPE_QUIT, '.*', $bot, 'getlog');
	$irc->registerActionhandler(SMARTIRC_TYPE_PART, '.*', $bot, 'getlog');
	$irc->registerActionhandler(SMARTIRC_TYPE_CHANNEL, '.*', $bot, 'getlog');
	$irc->registerActionhandler(SMARTIRC_TYPE_NOTICE, '.*', $bot, 'getlog');

	$irc->connect('aochd.jp', 6667);
	$irc->login('aochdjp', 'aochdjp');
	//$irc->join($channel_list);
	
	foreach($channel_list as $channel){
		$irc->join(array($channel));
	}

	$irc->listen();
	$irc->disconnect();

return 0;
}
