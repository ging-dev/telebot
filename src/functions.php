<?php

use React\Http\Browser;
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
