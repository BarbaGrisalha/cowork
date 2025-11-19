<?php
/* @var $this yii\web\View */
use yii\web\View;
use yii\helpers\Url;

// Simulação de como os dados do plano seriam passados
// Você pode ajustar estes valores conforme o link clicado (Por Hora, Diário, Mensal)
$planName = 'Diário';
$planPrice = '32,00';
$planCurrency = '€';
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - Cowork IPLeiria</title>
    <!-- Carregamento do Tailwind CSS para styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f9;
        }
        .card-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
            outline: none;
        }
    </style>
    <!-- Ícones de cartões de crédito/bandeiras -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-lg bg-white p-6 md:p-8 rounded-xl shadow-2xl border border-gray-100">

        <!-- Detalhes do Plano -->
        <div class="mb-6 pb-4 border-b border-gray-200">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Checkout</h1>
            <p class="text-sm text-gray-500">Pagamento do Plano: <span class="font-semibold text-indigo-600"><?= $planName ?></span></p>
            <p class="text-4xl font-extrabold text-indigo-600 mt-3"><?= $planPrice ?><?= $planCurrency ?></p>
        </div>

        <!-- Formulário de Pagamento -->
        <form id="paymentForm">
            <!-- Bandeiras de Cartão (Visual) -->
            <div class="mb-6 flex space-x-2 text-2xl" id="cardFlags">
                <!-- Ícones de simulação de bandeiras -->
                <i class="fab fa-cc-visa text-gray-300 transition-colors duration-300" data-prefix="4" id="icon-visa"></i>
                <i class="fab fa-cc-mastercard text-gray-300 transition-colors duration-300" data-prefix="5" id="icon-mastercard"></i>
                <i class="fab fa-cc-amex text-gray-300 transition-colors duration-300" data-prefix="34" id="icon-amex"></i>
                <i class="fab fa-cc-diners-club text-gray-300 transition-colors duration-300" data-prefix="36" id="icon-diners"></i>
            </div>

            <!-- Número do Cartão -->
            <div class="mb-6">
                <label for="cardNumber" class="block text-sm font-medium text-gray-700 mb-1">Número do Cartão</label>
                <input type="text" id="cardNumber" placeholder="0000 0000 0000 0000" maxlength="19" class="w-full px-4 py-3 border border-gray-300 rounded-lg card-input text-lg tracking-wider" required>
            </div>

            <!-- Nome do Titular -->
            <div class="mb-6">
                <label for="cardName" class="block text-sm font-medium text-gray-700 mb-1">Nome do Titular</label>
                <input type="text" id="cardName" placeholder="Nome Completo" class="w-full px-4 py-3 border border-gray-300 rounded-lg card-input" required>
            </div>

            <!-- Data de Validade e CVV -->
            <div class="flex space-x-4 mb-6">
                <div class="flex-1">
                    <label for="expiryDate" class="block text-sm font-medium text-gray-700 mb-1">Validade (MM/AA)</label>
                    <input type="text" id="expiryDate" placeholder="MM/AA" maxlength="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg card-input" required>
                </div>
                <div class="flex-1">
                    <label for="cvv" class="block text-sm font-medium text-gray-700 mb-1">CVV</label>
                    <input type="text" id="cvv" placeholder="321" maxlength="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg card-input" required>
                </div>
            </div>

            <!-- Mensagens de Feedback -->
            <div id="feedbackMessage" class="mt-4 p-3 rounded-lg text-center font-medium hidden"></div>

            <!-- Botão de Pagamento -->
            <button type="submit" class="w-full bg-indigo-600 text-white py-3 mt-6 rounded-lg font-bold text-lg shadow-lg hover:bg-indigo-700 transition-colors duration-300">
                Pagar Agora
            </button>
        </form>

    </div>

    <script>
        const cardNumberInput = document.getElementById('cardNumber');
        const expiryDateInput = document.getElementById('expiryDate');
        const cvvInput = document.getElementById('cvv');
        const feedbackMessage = document.getElementById('feedbackMessage');
        const cardFlags = {
            '4': document.getElementById('icon-visa'),
            '5': document.getElementById('icon-mastercard'),
            '34': document.getElementById('icon-amex'),
            '37': document.getElementById('icon-amex'),
            '36': document.getElementById('icon-diners'),
        };

        // --- Variáveis de Simulação (Regras do Projeto de Estudo) ---
        const VALID_LAST_TWELVE_DIGITS = '333322221111';
        const VALID_EXPIRY_DATE = '12/26';
        const VALID_CVV = '321';
        // -----------------------------------------------------------

        /**
         * Formata o número do cartão com espaços e destaca a bandeira.
         */
        cardNumberInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\s/g, ''); // Remove todos os espaços
            let formattedValue = '';
            
            // Adiciona espaço a cada 4 dígitos
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            e.target.value = formattedValue;
            
            // Destaca a Bandeira
            highlightCardBrand(value);
        });

        /**
         * Formata a data de validade como MM/AA.
         */
        expiryDateInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, ''); // Remove não-dígitos
            let formattedValue = value;

            if (value.length > 2) {
                // Insere a barra /
                formattedValue = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            if (formattedValue.length > 5) {
                formattedValue = formattedValue.substring(0, 5);
            }
            e.target.value = formattedValue;
        });

        /**
         * Destaca o ícone da bandeira com base no prefixo do cartão.
         */
        function highlightCardBrand(number) {
            // Limpa o destaque de todas as bandeiras
            Object.values(cardFlags).forEach(icon => {
                icon.classList.remove('text-indigo-600');
                icon.classList.add('text-gray-300');
            });

            if (number.length >= 2) {
                const prefix1 = number.substring(0, 1);
                const prefix2 = number.substring(0, 2);

                let targetIcon = null;

                if (cardFlags[prefix2]) { // Ex: 34, 37
                    targetIcon = cardFlags[prefix2];
                } else if (cardFlags[prefix1]) { // Ex: 4, 5
                    targetIcon = cardFlags[prefix1];
                }
                
                if (targetIcon) {
                    targetIcon.classList.remove('text-gray-300');
                    targetIcon.classList.add('text-indigo-600');
                }
            }
        }

        /**
         * Lógica de Simulação de Pagamento
         */
        document.getElementById('paymentForm').addEventListener('submit', (e) => {
            e.preventDefault();
            
            feedbackMessage.classList.add('hidden');
            feedbackMessage.classList.remove('bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700');

            const fullCardNumber = cardNumberInput.value.replace(/\s/g, '');
            const expiryDate = expiryDateInput.value;
            const cvv = cvvInput.value;
            
            // 1. Validar tamanho do número do cartão
            if (fullCardNumber.length !== 16) {
                showFeedback('Número do cartão deve ter 16 dígitos.', 'error');
                return;
            }

            // 2. Validar o padrão dos 12 últimos dígitos (Regra do Projeto)
            const brandPrefix = fullCardNumber.substring(0, 4);
            const lastTwelveDigits = fullCardNumber.substring(4);
            
            if (lastTwelveDigits !== VALID_LAST_TWELVE_DIGITS) {
                showFeedback(`Simulação: Últimos 12 dígitos inválidos. Use: ${VALID_LAST_TWELVE_DIGITS}`, 'error');
                return;
            }

            // 3. Validar a data de validade (Regra do Projeto)
            if (expiryDate !== VALID_EXPIRY_DATE) {
                showFeedback(`Simulação: Data de validade inválida. Use: ${VALID_EXPIRY_DATE}`, 'error');
                return;
            }

            // 4. Validar o CVV (Regra do Projeto)
            if (cvv !== VALID_CVV) {
                showFeedback(`Simulação: CVV inválido. Use: ${VALID_CVV}`, 'error');
                return;
            }
            
            // 5. Sucesso (Se todas as regras de simulação passarem)
            showFeedback('Pagamento Aprovado! Redirecionando...', 'success');
            // Em um projeto real, você faria aqui um fetch() para o backend.
            
            // Simulação de redirecionamento após 2 segundos
            setTimeout(() => {
                // Redirecionar ou mostrar a próxima tela de confirmação
                alert('Simulação de Pagamento Concluída com Sucesso!'); 
                // Note: Substituí o alert() por um modal/mensagem em uma aplicação real.
            }, 2000);
        });

        /**
         * Exibe a mensagem de feedback
         */
        function showFeedback(message, type) {
            feedbackMessage.textContent = message;
            feedbackMessage.classList.remove('hidden');
            
            if (type === 'success') {
                feedbackMessage.classList.add('bg-green-100', 'text-green-700');
            } else {
                feedbackMessage.classList.add('bg-red-100', 'text-red-700');
            }
        }
        
    </script>
</body>
</html>
