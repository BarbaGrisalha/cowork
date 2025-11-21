<?php

namespace frontend\models;

use yii\base\Model;

class PaymentMockForm extends Model
{
    public $test_input; // Um campo simples para dirigir o resultado

    public function rules()
    {
        return [
            ['test_input', 'required', 'message' => 'Insira um número para simular o pagamento (4, 5, ou qualquer outro).'],
            ['test_input', 'string', 'length' => [1, 19]],
        ];
    }
}
