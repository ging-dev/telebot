<?php

use GingTeam\React;
use React\EventLoop\Loop;
use React\Http\Browser;
use Workerman\Worker;
use Zanzara\Config;
use Zanzara\Context;
use Zanzara\Zanzara;

use function Safe\file_get_contents;
use function Symfony\Component\String\u;
use function React\Async\await;

require __DIR__.'/vendor/autoload.php';

$config = new Config();
$config->setLoop(Loop::get());

$bot = new Zanzara((string) $_ENV['BOT_TOKEN'], $config);

$bot->getContainer()->set('browser', new Browser($bot->getLoop()));

$bot->onCommand('game', function (Context $ctx) {
    changeQuestion($ctx);
});

$bot->onText('ans: {text}', function (Context $ctx, string $text) {
    $current = getCurrentQuestion($ctx);
    $opt = ['reply_to_message_id' => $ctx->getMessage()?->getMessageId()];

    if ($text === 'skip') {
        await($ctx->sendMessage('Đáp án: '.$current['result']));

        changeQuestion($ctx);
    } else if (u($text)->ascii()->lower() == u($current['result'])->ascii()->lower()) {
        $name = $ctx->getMessage()?->getFrom()->getFirstName();

        await($ctx->sendMessage(sprintf('Bạn %s đã trả lời chính xác', $name), $opt));

        changeQuestion($ctx);
    } else {
        $ctx->sendMessage('Không chính xác...', $opt);
    }
});

$bot->onCommand('start', function (Context $ctx) {
    $ctx->sendMessage('Chào bạn');
});

$bot->onCommand('myid', function (Context $ctx) {
    $id = $ctx->getMessage()?->getFrom()->getId();
    $ctx->sendMessage('Your ID: '.$id);
});

$bot->onCommand('hentai', [CommandHandler::class, 'hentai']);
$bot->onText('facebook {link}', [CommandHandler::class, 'facebook']);

$worker = new Worker();
$worker->name = 'Telebot';
$worker::$eventLoopClass = React::class;

$worker->onWorkerStart = function () use ($bot): void {
    /** @var array<int,array<string>> */
    $batChu = json_decode(file_get_contents(__DIR__.'/result.json'), true);
    $bot->getContainer()->set('batchu', $batChu);

    $bot->run();
};

Worker::runAll();
