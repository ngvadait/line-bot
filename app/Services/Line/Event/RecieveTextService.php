<?php
namespace App\Services\Line\Event;
use LINE\LINEBot;
use DB;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
class RecieveTextService
{
    /**
     * @var LineBot
     */
    private $bot;
    /**
     * Follow constructor.
     * @param LineBot $bot
     */
    public function __construct(LineBot $bot)
    {
        $this->bot = $bot;
    }
    /**
     * 登録
     * @param TextMessage $event
     * @return string
     */
    public function execute(TextMessage $event)
    {
        $text = $event->getText();
        if ($text === 'hi') {
            return 'What is your name?';
        } else if ($text === 'hello') {
            return 'How old are you?';
        } else {
            return 'What are you doing?';
        }
    }
}
