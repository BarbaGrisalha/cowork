<?php

use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = 'Documentação da API';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-api-docs py-5">
    <div class="container">
        <h1 class="text-center mb-5"><?= Html::encode($this->title) ?></h1>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3>Cowork IPLeiria API v1</h3>
            </div>
            <div class="card-body">
                <p><strong>Base URL:</strong> <code>http://localhost:8080/cowork/frontend/web/api</code></p>
                <p><strong>Autenticação:</strong> Bearer Token no header <code>Authorization</code></p>

                <h4 class="mt-4">Endpoints Disponíveis</h4>

                <table class="table table-bordered table-striped">
                    <thead class="bg-light">
                        <tr>
                            <th>Método</th>
                            <th>Endpoint</th>
                            <th>Descrição</th>
                            <th>Parâmetros</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>POST</td>
                            <td>/login</td>
                            <td>Login e obtém JWT</td>
                            <td>JSON: {"username": "altamir", "password": "altamir123"}</td>
                        </tr>
                        <tr>
                            <td>GET</td>
                            <td>/rooms</td>
                            <td>Lista todas as salas disponíveis</td>
                            <td>Nenhum</td>
                        </tr>
                        <tr>
                            <td>POST</td>
                            <td>/reservation</td>
                            <td>Cria uma nova reserva</td>
                            <td>JSON com data_reserva, hora_inicio_temp, hora_fim_temp, room_id, periodo</td>
                        </tr>
                        <tr>
                            <td>GET</td>
                            <td>/reservation</td>
                            <td>Lista reservas do usuário</td>
                            <td>Nenhum</td>
                        </tr>
                        <tr>
                            <td>POST</td>
                            <td>/pay/{id}</td>
                            <td>Paga uma reserva</td>
                            <td>ID da reserva</td>
                        </tr>
                        <tr>
                            <td>GET</td>
                            <td>/invoice/{id}</td>
                            <td>Mostra fatura</td>
                            <td>ID da reserva</td>
                        </tr>
                        <tr>
                            <td>GET</td>
                            <td>/invoice/pdf/{id}</td>
                            <td>Download PDF da fatura</td>
                            <td>ID da reserva</td>
                        </tr>
                    </tbody>
                </table>

                <h4 class="mt-5">Exemplo de Uso (Postman ou Volley)</h4>
                <pre class="bg-dark text-white p-3 rounded">
POST http://localhost:8080/cowork/frontend/web/api/login
Content-Type: application/json

{
    "username": "altamir",
    "password": "123456"
}

Resposta:
{
    "success": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
                </pre>

                <p class="mt-4">Use o token no header <code>Authorization: Bearer {token}</code> para os outros endpoints.</p>
            </div>
        </div>
    </div>
</div>