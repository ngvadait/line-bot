<?php

namespace App\Http\Controllers\Api;

use LINE\LINEBot;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Line\Event\FollowService;
use App\Services\Line\Event\RecieveTextService;
use App\Services\Line\Event\RecieveLocationService;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;

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
        /** @var LINEBot $bot */
        $bot = app('line-bot');
        $userId = env('LINE_USER_ID');

        /////////////////// Text
        $message = $request->message;
        $outputText = new TextMessageBuilder($message);

        /////////////////// Location
        $outputText = new LocationMessageBuilder("Eiffel Tower", "Champ de Mars, 5 Avenue Anatole France, 75007 Paris, France", 48.858328, 2.294750);

        /////////////////// Button
        $actions = array (
            // general message action
            New MessageTemplateActionBuilder("button 1", "text 1"),
            // URL type action
            New UriTemplateActionBuilder("Google", "http://www.google.com"),
            // The following two are interactive actions
            New PostbackTemplateActionBuilder("next page", "page=3"),
            New PostbackTemplateActionBuilder("Previous", "page=1")
        );
        $img_url = "https://cdn.shopify.com/s/files/1/0379/7669/products/sampleset2_1024x1024.JPG?v=1458740363";
        $button = new ButtonTemplateBuilder("button text", "description", $img_url, $actions);
        $outputText = new TemplateMessageBuilder("this message to use the phone to look to the Oh", $button);

        /////////////////// Carousel
        $columns = array();
		$img_url = "https://cdn.shopify.com/s/files/1/0379/7669/products/sampleset2_1024x1024.JPG?v=1458740363";
		for($i=0;$i<5;$i++) {
			$actions = array(
				new PostbackTemplateActionBuilder("Add to Card","action=carousel&button=".$i),
				new UriTemplateActionBuilder("View","http://www.google.com")
			);

            $column = new CarouselColumnTemplateBuilder("Title", "description", $img_url , $actions);
			$columns[] = $column;
		}
		$carousel = new CarouselTemplateBuilder($columns);
        $outputText = new TemplateMessageBuilder("Carousel Demo", $carousel);

        /////////////////// Image
        $img_url = "https://cdn.shopify.com/s/files/1/0379/7669/products/sampleset2_1024x1024.JPG?v=1458740363";
        $outputText = new ImageMessageBuilder($img_url, $img_url);

        /////////////////// Confirm
        $actions = array (
            new PostbackTemplateActionBuilder("yes", "ans=y"),
            new PostbackTemplateActionBuilder("no", "ans=N")
        );
        $button = new ConfirmTemplateBuilder("problem", $actions);
        $outputText = new TemplateMessageBuilder("this message to use the phone to look to the Oh", $button);

        /////////////////// Video
        $url = 'https://scontent.fhan2-3.fna.fbcdn.net/v/t66.18014-6/10000000_255155445158077_8402596495820534722_n.mp4?_nc_cat=107&efg=eyJ2ZW5jb2RlX3RhZyI6Im9lcF9oZCJ9&_nc_ht=scontent.fhan2-3.fna&oh=b0c8dfb38a88dc80e9db56f87af21c42&oe=5C7A03AC';
        $image = 'https://cdn2.stylecraze.com/wp-content/uploads/2013/10/2.-Anushka-Sharma_1.jpg';

        $outputText = new VideoMessageBuilder($url, $image);

        $bot->pushMessage($userId, $outputText);
        // send batch
        // $bot->multicast([env('LINE_USER_ID_1'), env('LINE_USER_ID_2'), env('LINE_USER_ID_3')], $textMessage);
    }
}
