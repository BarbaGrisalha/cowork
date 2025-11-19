<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Office - Agendamento de Recursos</title>

    <style>
        .side-drawer {
            position: fixed;
            top: 0;
            right: 0;
            width: 400px;
            height: 100%;
            /* Manter 100% como fallback ou valor de referência */
            max-height: 95vh;
            /* Limita a 95% da altura da viewport */
            overflow-y: auto;
            /* Adiciona barra de rolagem se o conteúdo for maior que 95vh */
            background: #fff;
            z-index: 1000;
            padding: 20px;
            box-shadow: -4px 0 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease-in-out;
            transform: translateX(400px);
            /* Inicialmente escondido */
        }

        .side-drawer.is-open {
            transform: translateX(0);
        }

        .resource-option.selected {
            background-color: #2ecc71;
            /* Cor de fundo verde */
            color: white;
            /* Cor do texto branco */
            border-color: #27ae60;
        }
    </style>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body>

    <div class="office-container" x-data="bookingApp()">
        <div id="calendar"></div>

        <div
            class="side-drawer"
            :class="{ 'is-open': isOpen }"
            x-show="isOpen"
            @click.away="isOpen = false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="ease-in duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full">
            <h2 class="text-xl font-bold border-b pb-2 mb-4">
                Agendar em: <span x-text="selectedDateDisplay"></span>
            </h2>

            <button @click="isOpen = false" style="position: absolute; top: 10px; right: 10px; font-size: 20px;">&times;</button>

            <div x-show="step === 1">
                <h3 class="text-lg mb-3">1. Qual tipo de recurso você precisa?</h3>

                <template x-for="resource in resources" :key="resource.key">
                    <div
                        class="resource-option"
                        :class="{ 'selected': selectedResource.key === resource.key }"
                        @click="selectResource(resource.key)">
                        <strong x-text="resource.name"></strong>
                        <p class="text-sm" x-text="resource.description"></p>
                    </div>
                </template>

                <button
                    @click="step = 2; fetchTimeSlots()"
                    :disabled="!selectedResource.key"
                    style="padding: 10px; background-color: #3498db; color: white; border: none; width: 100%; margin-top: 15px; cursor: pointer;">
                    Próximo &rarr;
                </button>
            </div>

            <div x-show="step === 2">
                <button @click="step = 1" style="background: none; border: none; color: #3498db;">&larr; Voltar</button>
                <h3 class="text-lg mb-3">2. Selecione um horário para <strong x-text="selectedResource.name"></strong>:</h3>

                <div x-show="loading" style="text-align: center; padding: 20px;">Carregando horários...</div>
                <div x-show="!loading && availableSlots.length === 0">Nenhum horário disponível. Tente outra data.</div>

                <div x-show="!loading && availableSlots.length > 0">
                    <template x-for="slot in availableSlots" :key="slot.start">
                        <div
                            class="time-slot"
                            :class="{ 'selected': selectedSlot.start === slot.start, 'unavailable': !slot.is_available }"
                            @click="slot.is_available ? selectSlot(slot) : null">
                            <span x-text="slot.display_time"></span>
                        </div>
                    </template>
                </div>

                <button
                    @click="confirmBooking()"
                    :disabled="!selectedSlot.start"
                    style="padding: 10px; background-color: #2ecc71; color: white; border: none; width: 100%; margin-top: 20px; cursor: pointer;">
                    Confirmar Reserva
                </button>
            </div>

        </div>
    </div>

    <script>
        // A única comunicação com o nosso PHP Backend é feita via API RESTful
        const API_BASE_URL = '/api/';

        document.addEventListener('alpine:init', () => {
            Alpine.data('bookingApp', () => ({
                // Estado do Alpine
                isOpen: false,
                step: 1, // 1: Seleção de Recurso, 2: Seleção de Horário
                selectedDate: '',
                selectedDateDisplay: '',
                loading: false,

                // Dados de Seleção
                resources: [{
                        key: 'private_office',
                        name: 'Escritório Privativo',
                        description: 'Espaço reservado para foco total.'
                    },
                    {
                        key: 'dedicated_desk',
                        name: 'Mesa Dedicada',
                        description: 'Seu ponto fixo na coworking.'
                    },
                    {
                        key: 'meeting_room',
                        name: 'Sala de Reunião',
                        description: 'Para equipes e clientes (capacidade 8).'
                    },
                    {
                        key: 'tech_services',
                        name: 'Serviços de Tecnologia',
                        description: 'Suporte técnico ou equipamentos (Slots de 30min).'
                    },
                ],
                selectedResource: {
                    key: '',
                    name: ''
                },
                availableSlots: [], // Slots disponíveis vêm do Backend
                selectedSlot: {
                    start: '',
                    end: '',
                    display_time: ''
                },

                init() {
                    this.initCalendar();
                },

                initCalendar() {
                    const calendarEl = document.getElementById('calendar');
                    const calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        locale: 'pt-br',
                        // Função que lida com o clique na data
                        dateClick: (info) => {
                            // Limpa o estado e define a data
                            this.selectedDate = info.dateStr;
                            this.selectedDateDisplay = info.date.toLocaleDateString('pt-BR', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric'
                            });
                            this.resetDrawer();
                            this.isOpen = true; // Abre a Gaveta Lateral
                        },
                        // Simulação de eventos carregados do banco de dados (reservas existentes)
                        events: (fetchInfo, successCallback, failureCallback) => {
                            // **AQUI VAI O FETCH DE EVENTOS DA API**
                            // Ex: fetch(`${API_BASE_URL}/bookings?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)...
                            // Por simplicidade, simulamos eventos de demonstração:
                            successCallback([{
                                    title: 'Sala Reunião',
                                    date: '2025-10-20',
                                    color: '#f39c12'
                                },
                                {
                                    title: 'Mesa Dedicada',
                                    date: '2025-10-21',
                                    color: '#1abc9c'
                                }
                            ]);
                        }
                    });
                    calendar.render();
                    this.$calendar = calendar; // Guarda a referência do calendário
                },

                resetDrawer() {
                    this.step = 1;
                    this.selectedResource = {
                        key: '',
                        name: ''
                    };
                    this.selectedSlot = {
                        start: '',
                        end: '',
                        display_time: ''
                    };
                    this.availableSlots = [];
                    this.loading = false;
                },

                selectResource(key) {
                    const resource = this.resources.find(r => r.key === key);
                    this.selectedResource = {
                        key: resource.key,
                        name: resource.name
                    };
                },

                selectSlot(slot) {
                    this.selectedSlot = slot;
                },

                async fetchTimeSlots() {
                    if (!this.selectedResource.key) return;
                    this.loading = true;
                    this.availableSlots = [];

                    // ----------------------------------------------------
                    // FETCH real para o Backend PHP: 
                    // Ele retorna os slots abertos, checando por conflitos.
                    // ----------------------------------------------------

                    try {
                        const response = await fetch(`${API_BASE_URL}/reservation/${this.selectedResource.key}/${this.selectedDate}`);
                        if (!response.ok) {
                            throw new Error('Falha ao buscar slots');
                        }
                        // Simulando o retorno da API:
                        // const data = await response.json();

                        // Dados simulados para demo:
                        const data = [{
                                start: '09:00:00',
                                end: '10:00:00',
                                display_time: '9:00 - 10:00',
                                is_available: true
                            },
                            {
                                start: '10:00:00',
                                end: '11:00:00',
                                display_time: '10:00 - 11:00',
                                is_available: false
                            }, // Ocupado
                            {
                                start: '14:00:00',
                                end: '15:00:00',
                                display_time: '14:00 - 15:00',
                                is_available: true
                            },
                            {
                                start: '15:00:00',
                                end: '16:00:00',
                                display_time: '15:00 - 16:00',
                                is_available: true
                            }
                        ];

                        this.availableSlots = data;

                    } catch (error) {
                        console.error('Erro na API:', error);
                        alert('Erro ao carregar horários. Tente novamente.');
                    } finally {
                        this.loading = false;
                    }
                },

                async confirmBooking() {
                    if (!this.selectedResource.key || !this.selectedDate || !this.selectedSlot.start) {
                        alert('Preencha todas as etapas antes de confirmar.');
                        return;
                    }

                    // ----------------------------------------------------
                    // POST final para o Backend PHP (Endpoint seguro)
                    // ----------------------------------------------------

                    const bookingData = {
                        resource_type: this.selectedResource.name,
                        booking_date: this.selectedDate,
                        start_time: this.selectedSlot.start,
                        end_time: this.selectedSlot.end
                    };

                    try {
                        const response = await fetch(`${API_BASE_URL}/bookings`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer SEU_TOKEN_JWT` // **OBRIGATÓRIO PARA SEGURANÇA**
                            },
                            body: JSON.stringify(bookingData)
                        });

                        const result = await response.json();

                        if (response.status === 201) {
                            alert('✅ Reserva Confirmada com Sucesso!');
                            this.isOpen = false;

                            // Otimização: Adiciona o evento ao calendário localmente e recarrega a vista.
                            this.$calendar.addEvent({
                                title: bookingData.resource_type,
                                date: bookingData.booking_date,
                                color: '#2ecc71' // Cor de sucesso
                            });
                            this.$calendar.refetchEvents(); // Garante que o calendário está atualizado
                        } else {
                            // Se o Backend (PHP) retornou 422 Unprocessable Entity (Ex: slot foi pego por outro)
                            alert(`❌ Falha na Reserva: ${result.message || 'Erro de validação ou slot indisponível.'}`);
                        }
                    } catch (error) {
                        console.error('Erro no POST:', error);
                        alert('Erro de comunicação com o servidor. Tente mais tarde.');
                    }
                }
            }));
        });
    </script>
</body>

</html>