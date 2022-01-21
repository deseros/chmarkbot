<?php

  

namespace App\Http\Controllers;

  

use BotMan\BotMan\BotMan;

use Illuminate\Http\Request;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\BotManFactory;
use App\Conversations\ExampleConversation;
use BotMan\BotMan\Cache\CodeIgniterCache;  
use Illuminate\Support\Facades\Log;
use BotMan\BotMan\Messages\Attachments\Image;
use App\Bitrix24\Bitrix24API;
use App\Bitrix24\Bitrix24APIException;
use Illuminate\Support\Facades\DB;

class BitrixConnect extends Controller

{   
    protected string $webhookURL;
    protected object $bx24;
    protected object $botman;

    //protected object $telegram_data;
    public function __construct(string $webhookURL = 'вебхук', object $bx24 = null, object $botman = null)
    {
      $this->webhookURL = $webhookURL;
      $this->bx24 = $bx24 = new Bitrix24API($webhookURL);
      $this->botman = $botman = app('botman');
      
    }
    public function get_user_bx()
    {
      $user = $this->botman->getUser();
      $id_user = $user->getId();  
      $generator = $this->bx24->getUsers(
        ['UF_USR_1638863226250' => $id_user,
        $order = 'ASC',
        $sort = 'NAME'
        ]);
      $user_arr = [];
      foreach ($generator as $users) {
        $user_arr[] = $users;
      }
      return $user_arr;
    }
    public function find_media_ticket() {
      $user = $this->botman->getUser();
      $id = $user->getId();
      $media_box = [];
      $find_media = collect( DB::table( 'bitrix_media' )->where( 'from_id', '=', $id )->get() );
  
      foreach ( $find_media as $media_item ) {
          $media_box[] = 'n' . $media_item->bitrix_id_file;
      }
      return $media_box;
    }
    public function find_subject(){
      $user = $this->botman->getUser();
      $id = $user->getId();
      $find_ticket_subject = collect( DB::table( 'ticket_subject' )->where( 'from_id', '=', $id)->get() )->last();
      $user_last_subject = [];
      if ( isset( $find_ticket_subject) ) {
          foreach ( $find_ticket_subject as $last_sub ) {
            $user_last_subject[] = $last_sub;
          }
      }
      return $user_last_subject;
    }
    public function find_body(){
      $user = $this->botman->getUser();
      $id = $user->getId();
      $find_ticket_body = collect( DB::table( 'ticket_body' )->where( 'from_id', '=', $id)->get() )->last();
      $user_last_body = [];
      if ( isset( $find_ticket_body) ) {
          foreach ( $find_ticket_body as $last_body ) {
            $user_last_body[] = $last_body;
          }
      }
      return $user_last_body;
    }
}