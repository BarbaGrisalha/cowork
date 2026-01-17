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
                <p><strong>Base URL:</strong> <code>http://localhost:8080/cowork/api/web/</code></p>
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
                            <td>Login</td>
                            <td>JSON: {"username": "altamir", "password": "altamir1972"}</td>
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
                            <td>JSON {
                                "customer_id": 1,
                                "room_id": 4,
                                "data_reserva": "2026-01-16 13:30:00",
                                "hora_inicio_agendada": "2026-01-19 09:00:00",
                                "hora_fim_agendada": "2026-01-19 10:00:00",
                                "total_estimado": 7.00,
                                "status": "pendente",
                                "tipo_reserva": "hora"
                                }</td>
                        </tr>
                        <tr>
                            <td>GET</td>
                            <td>/reservation</td>
                            <td>Lista reservas dos usuários</td>
                            <td>Nenhum</td>
                        </tr>
                    </tbody>
                </table>

                <h4 class="mt-5">Exemplo de Uso (Postman ou Volley)</h4>
                <pre class="bg-dark text-white p-3 rounded">
POST http://localhost:8080/cowork/web/api/login
Content-Type: application/json

{
    "username": "altamir",
    "password": "altamir1972"
}

Resposta:
{
    "success": true,
    
}
                </pre>

                <p class="mt-4">Use o token no header <code>Authorization: Bearer {token}</code> para os outros endpoints.</p>
            </div>
        </div>
    </div>
</div>