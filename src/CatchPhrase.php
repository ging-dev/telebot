<?php

use EventSauce\ObjectHydrator\ObjectMapperUsingReflection;
use function Amp\asyncCall;
use React\Promise\PromiseInterface;
use function Symfony\Component\String\u;
use Zanzara\Context;

class CatchPhrase
{
    private static ListOfQuestions $questions;

    /** @var array<string, Question> */
    private static array $current = [];

    public const IMAGE_URL = 'https://e.gamevui.vn/web/2014/10/batchu/assets/pics/';

    /** @param list<array<string,string>> $data */
    public static function initialize(array $data): void
    {
        $questions = (new ObjectMapperUsingReflection())->hydrateObjects(Question::class, $data);

        self::$questions = new ListOfQuestions($questions->toArray());
    }

    /**
     * Chuyển câu hỏi.
     */
    public static function changeQuestion(Context $ctx): PromiseInterface
    {
        $question = static::$questions->pop();

        if (null === $question) {
            return $ctx->sendMessage('Đã hết câu hỏi.');
        }

        self::$current[getGroupId($ctx)] = $question;

        return $ctx->sendPhoto(self::IMAGE_URL.$question->image);
    }

    public function game(Context $ctx): void
    {
        self::changeQuestion($ctx)->then(function () use ($ctx) {
            $ctx->sendMessage('Bắt đầu Game!');
        });
    }

    public function answer(Context $ctx, string $text): void
    {
        asyncCall(function () use ($ctx, $text) {
            $current = self::$current[getGroupId($ctx)];
            $opt = ['reply_to_message_id' => $ctx->getMessage()?->getMessageId()];

            if ('skip' === $text) {
                yield $ctx->sendMessage('Đáp án: '.$current->result);

                self::changeQuestion($ctx);
            } elseif (u($text)->ascii()->lower() == u($current->result)->ascii()->lower()) {
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
