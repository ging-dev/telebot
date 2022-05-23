<?php

use GingTeam\React;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Loop;
use React\Http\Browser;
use Workerman\Worker;
use Zanzara\Config;
use Zanzara\Context;
use Zanzara\Zanzara;

require __DIR__.'/vendor/autoload.php';

$config = new Config();
$config->setLoop(Loop::get());

$bot = new Zanzara('5338016928:AAEEJyoCazgqeIWsdSTKEETRUR2-w9SZJ6M', $config);
$client = new Browser($bot->getLoop());

$bot->onCommand('start', function (Context $ctx) {
    $ctx->sendMessage('Hello');
});

$bot->onCommand('myid', function (Context $ctx) {
    $id = $ctx->getMessage()?->getFrom()->getId();

    $ctx->sendMessage('Your ID: '.$id);
});

$bot->onCommand('hentai', function (Context $ctx) use ($bot, $client) {
    $bot->getLoop()->addPeriodicTimer(10, function () use ($ctx, $client) {
        $client->get('http://api.nekos.fun:8080/api/cum')->then(function (ResponseInterface $response) use ($ctx) {
            $image = json_decode((string) $response->getBody())->image;
            if (\pathinfo($image, PATHINFO_EXTENSION) === 'gif') {
                $ctx->sendChatAction('upload_video')->then(
                    fn () => $ctx->sendAnimation($image)
                );
            } else {
                $ctx->sendChatAction('upload_photo')->then(
                    fn () => $ctx->sendPhoto($image)
                );
            }
        });
    });
});

$bot->onText('facebook {link}', function (Context $ctx, string $link) use ($client) {
    $client->get('http://api.quangsangblog.com/api/facebook/video?url='.urlencode($link).'&apikey=Eris_m6FbAFJJQGwR2VZsiQuphnR5U3vkT5I')
        ->then(function (ResponseInterface $response) use ($ctx) {
            $video = json_decode((string) $response->getBody())->links->HD ?? false;
            if ($video === false) {
                $ctx->sendMessage('Đã xảy ra lỗi...');

                return;
            }

            $ctx->sendChatAction('upload_video')->then(
                fn () => $ctx->sendVideo($video)
            );
        });
});

$bot->fallback(function (Context $ctx) {
    $ctx->sendMessage('Reply tin nhắn để bắt đầu');
});

$worker = new Worker();
$worker->name = 'Telebot';
$worker::$eventLoopClass = React::class;

$worker->onWorkerStart = function () use ($bot) {
    $bot->run();
};

Worker::runAll();
