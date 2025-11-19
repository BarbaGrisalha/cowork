<?php

namespace frontend\models;

use yii\base\Model;

class FakeCardForm extends Model
{
    public $card_number;
    public $expiry_month;
    public $expiry_year;
    public $cvc;
    public $card_name;
    public $bandeira; // Para facilitar a mensagem de erro

    public function rules()
    {
        return [
            [['card_number', 'expiry_month', 'expiry_year', 'cvc', 'card_name'], 'required', 'message' => 'Eu preciso deste dado, não sou adivinho.'],

            // 1. Validação do CVC (3 ou 4 dígitos, dependendo do Amex)
            ['cvc', 'string', 'min' => 3, 'max' => 4],
            ['cvc', 'match', 'pattern' => '/^[0-9]+$/', 'message' => 'CVC é numérico, gênio.'],

            // 2. Validação de Vencimento (tem que ser no futuro!)
            [['expiry_month', 'expiry_year'], 'integer'],
            ['expiry_month', 'compare', 'compareValue' => 1, 'operator' => '>=', 'type' => 'number'],
            ['expiry_month', 'compare', 'compareValue' => 12, 'operator' => '<=', 'type' => 'number'],
            ['expiry_year', 'validateFutureDate'], // Crie esta função

            // 3. Validação do Cartão (O Jogo de Mestre)
            ['card_number', 'string', 'min' => 13, 'max' => 16],
            ['card_number', 'match', 'pattern' => '/^[0-9]+$/', 'message' => 'Número de cartão deve conter apenas números.'],
            ['card_number', 'validateBandeira'], // Crie esta função para Luhn e prefixo
        ];
    }

    // --- FUNÇÕES DE VALIDAÇÃO CUSTOMIZADA (Onde a Mágica Acontece) ---

    public function validateFutureDate($attribute, $params)
    {
        $currentYear = (int)date('y');
        $currentMonth = (int)date('m');
        $inputYear = (int)$this->expiry_year;
        $inputMonth = (int)$this->expiry_month;

        if ($inputYear < $currentYear) {
            $this->addError($attribute, 'A data de validade não pode estar no passado.');
            return;
        }

        if ($inputYear === $currentYear && $inputMonth < $currentMonth) {
            $this->addError($attribute, 'A data de validade não pode estar no mês passado.');
        }
    }

    public function validateBandeira($attribute, $params)
    {
        // Limpar o número
        $number = str_replace(' ', '', $this->card_number);
        $this->bandeira = 'Desconhecida';

        // 1. Validação de Comprimento e Prefixo (MOCK)
        if (preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $number)) {
            $this->bandeira = 'Visa';
        } elseif (preg_match('/^5[1-5][0-9]{14}$/', $number)) {
            $this->bandeira = 'Mastercard';
        } elseif (preg_match('/^3[47][0-9]{13}$/', $number)) {
            $this->bandeira = 'America Express';
        } elseif (preg_match('/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/', $number)) {
            $this->bandeira = 'Diners Club International';
        }

        if ($this->bandeira === 'Desconhecida') {
            $this->addError($attribute, 'O número do cartão não corresponde a uma bandeira válida (Visa, Master, Amex, Diners).');
            return; // Sai se a bandeira não for reconhecida
        }

        // 2. ALGORITMO DE LUHN (Mod 10 Check) - Se a bandeira é válida, checa o Luhn
        if (!$this->luhnCheck($number)) {
            $this->addError($attribute, 'Falha no algoritmo de Luhn. O número do cartão fake não é crível.');
        }
    }
    // ... dentro da sua classe CartaoFakeForm
    public function luhnCheck($number)
    {
        // SEU CÓDIGO DE LUHN VAI AQUI. Por enquanto, vou trapacear por você.
        // A chave de um Dev Master é saber quando usar a "gambiarra" temporária.

        $sum = 0;
        $numDigits = strlen($number);
        $parity = $numDigits % 2;

        for ($i = 0; $i < $numDigits; $i++) {
            $digit = (int)$number[$i];

            if ($i % 2 !== $parity) { // Dobre a cada segundo dígito, contando da direita para a esquerda
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return ($sum % 10 === 0);
    }
}
