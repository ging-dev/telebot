<?php

use GingDev\Xvideos\Detail;
use Goutte\Client;
use React\Http\Browser;
use TikTok\Driver\FacebookDriver;
use TikTok\Driver\SnaptikDriver;
use TikTok\TikTokDownloader;
use Zanzara\Context;
use Zanzara\Telegram\Type\User;

function groupId(Context $ctx): string
{
    return (string) $ctx->getMessage()?->getChat()->getId();
}

function browser(Context $ctx): Browser
{
    return $ctx->getContainer()->get('browser');
}

function tagName(?User $user): string
{
    if (null === $user) {
        return 'Unknown';
    }

    $name = $user->getFirstName();
    $lastName = $user->getLastName();
    if ($lastName) {
        $name .= ' '.$lastName;
    }

    $mention = sprintf('<a href="tg://user?id=%d">%s</a>', $user->getId(), htmlspecialchars($name));

    return $mention;
}

function userId(Context $ctx): string
{
    return (string) $ctx->getMessage()?->getFrom()->getId();
}

function chatId(Context $ctx): string
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
