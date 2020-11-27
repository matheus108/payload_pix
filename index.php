<?php

use App\Pix\Payload;
use Mpdf\QrCode\Output\Png;
use Mpdf\QrCode\QrCode;

require __DIR__ . "/vendor/autoload.php";

$obPayload = (new Payload)
    ->setChavePix("{CHAVE PIX}")
    ->setDescricao('{DESCRIÇÃO DA COBRANÇA}')
    ->setTitular("{NOME DO TITULAR}")
    ->setCidade("{CIDADE DO TITULAR}")
    ->setValor(10.00)
    ->setIdentificador('{IDENTIFICAÇÃO}');

$payloadQrCode = $obPayload->getPayload();

$obQrCode = new QrCode($payloadQrCode);

$qrcode = (new Png)->output($obQrCode, 400);

header('Content-Type: image/png');
echo $qrcode;
