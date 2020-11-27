<?php

namespace App\Pix;

class Payload
{
    /**
     * IDs do Payload do Pix
     * @var string
     */
    const ID_PAYLOAD_FORMAT_INDICATOR = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION = '26';
    const ID_MERCHANT_ACCOUNT_INFORMATION_GUI = '00';
    const ID_MERCHANT_ACCOUNT_INFORMATION_KEY = '01';
    const ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION = '02';
    const ID_MERCHANT_CATEGORY_CODE = '52';
    const ID_TRANSACTION_CURRENCY = '53';
    const ID_TRANSACTION_AMOUNT = '54';
    const ID_COUNTRY_CODE = '58';
    const ID_MERCHANT_NAME = '59';
    const ID_MERCHANT_CITY = '60';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE = '62';
    const ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID = '05';
    const ID_CRC16 = '63';

    private $chavePix;

    private $descricao;

    private $titular;

    private $cidade;

    private $identificador;

    private $valor;

    public function setChavePix($chavePix)
    {
        $this->chavePix = $chavePix;
        return $this;
    }

    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function setTitular($titular)
    {
        $this->titular = $titular;
        return $this;
    }

    public function setCidade($cidade)
    {
        $this->cidade = $cidade;
        return $this;
    }

    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;
        return $this;
    }

    public function setValor($valor)
    {
        $this->valor = (string) number_format($valor, 2, '.', '');
        return $this;
    }

    public function getValue($id, $value)
    {
        $size = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
        return $id . $size . $value;
    }

    private function getInfoConta()
    {
        $gui = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_GUI, 'br.gov.bcb.pix');

        $chave = $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_KEY, $this->chavePix);

        $descricao = ($this->descricao) ? $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION_DESCRIPTION, $this->descricao) : '';

        return $this->getValue(self::ID_MERCHANT_ACCOUNT_INFORMATION, $gui . $chave . $descricao);
    }

    private function getDadosAdicionais()
    {
        $identificador = $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE_TXID, $this->identificador);

        return $this->getValue(self::ID_ADDITIONAL_DATA_FIELD_TEMPLATE, $identificador);
    }

    /**
     * Método responsável por calcular o valor da hash de validação do código pix
     * @return string
     */
    private function getCRC16($payload)
    {
        //ADICIONA DADOS GERAIS NO PAYLOAD
        $payload .= self::ID_CRC16 . '04';

        //DADOS DEFINIDOS PELO BACEN
        $polinomio = 0x1021;
        $resultado = 0xFFFF;

        //CHECKSUM
        if (($length = strlen($payload)) > 0) {
            for ($offset = 0; $offset < $length; $offset++) {
                $resultado ^= (ord($payload[$offset]) << 8);
                for ($bitwise = 0; $bitwise < 8; $bitwise++) {
                    if (($resultado <<= 1) & 0x10000) {
                        $resultado ^= $polinomio;
                    }

                    $resultado &= 0xFFFF;
                }
            }
        }

        //RETORNA CÓDIGO CRC16 DE 4 CARACTERES
        return self::ID_CRC16 . '04' . strtoupper(dechex($resultado));
    }

    public function getPayload()
    {
        $payload = $this->getValue(self::ID_PAYLOAD_FORMAT_INDICATOR, '01')
        . $this->getInfoConta()
        . $this->getValue(self::ID_MERCHANT_CATEGORY_CODE, '0000')
        . $this->getValue(self::ID_TRANSACTION_CURRENCY, '986')
        . $this->getValue(self::ID_TRANSACTION_AMOUNT, $this->valor)
        . $this->getValue(self::ID_COUNTRY_CODE, 'BR')
        . $this->getValue(self::ID_MERCHANT_NAME, $this->titular)
        . $this->getValue(self::ID_MERCHANT_CITY, $this->cidade)
        . $this->getDadosAdicionais();

        return $payload . $this->getCRC16($payload);
    }
}
