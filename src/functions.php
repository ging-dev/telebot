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
    $username = $user->getUsername();
    if ($username) {
        return '@'.$username;
    }

    $name = $user->getFirstName();
    $lastName = $user->getLastName();
    if ($lastName) {
        $name .= ' '.$lastName;
    }

    $mention = sprintf('[%s](tg://user?id=%d)', $name, $user->getId());

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
