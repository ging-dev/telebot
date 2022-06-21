<?php

use Psr\Http\Message\ResponseInterface;
use TikTok\Driver\SnaptikDriver;
use TikTok\TikTokDownloader;
use Zanzara\Context;
use Zanzara\Telegram\Type\ChatMember;
use Zanzara\Config;
use TikTok\Driver\FacebookDriver;

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
        $ctx->deleteMessage($ctx->getMessage()?->getChat()->getId(), $ctx->getMessage()?->getMessageId());

        $tiktok = new TikTokDownloader(new FacebookDriver());
        try {
            $video = $tiktok->getVideo($link);
            $ctx->sendChatAction('upload_video')->then(
                fn () => $ctx->sendVideo($video, ['caption' => 'Video from Facebook'])
            );
        } catch (\InvalidArgumentException $e) {
            $ctx->sendMessage('Không thành công!');
        }
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

    public function tiktok(Context $ctx, string $link): void
    {
        $ctx->deleteMessage($ctx->getMessage()?->getChat()->getId(), $ctx->getMessage()?->getMessageId());

        $tiktok = new TikTokDownloader(new SnaptikDriver());
        try {
            $video = $tiktok->getVideo($link);
            $ctx->sendChatAction('upload_video')->then(
                fn () => $ctx->sendVideo($video, ['caption' => 'Video from TikTok'])
            );
        } catch (\InvalidArgumentException $e) {
            $ctx->sendMessage('Không thành công!');
        }
    }

    public function cat(Context $ctx): void
    {
        browser($ctx)->get('https://api.thecatapi.com/v1/images/search?limit=1&size=full')
            ->then(function (ResponseInterface $response): string {
                /*
                 * @psalm-suppress MixedArrayAccess
                 */
                return json_decode((string) $response->getBody())[0]->url;
            })
            ->then(function (string $url) use ($ctx) {
                $ctx->sendChatAction('upload_photo')->then(
                    fn () => $ctx->sendPhoto($url)
                );
            });
    }
}
