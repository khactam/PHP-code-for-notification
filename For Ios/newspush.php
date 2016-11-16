<?php

// Put your device token here (without spaces):
$deviceToken = 'af713f9602b32f93b1f931f38d5f8ff4bf0982a0920eb1f7dbe4248692d87269';

// Put your private key's passphrase here:
$passphrase = 'Koodaus1';

$message = $argv[1];
$url = $argv[2];

if (!$message || !$url)
    exit('Example Usage: $php newspush.php \'Hahaha!\' \'https://superapp.fi\'' . "\n");

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem'); //the ck.pem was generated from the app id certificate for push notification using 
                                                                //openssl pkcs12 -in Certificate.p12 -out ck.pem -nodes -clcerts;
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
stream_context_set_option($ctx, 'ssl', 'verify_peer', false);//not sure for this but without it, the whole thing doesnt work
stream_context_set_option($ctx, 'ssl', 'cafile', 'entrust_2048_ca.cer'); //download entrust 2048 from https://www.entrust.com/get-support/ssl-certificate-support/root-certificate-downloads/
// Open a connection to the APNS server
$fp = stream_socket_client(
  'ssl://gateway.sandbox.push.apple.com:2195', $err,
  $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp)
  exit("Failed to connect: $err $errstr" . PHP_EOL);

echo 'Connected to APNS' . PHP_EOL;

// Create the payload body
$body['aps'] = array(
  'alert' => $message,
  'sound' => 'default',
  'link_url' => $url,
  'category' => 'NEWS_CATEGORY',
  );

// Encode the payload as JSON
$payload = json_encode($body);

// Build the binary notification
$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

if (!$result)
  echo 'Message not delivered' . PHP_EOL;
else
  echo 'Message successfully delivered' . PHP_EOL;

// Close the connection to the server
fclose($fp);
