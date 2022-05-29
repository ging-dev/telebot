<?php

use Zanzara\Context;

function getGroupId(Context $ctx): string
{
    return (string) $ctx->getMessage()?->getChat()->getId();
}
