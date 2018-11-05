<?php

namespace App\Http\Controllers\Api;

use LINE\LINEBot;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Line\Event\FollowService;
use App\Services\Line\Event\RecieveTextService;
use App\Services\Line\Event\RecieveLocationService;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class LineBotController extends Controller
{


    public function callback(Request $request)
    {
        /** @var LINEBot $bot */
        $bot = app('line-bot');
        $signature = $_SERVER['HTTP_' . LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
        if (!LINEBot\SignatureValidator::validateSignature($request->getContent(), env('LINE_CHANNEL_SECRET'), $signature)) {
            logger()->info('recieved from difference line-server');
            abort(400);
        }
        $events = $bot->parseEventRequest($request->getContent(), $signature);
        /** @var LINEBot\Event\BaseEvent $event */
        foreach ($events as $event) {
            $reply_token = $event->getReplyToken();
            $reply_message = 'Not Support.[' . get_class($event) . '][' . $event->getType() . ']';
            $userID = $event->getUserId() ?? 333;
            switch (true) {
                case $event instanceof LINEBot\Event\MessageEvent\TextMessage:
                    $service = new RecieveTextService($bot);
                    $reply_message = $service->execute($event);
                    break;
                case $event instanceof LINEBot\Event\PostbackEvent:
                    break;
                case $event instanceof LINEBot\Event\UnfollowEvent:
                    break;
                default:
                    $body = $event->getEventBody();
                    logger()->warning('Unknown event. [' . get_class($event) . ']', compact('body'));
            }
            $bot->replyText($reply_token, $reply_message . 'Id cua ban la - ' . $userID);
        }
    }

    public function getForm()
    {
        return view('form.line');
    }

    public function testSendLine(Request $request)
    {
        $message = $request->message;
        $textMessage = new TextMessageBuilder($message);
        $userId = env('LINE_USER_ID');
        /** @var LINEBot $bot */
        $bot = app('line-bot');
        $bot->pushMessage($userId, $textMessage);
    }
}
