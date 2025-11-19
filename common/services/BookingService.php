<?php

declare(strict_types=1);

namespace common\services;

use Yii;
use yii\db\Connection;
use yii\db\Query;

/**
 * Service Layer para lidar com a lógica de disponibilidade e horários.
 * No Yii2, usaremos o componente Db para consultas.
 */

class BookingService
{
    /**
     * @var Connection
     */
    private $db;

    public function __construct()
    {
        // Padrão Yii2: injetar o componente 'db' (conexão com o BD)
        $this->db = Yii::$app->db;
    }

    /**
     * Gera todos os slots possíveis e verifica quais estão ocupados.
     * @param string $resourceType O ID do recurso (agora mapeado para room_id)
     * @param string $date A data no formato 'YYYY-MM-DD'
     * @param int $slotDurationMinutes Duração do slot em minutos.
     * @param string $startHour Hora de início da operação (HH:MM:SS).
     * @param string $endHour Hora final da operação (HH:MM:SS).
     * @return array Lista de slots com status de disponibilidade.
     */
    public function getDailySlots(
        string $resourceType,
        string $date,
        int $slotDurationMinutes = 60, // 60 minutos por padrão
        string $startHour = '07:00:00',
        string $endHour = '21:00:00'
    ): array {
        // 1. Geração de Slots Potenciais
        $allSlots = $this->generatePotentialSlots($date, $startHour, $endHour, $slotDurationMinutes);

        // 2. Consulta ao Banco de Dados (Reservas Existentes)
        // *** CORREÇÃO AQUI ***
        $occupiedSlots = (new Query())
            // Usando as colunas corretas do seu SQL Dump
            ->select(['booking_start_time', 'booking_end_time'])
            ->from('reservations') // CORRIGIDO: Nome da tabela é 'reservations', não 'bookings'
            ->where([
                // CORRIGIDO: A coluna de filtro é 'room_id', não 'resource_type'
                'room_id' => $resourceType,
                // CORRIGIDO: A coluna de data é 'booking_date', não DATE(start_at)
                'booking_date' => $date,
            ])
            ->all($this->db);

        // 3. Checagem de Conflito e Marcação de Disponibilidade
        foreach ($allSlots as $index => &$slot) {
            $slot['is_available'] = true;

            $slotStart = new \DateTimeImmutable($date . ' ' . $slot['start']);
            $slotEnd = new \DateTimeImmutable($date . ' ' . $slot['end']);

            foreach ($occupiedSlots as $occupied) {
                // *** CORREÇÃO AQUI ***
                // Usando as colunas de hora selecionadas na query acima
                $occupiedStart = new \DateTimeImmutable($date . ' ' . $occupied['booking_start_time']);
                $occupiedEnd = new \DateTimeImmutable($date . ' ' . $occupied['booking_end_time']);

                // Lógica de Sobreposição (Intersection/Conflito)
                if ($slotStart < $occupiedEnd && $slotEnd > $occupiedStart) {
                    $slot['is_available'] = false;
                    break;
                }
            }
        }

        unset($slot);
        return $allSlots;
    }

    /**
     * Função auxiliar para gerar todos os intervalos de tempo possíveis.
     * * @param string $date 
     * @param string $startHour 
     * @param string $endHour 
     * @param int $duration
     * @return array
     */
    private function generatePotentialSlots(string $date, string $startHour, string $endHour, int $duration): array
    {
        $slots = [];
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $startHour);
        $end   = \DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $endHour);
        $interval = new \DateInterval('PT' . $duration . 'M'); // Duração do slot

        // Loop que gera os slots: 07:00-08:00, 08:00-09:00, ..., 20:00-21:00
        while ($start < $end) {
            $slotStart = clone $start;
            $start->add($interval); // Avança para o fim do slot

            if ($start > $end) {
                // Não inclui slots que excedem o horário de fechamento (21:00)
                break;
            }

            $slots[] = [
                // Formato exigido pelo Front-end (apenas hora)
                'start' => $slotStart->format('H:i:s'),
                'end' => $start->format('H:i:s'),
                // Formato amigável para exibição
                'display_time' => $slotStart->format('H:i') . ' - ' . $start->format('H:i'),
                'is_available' => true, // Status placeholder
            ];
        }

        return $slots;
    }
}
