<?php

namespace App\Conversations;

use BotMan\BotMan\BotMan;
use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\BotManFactory;
use App\Conversations\ExampleConversation;
use Illuminate\Support\Facades\Log;
use App\Bitrix24\Bitrix24API;
use App\Bitrix24\Bitrix24APIException;
use App\Http\Controllers\BitrixConnect;
use BotMan\Drivers\Telegram\Extensions\Keyboard;
use BotMan\Drivers\Telegram\Extensions\KeyboardButton;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class ExampleConversation extends Conversation
{
    protected string $subject;

    protected string $body;

    protected object $botman;

    public function __construct(string $subject = '', string $body = '', object $botman  = null)
    {
      $this->subject = $subject;
      $this->body = $body;
      $this->botman = $botman = app('botman');
      
    }
    public function subject_ticket()
    {
       
        $this->ask('Введите (кратко 5-7 слов) тему обращения.', function(Answer $answer) {
            // Save result
            $this->subject = $answer->getText();
            $user = $this->botman->getUser();
            $id = $user->getId();
            DB::table( 'ticket_subject' )->insert( [
                'from_id' => $id,
                'subject' => $this->subject
           ] );
            $this->body_ticket();
        });
    }

    public function body_ticket()
    {
      
        $this->ask('Опишите вашу проблему детально.', function(Answer $answer) {
            // Save result
            $user = $this->botman->getUser();
            $id = $user->getId();
            $this->body = $answer->getText();
           DB::table( 'ticket_body' )->insert( [
                'from_id' => $id,
                'body' => $this->body
            ] );
            $this->send_media();
        });
    }
    public function send_media(){
        $question = Question::create('Хотите добавить фотографии?')
        ->addButtons([
            Button::create('Да хочу')->value('да'),
            Button::create('Завершить отправку')->value('нет'),
        ]);

    $this->ask($question, function (Answer $answer) {
        // Detect if button was clicked:
        if ($answer->isInteractiveMessageReply()) {
           if($answer->getValue() === "да")
           {        
            $this->askPhoto();
           } 
           else{
         $this->complete_ticket();    
           }
          
        }
    });
    
    }
    
    public function askPhoto()
    {
        $keyboard = Keyboard::create()->type( Keyboard::TYPE_KEYBOARD )
        ->oneTimeKeyboard(true)
        ->resizeKeyboard(true)
        ->addRow( 
           KeyboardButton::create("Завершить отправку")->callbackData('first_inline')
        )
        ->toArray();
        $this->say("Добавляйте фотографии", $keyboard);
             
    }
    public function complete_ticket(){
        $this->say('Обращение отправлено');
        $webhookURL = 'вебхук';
        $bx24 = new Bitrix24API($webhookURL);
       $bitrix = new BitrixConnect();
       $keep_bitrix = $bitrix->get_user_bx();
      $send_bx = $bx24->addTask( [
            'TITLE'           => $this->subject, // Название задачи
            'RESPONSIBLE_ID'  => 25, // ID ответственного пользователя
            'DESCRIPTION' => $this->body,
            'START_DATE_PLAN' => date( 'Y/m/d H:i:s'), // Плановая дата начала.
            'CREATED_BY' =>$keep_bitrix[ 0 ][ 0 ][ 'ID' ],
            'GROUP_ID' => $keep_bitrix[ 0 ][ 0 ][ 'UF_USR_1638863249307' ],

        ]);
        if(!empty($keep_bitrix[ 0 ][ 0 ][ 'UF_USR_1640168223479' ])){
        $client = new Client();
        $res = $client->request('POST', 'https://api.telegram.org/bot123355555/sendMessage', [
            'form_params' => [
                'chat_id' => $keep_bitrix[ 0 ][ 0 ]['UF_USR_1640168223479'],
                'text' => '❗️❗️❗️Открыто новое обращение' . "\n" . $bitrix->find_subject()[2] . "\n" . "\n" . $bitrix->find_body()[2],
                'parse_mode' => 'HTML',
            ]
        ]);
       }
        $user = $this->botman->getUser();
        $id = $user->getId();
        DB::table('ticket_subject')->where('from_id', '=', $id)->delete();
        DB::table('ticket_body')->where('from_id', '=', $id)->delete();
    }
    public function run()
    {
        // This will be called immediately
        $this->subject_ticket();
    }
}
