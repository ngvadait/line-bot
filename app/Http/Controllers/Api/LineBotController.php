<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Line\Event\FollowService;
use App\Services\Line\Event\RecieveLocationService;
use App\Services\Line\Event\RecieveTextService;
use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use LINE\LINEBot\RichMenuBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuAreaBoundsBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuAreaBuilder;
use LINE\LINEBot\RichMenuBuilder\RichMenuSizeBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

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
            $bot->replyText($reply_token, 'Your ID: - ' . $userID);
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
        $url = 'https://goo.gl/3Sbsyd';
        $image = 'https://cdn2.stylecraze.com/wp-content/uploads/2013/10/2.-Anushka-Sharma_1.jpg';

        $outputText = new VideoMessageBuilder($url, $image);

        $bot->pushMessage($userId, $outputText);
        // send batch
        // $bot->multicast([env('LINE_USER_ID_1'), env('LINE_USER_ID_2'), env('LINE_USER_ID_3')], $textMessage);
    }

    public function getFormRichMenu()
    {
        return view('form.richmenu');
    }

    public function testRichMenu(Request $request)
    {
        $bot = app('line-bot');
        $size = $request->size;
        $width = 1250;
        $height= 843;
        $richMenuSize = new RichMenuSizeBuilder($width, $height);

        if ($size == 'full') {
            $richMenuSize = $richMenuSize->getFull();
            $boundsBuilder = new RichMenuAreaBoundsBuilder(0, 0, 2500, 1686);
        } else {
            $richMenuSize = $richMenuSize->getHalf();
            $boundsBuilder = new RichMenuAreaBoundsBuilder(0, 0, 2500, 843);
        }

        if ($request->has('checked')) {
            $checked = $request->checked;
            if ($checked == 'yes') {
                $selected = true;
            }
        } else {
            $selected = false;
        }

        $name = $request->name;
        $chatbar = $request->chatbar;

        $actionBuilder = new PostbackTemplateActionBuilder('Buy', 'action=buy&itemid=123');

        $areaBuilders = new RichMenuAreaBuilder($boundsBuilder, $actionBuilder);

        $richMenu = new RichMenuBuilder($richMenuSize, $selected, $name, $chatbar, $areaBuilders);

        $listIds = $bot->createRichMenu($richMenu);
        $richMenuId = array_values($listIds->getJSONDecodedBody())[0];
        echo $richMenuId;
        $imagePath = public_path() . '/img/peppa.jpg';
        $contentType = 'image/jpeg';

        $updateRichMenu = $bot->uploadRichMenuImage($richMenuId, $imagePath, $contentType);

        /**
         * Write new function LINEBot.php
         */

//        public function setDefaultRichMenu($richMenuId)
//        {
//            $url = $this->endpointBase . '/v2/bot/user/all/richmenu/' . urlencode($richMenuId);
//            return $this->httpClient->post($url, []);
//        }

        /**
         * Set default all user rich menus
         *
         * @param $richMenuId
         * @return Response
         */

        $setDefaultRichMenu = $bot->setDefaultRichMenu($richMenuId);

        dd($setDefaultRichMenu);
    }

    public function getListRichMenu()
    {
        $bot = app('line-bot');
        $listRichMenus = $bot->getRichMenuList();
        $listRichMenus = $listRichMenus->getJSONDecodedBody();

        /**
         * Delete rich menus
         */
        foreach ($listRichMenus as $listRichMenu) {
            foreach ($listRichMenu as $richMenu) {
                if ($richMenu) {
                    $richMenuId = $richMenu['richMenuId'];
                    $bot->deleteRichMenu($richMenuId);
                }
            }
        }
        dd('OK');
    }
}
