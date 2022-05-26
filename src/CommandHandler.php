<?php

use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;
use Zanzara\Context;

class CommandHandler
{
    public function hentai(Context $ctx): void
    {
        /** @var Browser */
        $client = $ctx->getContainer()->get('browser');
        $client->get('http://api.nekos.fun:8080/api/cum')->then(function (ResponseInterface $response) use ($ctx) {
            /** @var string */
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
    }

    public function facebook(Context $ctx, string $link): void
    {
        $ctx->deleteMessage($ctx->getMessage()?->getChat()->getId(), $ctx->getMessage()?->getMessageId());
        /** @var Browser */
        $client = $ctx->getContainer()->get('browser');
        $client->get('http://api.quangsangblog.com/api/facebook/video?url='.urlencode($link).'&apikey=Eris_m6FbAFJJQGwR2VZsiQuphnR5U3vkT5I')->then(function (ResponseInterface $response) use ($ctx) {
            /** @var string|false */
            $video = json_decode((string) $response->getBody())->links->HD ?? false;

            if ($video === false) {
                $ctx->sendMessage('Đã xảy ra lỗi...');

                return;
            }

            $ctx->sendChatAction('upload_video')->then(
                fn () => $ctx->sendVideo($video)
            );
        });
    }
}
