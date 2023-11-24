<?php

namespace App\Traits;

trait MessageTrait
{
    //send message
    public function sendMessage(string $phoneNumber, string $message)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.africastalking.com/version1/messaging');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  "username=katznicho" . "&to=" . urlencode($phoneNumber) . "&message=" . urlencode($message));
        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $headers[] = 'Apikey: 5ac50be8e3216fa155f4871dcda7be052d3523a697b3e798af92b0b4cc8879ea';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            return 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return substr($result, 0, 4); // "1701" indicates success */

    }
}
