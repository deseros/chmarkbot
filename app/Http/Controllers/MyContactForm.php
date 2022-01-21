<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Illuminate\Support\Facades\Log;

class MyContactForm extends Controller
{
    public function send(Request $request){
        $check_data = $this->validate($request, [
			'name_ticket' => 'required|max:255',
            'subject_ticket' => 'required|max:255',
			'email_ticket' => 'required|email',
			'text_ticket' => 'required'
		]);
        
        
        $result = Mail::send('emailsend', ['data' => $request], function ($message) use ($request) {
            $message->subject($request['subject_ticket']);
            $message->from($request['email_ticket'], $request['name_ticket']);         
            $message->to('deteksi1703@gmail.com');
             
        });
       return redirect()->back()->with('status', 'Обращение успешно отправлено');
    }
}
