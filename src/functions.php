<?php

use GingDev\Xvideos\Detail;
use Goutte\Client;
use React\Http\Browser;
use TikTok\Driver\FacebookDriver;
use TikTok\Driver\SnaptikDriver;
use TikTok\TikTokDownloader;
use Zanzara\Context;
use Zanzara\Telegram\Type\User;

function getGroupId(Context $ctx): string
{
    return (string) $ctx->getMessage()?->getChat()->getId();
}

function browser(Context $ctx): Browser
{
    return $ctx->getContainer()->get('browser');
}

function getTagName(User $user): string
{
    $name = $user->getFirstName();
    $lastName = $user->getLastName();
    if ($lastName) {
        $name .= ' '.$lastName;
    }

    $mention = sprintf('<a href="tg://user?id=%d">%s</a>', $user->getId(), htmlspecialchars($name));

    return $mention;
}

function getUserId(Context $ctx): string
{
    return (string) $ctx->getMessage()?->getFrom()->getId();
}

function getChatId(Context $ctx): string
{
    return (string) $ctx->getMessage()?->getChat()->getId();
}

/**
 * @return string|bool
 */
function get_video(string $url, bool $facebook = false)
{
    $tiktok = new TikTokDownloader($facebook ? new FacebookDriver() : new SnaptikDriver());

    try {
        return $tiktok->getVideo($url);
    } catch (\InvalidArgumentException) {
    }

    return false;
}

/**
 * @return array{high: string, low: string, title: string, thumb: string}
 */
function get_xvideos_last_month()
{
    $client = new Client();

    $oneMonthAgo = new \DateTime('-1 month');

    $crawler = $client->request(
        'GET',
        sprintf('https://www.xv-videos1.com/best/%s/%d',
            rand(0, 111), $oneMonthAgo->format('Y-m')
        )
    );

    /** @var list<string> */
    $list = $crawler->filterXPath('//div[contains(@id, "video_")]')->evaluate('substring-after(@id, "_")');

    shuffle($list);

    return (new Detail($client))->get(array_pop($list));
}
