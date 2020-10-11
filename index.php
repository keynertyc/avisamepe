<?php

/*** 
 * By Keyner
 * keyner.peru@gmail.com
 * https://github.com/keynertyc
***/

require 'vendor/autoload.php';
use Goutte\Client;
use PHPMailer\PHPMailer\PHPMailer;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

$sku = $argv[1];
$min = $argv[2];
$max = $argv[3];
$email = $argv[4];
$url = 'https://www.falabella.com.pe/falabella-pe/search/?Ntt='.$sku;

$client = new Client();
$crawler = $client->request('GET', $url);
$title = $crawler->filterXPath('//*[@id="__next"]/div[1]/section/div[1]/div[1]/div[2]/section[2]/div[1]/div[2]/h1/div')->text();
$img = $crawler->filter('.fa--image-gallery-item__desktop')->filter('img')->first()->attr('src');
$divInfo = $crawler->filterXPath('//*[@id="__NEXT_DATA__"]')->html();
$posStart = strpos($divInfo, '"prices":[');
if ($posStart < 0) exit;

$priceInfo = substr($divInfo,$posStart);
$posStart = strpos($priceInfo, '"prices":[');
$posFinal = strpos($priceInfo, '}],"');
$priceInfo = substr($priceInfo,$posStart,$posFinal);
$prices = json_decode("{".$priceInfo."}]}", true);

foreach ($prices['prices'] as $row) {
        $type = $row['type'];
        $symbol = $row['symbol'];
        $price = (float)sprintf("%1\$.2f", str_replace(",","", $row['price'][0]));

        if (!empty($price) && $price >= $min && $price <= $max) {
                $payload = [
                        'title' => $title,
                        'url' => $url,
                        'symbol' => $symbol,
                        'price' => $price,
                        'type' => $type,
                        'img' => $img,
                ];

                sendMail($email, $payload); 
                break;
        }
}

function sendMail($email, $payload) {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = getenv('MAIL_HOST');
        $mail->Port = getenv('MAIL_PORT');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Username = getenv('MAIL_USERNAME');
        $mail->Password = getenv('MAIL_PASSWORD');
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(getenv('MAIL_FROM_EMAIL'), getenv('MAIL_FROM_NAME'));
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = "Alerta: ".utf8_decode($payload['title']);
        $mail->Body = "<h3><a href='".$payload['url']."' target='_blank'>".$payload['title']."</a></h3>
                        <h2><strong>Precio: ".$payload['symbol']." ".$payload['price']."</strong></h2>
                        <h2><strong>Tipo de Precio: ".$payload['type']."</strong></h2>
                        <img src='".$payload['img']."' />";
        $mail->send();
}