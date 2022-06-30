<?php

use function Amp\asyncCall;
use function Amp\Parallel\Worker\enqueueCallable;
use Psr\Http\Message\ResponseInterface;
use Zanzara\Config;
use Zanzara\Context;
use Zanzara\Telegram\Type\ChatMember;

class CommandHandler
{
    public function hentai(Context $ctx): void
    {
        browser($ctx)->get('http://api.nekos.fun:8080/api/cum')
            ->then(function (ResponseInterface $response): string {
                return json_decode((string) $response->getBody())->image;
            })
            ->then(function (string $url) use ($ctx) {
                if ('gif' === \pathinfo($url, PATHINFO_EXTENSION)) {
                    $ctx->sendChatAction('upload_video')->then(
                        fn () => $ctx->sendAnimation($url)
                    );
                } else {
                    $ctx->sendChatAction('upload_photo')->then(
                        fn () => $ctx->sendPhoto($url)
                    );
                }
            });
    }

    public function facebook(Context $ctx, string $link): void
    {
        asyncCall(function () use ($ctx, $link) {
            yield $ctx->deleteMessage($ctx->getMessage()?->getChat()->getId(), $ctx->getMessage()?->getMessageId());

            /** @var string|false */
            $video = yield enqueueCallable('get_video', $link, true);

            if (!$video) {
                $ctx->sendMessage('Không thành công!');

                return;
            }

            $ctx->sendChatAction('upload_video')->then(
                fn () => $ctx->sendVideo($video, ['caption' => 'Video from Facebook'])
            );
        });
    }

    public function tiktok(Context $ctx, string $link): void
    {
        asyncCall(function () use ($ctx, $link) {
            yield $ctx->deleteMessage($ctx->getMessage()?->getChat()->getId(), $ctx->getMessage()?->getMessageId());

            /** @var string|false */
            $video = yield enqueueCallable('get_video', $link);

            if (!$video) {
                $ctx->sendMessage('Không thành công!');

                return;
            }

            $ctx->sendChatAction('upload_video')->then(
                fn () => $ctx->sendVideo($video, ['caption' => 'Video from TikTok'])
            );
        });
    }

    public function admin(Context $ctx): void
    {
        $ctx->getChatAdministrators($ctx->getMessage()?->getChat()->getId())->then(
            /** @param list<ChatMember> $list */
            function (array $list) use ($ctx) {
                /** @var list<string> */
                $admin = [];
                foreach ($list as $member) {
                    $user = $member->getUser();
                    if ($user->isBot()) {
                        continue;
                    }
                    $admin[] = getTagName($user);
                }

                $ctx->sendMessage('Danh sách chiến thần: '.implode(', ', $admin), ['parse_mode' => Config::PARSE_MODE_HTML]);
            }
        );
    }

    public function cat(Context $ctx): void
    {
        browser($ctx)->get('https://api.thecatapi.com/v1/images/search?limit=1&size=full')
            ->then(function (ResponseInterface $response): string {
                return json_decode((string) $response->getBody())[0]->url;
            })
            ->then(function (string $url) use ($ctx) {
                $ctx->sendChatAction('upload_photo')->then(
                    fn () => $ctx->sendPhoto($url)
                );
            });
    }
}
