<?php

$host = '54.94.157.185:8088';
$username = 'click2call';
$password = '3DYCzfyXsDgEkR';
$extension = 7773;
$phone = preg_replace('/[^0-9]/', null, $_GET['phone']);

$headers = [
  sprintf('Authorization: Basic %s', base64_encode("$username:$password")),
  'Content-Type: application/json; charset=utf-8',
];

$data = [
    'callerId' => sprintf('"%s" <%s>', 'Click2call', $phone),
    'endpoint' => sprintf('Local/%s@from-internal', $phone),
    'extension' => (string) $extension,
    'context' => 'from-internal',
    'priority' => 1,
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, sprintf('http://%s/ari/channels', $host));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

curl_exec($ch);
curl_close($ch);
