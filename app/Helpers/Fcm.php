<?php
namespace App\Helpers;

use App\Models\Notifications;

class Fcm{

    static public function sendNotification(Notifications $notification)
    {

        if($notification->user->fcm_token == null){
            return;
        }
        $token =  $notification->user->fcm_token;
        return Fcm::send([$token],$notification->title, $notification->body,(array) json_decode($notification->data));
    }

    static public function send(Array $tokens, $title, $body, Array $data)
    {
        $SERVER_API_KEY = 'AAAAClXVJog:APA91bEIa0MgChQCqcymqKnTnK6i2UrZw6XxS-ZrzZWQWnldy31IxpyB8-R9xs2pc9-P5rT1JSRGfStdPn57tsfXHpgYJ9TNxip2lWS08vTwFqsOthb8Bth7TIZEk6iOysJx4mhfm-QG';
        $notification = [
            "registration_ids" => $tokens,
            "notification" => [
                "title" => $title,
                "body" => $body,
            ],
            "data" => $data,
        ];
        $dataString = json_encode($notification);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);
        return response()->json($response);
    }
}
