<?php

use function Amp\asyncCall;
use function Symfony\Component\String\u;
use Zanzara\Context;

/**
 * @psalm-type QuestionType = array{image: string, result: string}
 */
class CatchPhrase
{
    /** @var list<QuestionType> */
    private static array $data = [];

    /** @var array<string, QuestionType> */
    private static array $current = [];

    public const IMAGE_URL = 'https://e.gamevui.vn/web/2014/10/batchu/assets/pics/';

    /** @param list<QuestionType> $data */
    public static function importData(array $data): void
    {
        self::$data = $data;
    }

    /**
     * Chuyển câu hỏi.
     */
    public static function changeQuestion(Context $ctx): void
    {
        $question = array_shift(self::$data);

        if (null === $question) {
            $ctx->sendMessage('Đã hết câu hỏi.');

            return;
        }

        self::$current[getGroupId($ctx)] = $question;

        $ctx->sendPhoto(self::IMAGE_URL.$question['image']);
    }

    public function game(Context $ctx): void
    {
        self::changeQuestion($ctx);
    }

    public function answer(Context $ctx, string $text): void
    {
        asyncCall(function () use ($ctx, $text) {
            $current = self::$current[getGroupId($ctx)];
            $opt = ['reply_to_message_id' => $ctx->getMessage()?->getMessageId()];

            if ('skip' === $text) {
                yield $ctx->sendMessage('Đáp án: '.$current['result']);

                self::changeQuestion($ctx);
            } elseif (u($text)->ascii()->lower() == u($current['result'])->ascii()->lower()) {
                $name = $ctx->getMessage()?->getFrom()->getFirstName();

                if (5214954937 === $ctx->getMessage()?->getFrom()->getId()) {
                    $message = 'Chồng yêu đã trả lời chính xác! 😍';
                } else {
                    $message = sprintf('Bạn %s đã trả lời chính xác! 😊', $name);
                }

                yield $ctx->sendMessage($message, $opt);

                self::changeQuestion($ctx);
            } else {
                $ctx->sendMessage('Không chính xác...', $opt);
            }
        });
    }
}
