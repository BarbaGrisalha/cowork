<?php

use yii\db\Migration;
use yii\db\Expression;

/**
 * Class m251107_195108_seed_tables_with_datas
 * Migration única para popular todos os dados iniciais. (FINALMENTE CORRIGIDO PARA FK user_id e auth_key)
 */
class m251107_195108_seed_tables_with_datas extends Migration
{
    private $userIds = [];
    private $customerIds = [];
    private $roomIds = [];


    public function safeUp()
    {
        echo "Iniciando Seeding Consolidado de Dados (Yii2)..." . PHP_EOL;

        // 1. Dependência Zero
        $this->seedPricingPlans();

        // 2. CRÍTICO: Usuários devem vir antes de clientes.
        $this->seedUsers();

        // 3. Dependências Resolvidas
        $this->seedCustomers();
        $this->seedRooms();
        $this->seedReservations();

        echo "Seeding concluído com sucesso." . PHP_EOL;
    }

    public function safeDown()
    {
        echo "Removendo dados de Seeding em ordem inversa..." . PHP_EOL;

        $this->delete('reservations');
        $this->delete('rooms');
        $this->delete('customers');
        // Remoção dos usuários criados (IMPORTANTE!)
        $this->delete('user', ['id' => $this->userIds]);
        $this->delete('pricing_plans');

        echo "Dados de Seeding removidos com sucesso." . PHP_EOL;
        return true;
    }

    // --------------------------------------------------------------------------------
    // MÉTODOS DE POPULAÇÃO
    // --------------------------------------------------------------------------------

    private function seedPricingPlans()
    {
        echo "Populando Pricing Plans (3 registros)..." . PHP_EOL;

        $data = [
            ['Plano Horário Sênior', 'HOUR', 7.00, 1],
            ['Plano Diário Flex', 'DAY', 32.00, 1],
            ['Plano Mensal Gold', 'MONTH', 225.00, 1]
        ];

        $this->batchInsert('pricing_plans', ['nome', 'unidade_tempo', 'valor', 'is_active'], $data);
    }

    private function seedUsers()
    {
        echo "Populando 10 Usuários (User) para satisfazer FK customers.user_id..." . PHP_EOL;
        $data = [];
        $time = time();

        for ($i = 1; $i <= 10; $i++) {
            // CORREÇÃO APLICADA: auth_key é apenas o hash MD5 (32 caracteres).
            $authKey = md5(uniqid('auth', true) . $i);

            $data[] = [
                'customer' . $i,
                $authKey,
                // Hash dummy de 60 caracteres (padrão de bcrypt, mais seguro para seed)
                '$2y$13$sD.X/0G9d3gWlR6eI7dEBeuQ7d3kG9d3gWlR6eI7dEBeuQ7d3kG9d3g',
                'customer' . $i . '@cowork.dev',
                10,
                $time,
                $time
            ];
        }

        $this->batchInsert('user', ['username', 'auth_key', 'password_hash', 'email', 'status', 'created_at', 'updated_at'], $data);

        $this->userIds = $this->db->createCommand("SELECT id FROM user ORDER BY id DESC LIMIT 10")->queryColumn();
        sort($this->userIds);
    }


    private function seedCustomers()
    {
        echo "Populando 10 Clientes (Customers)..." . PHP_EOL;
        $data = [];
        $nomes = [
            'Alice Silva',
            'Bruno Costa',
            'Carla Mendes',
            'David Almeida',
            'Eva Fernandes',
            'Filipe Rocha',
            'Gisela Pires',
            'Hugo Ribeiro',
            'Inês Martins',
            'João Neves'
        ];

        for ($i = 0; $i < 10; $i++) {
            $nome = $nomes[$i];

            $userId = $this->userIds[$i];
            $nifFicticio = '50' . str_pad((string)(rand(1000000, 9999999)), 7, '0', STR_PAD_LEFT);
            $telefone = '9' . str_pad((string)(rand(10000000, 99999999)), 8, '0', STR_PAD_LEFT);

            $data[] = [
                $userId,
                $nome,
                $nifFicticio,
                "Rua Fictícia, {$i}",
                $telefone,
                new Expression('NOW()')
            ];
        }

        $this->batchInsert('customers', ['user_id', 'nome', 'nif', 'morada', 'telefone', 'data_registro'], $data);

        $this->customerIds = $this->db->createCommand("SELECT id FROM customers ORDER BY id DESC LIMIT 10")->queryColumn();
        sort($this->customerIds);
    }

    private function seedRooms()
    {
        echo "Populando 10 Salas/Mesas (Rooms)..." . PHP_EOL;
        $data = [];
        $totalPlans = 3;

        for ($i = 1; $i <= 10; $i++) {
            $capacidade = $i % 3 === 0 ? 8 : ($i % 2 === 0 ? 4 : 2);
            $existingPricingPlanId = ($i % $totalPlans) + 1;

            $data[] = [
                "Mesa de Coworking " . $i,
                $capacidade,
                "Mesa com capacidade para {$capacidade} pessoas.",
                $existingPricingPlanId,
                'ativa'
            ];
        }

        $this->batchInsert('rooms', ['nome_sala', 'capacidade', 'descricao', 'pricing_plan_id', 'status'], $data);

        $this->roomIds = $this->db->createCommand("SELECT id FROM rooms ORDER BY id DESC LIMIT 10")->queryColumn();
        sort($this->roomIds);
    }

    private function seedReservations()
    {
        if (empty($this->roomIds) || empty($this->customerIds)) {
            echo "ERRO: IDs de Salas ou Clientes não encontrados. Verifique as tabelas." . PHP_EOL;
            return;
        }

        echo "Populando 10 Reservas (Reservations)..." . PHP_EOL;
        $data = [];
        $statuses = ['confirmada', 'pendente', 'rascunho'];
        $now = new DateTimeImmutable();

        for ($i = 0; $i < 10; $i++) {
            $date = $now->modify('+' . ($i % 5) . ' days')->format('Y-m-d');
            $startTime = rand(9, 17);
            $durationHours = rand(1, 3);

            $startDateTime = $date . ' ' . str_pad((string)$startTime, 2, '0', STR_PAD_LEFT) . ':00:00';
            $endDateTime = $now->setTimestamp(strtotime($startDateTime))->modify("+$durationHours hours")->format('Y-m-d H:i:s');

            $data[] = [
                $this->customerIds[$i % count($this->customerIds)],
                $this->roomIds[$i % count($this->roomIds)],
                new Expression('NOW()'),
                $startDateTime,
                $endDateTime,
                (float)($durationHours * 7.00),
                $statuses[rand(0, 2)],
            ];
        }

        $this->batchInsert(
            'reservations',
            ['customer_id', 'room_id', 'data_reserva', 'hora_inicio_agendada', 'hora_fim_agendada', 'total_estimado', 'status'],
            $data
        );
    }
}
