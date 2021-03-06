<?php
/**
 * Super-simple AWS CloudFront Invalidation Script
 * 
 * Steps:
 * 1. Set your AWS access_key
 * 2. Set your AWS secret_key
 * 3. Set your CloudFront Distribution ID
 * 4. Define the batch of paths to invalidate
 * 5. Run it on the command-line with: php cf-invalidate.php
 * 
 * The author disclaims copyright to this source code.
 *
 * Details on what's happening here are in the CloudFront docs:
 * http://docs.amazonwebservices.com/AmazonCloudFront/latest/DeveloperGuide/Invalidation.html
 * 
 */
$access_key = 'AKIAJ7IKIK52LBF3LXOA';
$secret_key = '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';
$distribution = 'E3TILXOTYMK6MS';
$epoch = date('U');

$xml = <<<EOD
<InvalidationBatch>
	<Path>/NutriiInvalidateCache.php</Path>
    <Path>/*</Path>
    <CallerReference>{$distribution}{$epoch}</CallerReference>
</InvalidationBatch>
EOD;


/**
 * You probably don't need to change anything below here.
 */
$len = strlen($xml);
$date = gmdate('D, d M Y G:i:s T');
$sig = base64_encode(
    hash_hmac('sha1', $date, $secret_key, true)
);

$msg = "POST /2010-11-01/distribution/{$distribution}/invalidation HTTP/1.0\r\n";
$msg .= "Host: cloudfront.amazonaws.com\r\n";
$msg .= "Date: {$date}\r\n";
$msg .= "Content-Type: text/xml; charset=UTF-8\r\n";
$msg .= "Authorization: AWS {$access_key}:{$sig}\r\n";
$msg .= "Content-Length: {$len}\r\n\r\n";
$msg .= $xml;

$fp = fsockopen('ssl://cloudfront.amazonaws.com', 443, 
    $errno, $errstr, 30
);
if (!$fp) {
    die("Connection failed: {$errno} {$errstr}\n");
}
fwrite($fp, $msg);
$resp = '';
while(! feof($fp)) {
    $resp .= fgets($fp, 1024);
}
fclose($fp);
echo $resp;