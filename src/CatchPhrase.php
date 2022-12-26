<?php

use function Amp\asyncCall;

use EventSauce\ObjectHydrator\ObjectMapperUsingReflection;
use React\Promise\PromiseInterface;

use function Symfony\Component\String\u;

use Zanzara\Config;
use Zanzara\Context;

final class CatchPhrase
{
    private static ListOfQuestions $questions;

    /** @var array<string,Question> */
    private static array $currentQuestionOf = [];

    /** @var array<string,int> */
    public static array $scoreOf = [];

    /** @param list<array<string,string>> $data */
    public static function initialize(array $data): void
    {
        $questions = (new ObjectMapperUsingReflection())->hydrateObjects(Question::class, $data);

        static::$questions = new ListOfQuestions($questions->toArray());
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

        static::$currentQuestionOf[groupId($ctx)] = $question;

        return $ctx->sendPhoto($question->getImage());
    }

    public function game(Context $ctx): void
    {
        static::changeQuestion($ctx)->then(function () use ($ctx) {
            $ctx->sendMessage('Bắt đầu Game!');
        });
    }

    public function answer(Context $ctx, string $text): void
    {
        asyncCall(function () use ($ctx, $text) {
            $current = static::$currentQuestionOf[groupId($ctx)];
            $opt = [
                'reply_to_message_id' => $ctx->getMessage()?->getMessageId(),
                'parse_mode' => Config::PARSE_MODE_HTML,
            ];

            if ('skip' === $text) {
                yield $ctx->sendMessage('Đáp án: '.$current->getResult());

                static::changeQuestion($ctx);
            } elseif (u($text)->ascii()->lower() == u($current->getResult())->ascii()->lower()) {
                $user = $ctx->getMessage()?->getFrom();
                $userId = userId($ctx);

                if (!array_key_exists($userId, static::$scoreOf)) {
                    static::$scoreOf[$userId] = 0;
                }

                ++static::$scoreOf[$userId];

                $message = sprintf('Bạn %s đã trả lời chính xác! 😊', tagName($user));

                yield $ctx->sendMessage($message, $opt);

                static::changeQuestion($ctx);
            } else {
                yield $ctx->sendMessage('Không chính xác...', $opt);
                $ctx->sendMessage('Gợi ý: Đáp án bắt đầu bằng: '.substr($current->getResult(), 0, 1));
            }
        });
    }
}
