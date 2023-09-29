<?php

namespace App\Clients;

class FirebaseClient
{
    private $access_token;
    private $endpoint;

    public function __construct($access_token)
    {
        $this->access_token = $access_token;
        $this->endpoint = "https://fcm.googleapis.com/fcm/send";
    }

    public function sendByRegistrationId($firebase_token, $title, $messages, $data)
    {
        $firebase_token = $firebase_token[0]->firebase_android;
        $headers = array(
            'Authorization: key=' . $this->access_token,
            'Content-Type: application/json'
        );

        $fields = array(
            'registration_ids' => array($firebase_token),
            'notification' => array(
                'body' => $messages,
                'title' => $title,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ),
            'data' => $data,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
    }

    public function sendByTopic($topic, $title, $messages, $data)
    {
        $topicPayload = '/topics/' . $topic;
        $headers = array(
            'Authorization: key=' . $this->access_token,
            'Content-Type: application/json'
        );

        $fields = array(
            'to' => $topicPayload,
            'notification' => array(
                'body' => $messages,
                'title' => $title,
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ),
            'data' => $data,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);
    }
}
