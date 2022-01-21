<?php

  

namespace App\Http\Controllers;

  

use BotMan\BotMan\BotMan;

use Illuminate\Http\Request;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\Drivers\Telegram\Extensions\Keyboard;
use BotMan\Drivers\Telegram\Extensions\KeyboardButton;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\BotManFactory;
use App\Conversations\ExampleConversation;
use BotMan\BotMan\Cache\CodeIgniterCache;  
use Illuminate\Support\Facades\Log;
use BotMan\BotMan\Messages\Attachments\Image;
use App\Http\Controllers\BitrixConnect;
use App\Bitrix24\Bitrix24API;
use App\Bitrix24\Bitrix24APIException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class BotManController extends Controller

{   
    public function handle()

    {
        $botman = app('botman');
        
        $botman->hears('/start', function($bot) {
            $bitrix = new BitrixConnect();
           $check_bitrix = $bitrix->get_user_bx()[0];
           if(empty($check_bitrix)){
            $bot->reply('Бот запущен, однако функционал вам пока не доступен. Наш специалист уже получил извещение о том, что вы запустили бота. Если вы являетесь клиентом, вас известят о возможности работы с ботом в вашем чате поддержки.');
           }
           else{
            $keyboard = Keyboard::create()->type( Keyboard::TYPE_KEYBOARD )
            ->oneTimeKeyboard(true)
            ->resizeKeyboard(true)
            ->addRow( 
               KeyboardButton::create("Отправить обращение")->callbackData('first_inline')
            )
            ->toArray();
            $bot->reply('Бот запущен, нажмите на кнопку «Отправить обращение» на клавиатуре бота', $keyboard);
            $user = $bot->getUser();
            $id = $user->getId();
            $info = $user->getInfo();
            $text = '❗️❗️❗️Внимание' . "\n" . 'Выполнен новый запуск бота пользователем' . "\n" 
            .'<b>Его имя </b>'. '  ' . $info['user']['first_name'] . "\n"
            .'<b>Его фамилия </b>'. '  ' . $info['user']['last_name'] . "\n"
            .'<b>Его имя пользователя</b>'. '  ' .'@' . $info['user']['username'] . "\n"
            .'<b>Его ID</b>' . '  ' . $id;
            $client = new Client();
            $res = $client->request('POST', 'https://api.telegram.org/bot12556555/sendMessage', 
            [
                'form_params' => [
                    'chat_id' => '-1001777521223',
                    'text' => $text,
                    'parse_mode' => 'HTML',
                ]
            ]);
        }
        });
        
        $botman->hears('Отправить обращение', function($bot) {
            $bitrix = new BitrixConnect();
            $check_bitrix = $bitrix->get_user_bx()[0];
            if(empty($check_bitrix)){
                $bot->reply('Бот запущен, однако функционал вам пока не доступен. Наш специалист уже получил извещение о том, что вы запустили бота. Если вы являетесь клиентом, вас известят о возможности работы с ботом в вашем чате поддержки.');
            }
            else{
            $user = $bot->getUser();
            $id = $user->getId();  
            $check_mediadb = DB::table('bitrix_media')->where('from_id', '=', $id)->exists();
            if($check_mediadb){
                DB::table('bitrix_media')->where('from_id', '=', $id)->delete();
            }
            $bot->startConversation(new ExampleConversation); 
            }
        });
       
        $botman->hears('Завершить отправку', function($bot) {
            $bitrix = new BitrixConnect();
            $check_bitrix = $bitrix->get_user_bx()[0];
            if(empty($check_bitrix)){
                $bot->reply('Бот запущен, однако функционал вам пока не доступен. Наш специалист уже получил извещение о том, что вы запустили бота. Если вы являетесь клиентом, вас известят о возможности работы с ботом в вашем чате поддержки.');
            }
            else{
                $keyboard = Keyboard::create()->type( Keyboard::TYPE_KEYBOARD )
            ->oneTimeKeyboard(true)
            ->resizeKeyboard(true)
            ->addRow( 
               KeyboardButton::create("Отправить обращение")->callbackData('first_inline')
            )
            ->toArray();
            $bot->reply('Обращение отправлено', $keyboard);
            $webhookURL = '';
            $bx24 = new Bitrix24API($webhookURL);
           $bitrix = new BitrixConnect();
           $keep_bitrix = $bitrix->get_user_bx();
          $send_bx = $bx24->addTask( [
                'TITLE'           => $bitrix->find_subject()[2], // Название задачи
                'RESPONSIBLE_ID'  => 25, // ID ответственного пользователя
                'DESCRIPTION' => $bitrix->find_body()[2],
                'START_DATE_PLAN' => date( 'Y/m/d H:i:s'), // Плановая дата начала.
                'CREATED_BY' =>$keep_bitrix[ 0 ][ 0 ][ 'ID' ],
                'GROUP_ID' => $keep_bitrix[ 0 ][ 0 ][ 'UF_USR_1638863249307' ],
    
            ] );
            if ( !empty($bitrix->find_media_ticket()) ) {
                $tesa = $bx24->updTask( $send_bx['task']['id'],
                [ 'UF_TASK_WEBDAV_FILES'  => $bitrix->find_media_ticket() ] );
            }
            $user = $bot->getUser();
            $id = $user->getId();
            $client = new Client();
            if(!empty($keep_bitrix[ 0 ][ 0 ][ 'UF_USR_1640168223479' ])){
            $res = $client->request('POST', 'https://api.telegram.org/bot1233555555/sendMessage', [
                'form_params' => [
                    'chat_id' => $keep_bitrix[ 0 ][ 0 ]['UF_USR_1640168223479'],
                    'text' => '❗️❗️❗️Открыто новое обращение' . "\n" . $bitrix->find_subject()[2] . "\n" . "\n" . $bitrix->find_body()[2],
                    'parse_mode' => 'HTML',
                ]
            ]);
        }
            DB::table('ticket_subject')->where('from_id', '=', $id)->delete();
            DB::table('ticket_body')->where('from_id', '=', $id)->delete();
            DB::table('bitrix_media')->where('from_id', '=', $id)->delete();
        }
        });
        $botman->receivesImages(function($bot, $images) {
            $bitrix = new BitrixConnect();
            $check_bitrix = $bitrix->get_user_bx()[0];
            if(empty($check_bitrix)){
                $bot->reply('Бот запущен, однако функционал вам пока не доступен. Наш специалист уже получил извещение о том, что вы запустили бота. Если вы являетесь клиентом, вас известят о возможности работы с ботом в вашем чате поддержки.');
            }
            else{
            $user = $bot->getUser();
            $id = $user->getId();
            $webhookURL = 'вебхук';
            $bx24 = new Bitrix24API($webhookURL);
            foreach ($images as $image) {
                $filename = Str::random( 10 ). '.jpg';
                $url = $image->getUrl(); // The direct url
                $start_picture = $bx24->uploadfileDiskFolder(
                    $filderId = 557,
                    $rawFile = file_get_contents( $url ),
                    [ 'NAME' => $filename ],
                    $isBase64FileData = false
                );
                DB::table( 'bitrix_media' )->insert( [
                    'from_id' => $id,
                    'bitrix_id_file' => $start_picture[ 'ID' ],
                    'bitrix_file_name' => $start_picture[ 'NAME' ]
                ] );
            }    
        }            
        }); 
    
        $botman->listen();
    }
   
}