<?php

use React\Http\Browser;
use function Safe\file_get_contents;
use function Safe\json_decode;
use Zanzara\Config;
use Zanzara\Context;
use Zanzara\Zanzara;

require __DIR__.'/vendor/autoload.php';

$config = new Config();

$bot = new Zanzara((string) $_ENV['BOT_TOKEN'], $config);

$bot->getContainer()->set('browser', new Browser($bot->getLoop()));

$bot->onCommand('start', function (Context $ctx) {
    $ctx->sendMessage('Chào bạn');
});

$bot->onCommand('myid', function (Context $ctx) {
    $id = $ctx->getMessage()?->getFrom()->getId();
    $groupId = $ctx->getMessage()?->getChat()->getId();
    $ctx->sendMessage('Your ID: '.$id);
    $ctx->sendMessage('Group ID: '.$groupId);
});

$bot->onCommand('game', [CatchPhrase::class, 'game']);
$bot->onText('ans: {text}', [CatchPhrase::class, 'answer']);
$bot->onCommand('hentai', [CommandHandler::class, 'hentai']);
$bot->onCommand('admin', [CommandHandler::class, 'admin']);
$bot->onCommand('cat', [CommandHandler::class, 'cat']);
$bot->onText('tiktok {link}', [CommandHandler::class, 'tiktok']);
$bot->onText('facebook {link}', [CommandHandler::class, 'facebook']);


/** @var array<int,array<string>> */
$batChu = json_decode(file_get_contents(__DIR__.'/result.json'), true);
$bot->getContainer()->set('batchu', $batChu);

$bot->run();
