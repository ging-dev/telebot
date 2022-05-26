<?php

use Zanzara\Context;

/**
 * Lấy thông tin câu hỏi hiện tại
 *
 * @psalm-suppress MixedInferredReturnType
 * @psalm-suppress MixedReturnStatement
 *
 * @return array{
 *  image: string,
 *  result: string,
 * }
 */
function getCurrentQuestion(Context $ctx): array
{
    return $ctx->getContainer()->get('current');
}

/**
 * Lấy toàn bộ dữ liệu Bắt Chữ
 *
 * @psalm-suppress MixedInferredReturnType
 * @psalm-suppress MixedReturnStatement
 *
 * @return array<int,array{
 *  image: string,
 *  result: string,
 * }>
 */
function getDataBatChu(Context $ctx): array
{
    return $ctx->getContainer()->get('batchu');
}

/**
 * Đổi câu hỏi hiện tại
 *
 * @psalm-suppress UndefinedInterfaceMethod
 */
function changeQuestion(Context $ctx): void
{
    $data = getDataBatChu($ctx);

    $ctx->getContainer()->set('current', $data[array_rand($data)]);

    $ctx->sendPhoto('https://e.gamevui.vn/web/2014/10/batchu/assets/pics/'.getCurrentQuestion($ctx)['image']);
}
