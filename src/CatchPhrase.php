<?php

use function React\Async\await;
use function Symfony\Component\String\u;
use Zanzara\Context;

class CatchPhrase
{
    public const IMAGE_URL = 'https://e.gamevui.vn/web/2014/10/batchu/assets/pics/';

    /**
     * Lấy thông tin câu hỏi hiện tại.
     *
     * @return array{
     *  image: string,
     *  result: string,
     * }
     */
    public static function getCurrentQuestion(Context $ctx): array
    {
        return $ctx->getContainer()->get(getGroupId($ctx));
    }

    /**
     * @return list<array{
     *  image: string,
     *  result: string,
     * }>
     */
    public static function getDataBatChu(Context $ctx): array
    {
        return $ctx->getContainer()->get('batchu');
    }

    /**
     * Chuyển câu hỏi.
     *
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public static function changeQuestion(Context $ctx): void
    {
        $data = self::getDataBatChu($ctx);

        $ctx->getContainer()->set(getGroupId($ctx), $data[array_rand($data)]);

        $ctx->sendPhoto(self::IMAGE_URL.self::getCurrentQuestion($ctx)['image']);
    }

    public function game(Context $ctx): void
    {
        self::changeQuestion($ctx);
    }

    public function answer(Context $ctx, string $text): void
    {
        $current = self::getCurrentQuestion($ctx);
        $opt = ['reply_to_message_id' => $ctx->getMessage()?->getMessageId()];

        if ('skip' === $text) {
            await($ctx->sendMessage('Đáp án: '.$current['result']));

            self::changeQuestion($ctx);
        } elseif (u($text)->ascii()->lower() == u($current['result'])->ascii()->lower()) {
            $name = $ctx->getMessage()?->getFrom()->getFirstName();

            if (5214954937 === $ctx->getMessage()?->getFrom()->getId()) {
                $message = 'Chồng yêu đã trả lời chính xác! 😍';
            } else {
                $message = sprintf('Bạn %s đã trả lời chính xác! 😊', $name);
            }

            await($ctx->sendMessage($message, $opt));

            self::changeQuestion($ctx);
        } else {
            $ctx->sendMessage('Không chính xác...', $opt);
        }
    }
}
