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
$title = $crawler->filterXPath('//h1[@class="fb-product-cta__title"]')->text();
$img = "https:".$crawler->filterXPath('//img[@id="js-fb-pp-photo__media"]')->attr('src');
$divInfo = $crawler->filterXPath('//div[@id="fbra_browseMainProduct"]')->html();
$posStart = strpos($divInfo, '"prices":[');
$posFinal = strpos($divInfo, '}],"');
$posEnd = $posFinal - $posStart;
if ($posStart!= false) {
        $prices = json_decode("{".substr($divInfo,$posStart,$posEnd)."}]}", true);
}
$price = (float)sprintf("%1\$.2f", str_replace(",","", $prices['prices'][0]['originalPrice']));

if (!empty($price) && $price >= $min && $price <= $max) {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = getenv('MAIL_HOST');
        $mail->Port = getenv('MAIL_PORT');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Username = getenv('MAIL_USERNAME');
        $mail->Password = getenv('MAIL_PASSWORD');
        $mail->setFrom(getenv('MAIL_FROM_EMAIL'), getenv('MAIL_FROM_NAME'));
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = "Alerta: ".utf8_decode($title);
	$mail->Body = "<h3><a href='$url' target='_blank'>$title</a></h3><h2><strong>Precio: S/. $price</strong></h2><img src='$img' />";
        $mail->send();
}
