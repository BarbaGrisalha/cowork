<?php

namespace frontend\models;

use yii\base\Model;

class FakeCardForm extends Model
{
    // 🚨 Campos solicitados:
    public $card_number;
    public $expiry_month;
    public $expiry_year;
    public $cvv;
    public $card_name;
    public $bandeira; // Apenas para debug/erro

    public function rules()
    {
        return [
            [['card_number', 'expiry_month', 'expiry_year', 'cvv', 'card_name'], 'required', 'message' => 'Eu preciso deste dado, não sou adivinho.'],

            // Validação do CVV
            ['cvv', 'string', 'min' => 3, 'max' => 4],
            ['cvv', 'match', 'pattern' => '/^[0-9]+$/', 'message' => 'CVV é numérico.'],

            // Validação de Vencimento
            [['expiry_month', 'expiry_year'], 'integer'],
            ['expiry_month', 'compare', 'compareValue' => 1, 'operator' => '>=', 'type' => 'number'],
            ['expiry_month', 'compare', 'compareValue' => 12, 'operator' => '<=', 'type' => 'number'],
            ['expiry_year', 'validateFutureDate'],

            // Validação do Cartão (Luhn e Prefixo)
            ['card_number', 'string', 'min' => 13, 'max' => 16],
            ['card_number', 'match', 'pattern' => '/^[0-9]+$/', 'message' => 'Número de cartão deve conter apenas números.'],
            ['card_number', 'validateBandeira'], // Onde a bandeira e o Luhn são checados
        ];
    }

    // --- FUNÇÕES DE VALIDAÇÃO CUSTOMIZADA (Recuperadas) ---

    public function validateFutureDate($attribute, $params)
    {
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        $inputYear = (int)$this->expiry_year;
        $inputMonth = (int)$this->expiry_month;

        if ($inputYear < $currentYear || ($inputYear === $currentYear && $inputMonth < $currentMonth)) {
            $this->addError($attribute, 'A data de validade não pode estar no passado.');
        }
    }

    public function validateBandeira($attribute, $params)
    {
        $number = str_replace(' ', '', $this->card_number);
        $this->bandeira = 'Desconhecida';

        if (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $number)) {
            $this->bandeira = 'Visa';
        } elseif (preg_match('/^5[1-5][0-9]{14}$/', $number)) {
            $this->bandeira = 'Mastercard';
        } elseif (preg_match('/^3[47][0-9]{13}$/', $number)) {
            $this->bandeira = 'America Express';
        } // Adicione outras bandeiras conforme necessário

        if ($this->bandeira === 'Desconhecida') {
            $this->addError($attribute, 'O número do cartão não corresponde a uma bandeira válida (Visa, Master, Amex, Diners).');
            return;
        }
        /*
        if (!$this->luhnCheck($number)) {
            $this->addError($attribute, 'Falha no algoritmo de Luhn. O número do cartão fake não é crível.');
        }
            */
    }

    public function luhnCheck($number)
    {
        // Remove tudo que não for dígito e garante que estamos com o número correto.
        $number = preg_replace('/[^0-9]/', '', $number);

        $sum = 0;
        $numDigits = strlen($number);

        // Itera do final para o início (o padrão do algoritmo de Luhn)
        for ($i = $numDigits - 1; $i >= 0; $i--) {
            $digit = (int)$number[$i];

            // O dígito a ser dobrado é aquele cujo índice do array (contando da direita, começando em 0) é ÍMPAR.
            if (($numDigits - 1 - $i) % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9; // Subtrai 9
                }
            }
            $sum += $digit;
        }
        return ($sum % 10 === 0);
    }
}
