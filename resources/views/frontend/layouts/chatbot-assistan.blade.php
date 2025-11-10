<div id="chatbot-assistant" style="position: fixed; bottom: 90px; right: 30px; z-index: 9999;">
    <button onclick="toggleChatbot()" style="background: #007bff; border: none; border-radius: 50%; width: 60px; height: 60px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
        <img src="{{ asset('frontend/images/iconos/assistant-icono.webp') }}" alt="Asistente" style="width: 32px;">
    </button>
    <div id="chatbot-window" style="display: none; position: absolute; bottom: 70px; right: 0; width: 320px; height: 400px; background: #fff; border-radius: 10px; box-shadow: 0 2px 16px rgba(0,0,0,0.2); overflow: hidden;">
        <div style="background: #007bff; color: #fff; padding: 10px; font-weight: bold;">Asistente Virtual</div>
        <div style="padding: 10px; height: 320px; overflow-y: auto;" id="chatbot-messages">
            <div>¡Hola! ¿En qué podemos ayudarte?</div>
            <div id="chatbot-options">
                <button onclick="chatbotOption('comprar')" style="margin: 5px;">Quiero comprar</button>
                <button onclick="chatbotOption('soporte')" style="margin: 5px;">Soporte técnico</button>
                <button onclick="chatbotOption('contacto')" style="margin: 5px;">Contacto</button>
            </div>
        </div>
        <form onsubmit="sendChatbotMessage(event)" style="display: flex; border-top: 1px solid #eee;">
            <input type="text" id="chatbot-input" placeholder="Escribe tu mensaje..." style="flex: 1; border: none; padding: 10px;">
            <button type="submit" style="background: #007bff; color: #fff; border: none; padding: 0 16px;">Enviar</button>
        </form>
    </div>



    {{-- POSIBLEMENTE NO  SE OCUPEEEEE --}}
</div>
<script>
function toggleChatbot() {
    var win = document.getElementById('chatbot-window');
    win.style.display = win.style.display === 'none' ? 'block' : 'none';
}
function sendChatbotMessage(e) {
    e.preventDefault();
    var input = document.getElementById('chatbot-input');
    var messages = document.getElementById('chatbot-messages');
    if(input.value.trim() !== '') {
        var userMsg = document.createElement('div');
        userMsg.textContent = input.value;
        userMsg.style.textAlign = 'right';
        messages.appendChild(userMsg);
        var botMsg = document.createElement('div');
        botMsg.textContent = 'Gracias por tu mensaje. Pronto te responderemos.';
        messages.appendChild(botMsg);
        input.value = '';
        messages.scrollTop = messages.scrollHeight;
    }
}
function chatbotOption(option) {
    var messages = document.getElementById('chatbot-messages');
    var optionsDiv = document.getElementById('chatbot-options');
    if (optionsDiv) optionsDiv.style.display = 'none';

    var userMsg = document.createElement('div');
    userMsg.textContent = option === 'comprar' ? 'Quiero comprar' : option === 'soporte' ? 'Soporte técnico' : 'Contacto';
    userMsg.style.textAlign = 'right';
    messages.appendChild(userMsg);

    var botMsg = document.createElement('div');
    if(option === 'comprar') {
        botMsg.innerHTML = '¡Perfecto! <a href="/productos" style="color:#007bff;">Haz clic aquí para ver nuestros productos</a>.';
    } else if(option === 'soporte') {
        botMsg.innerHTML = '¿Qué tipo de soporte necesitas?<br><button onclick="chatbotSoporte(\'garantía\')">Garantía</button> <button onclick="chatbotSoporte(\'envío\')">Envío</button>';
    } else if(option === 'contacto') {
        botMsg.innerHTML = 'Puedes contactarnos por <a href="mailto:soporte@tusitio.com" style="color:#007bff;">correo</a> o por <a href="https://wa.link/f28njw" target="_blank" style="color:#007bff;">WhatsApp</a>.';
    }
    messages.appendChild(botMsg);
    messages.scrollTop = messages.scrollHeight;
}
function chatbotSoporte(tipo) {
    var messages = document.getElementById('chatbot-messages');
    var userMsg = document.createElement('div');
    userMsg.textContent = tipo === 'garantía' ? 'Garantía' : 'Envío';
    userMsg.style.textAlign = 'right';
    messages.appendChild(userMsg);

    var botMsg = document.createElement('div');
    if(tipo === 'garantía') {
        botMsg.textContent = 'Para temas de garantía, por favor visita nuestra sección de garantías o contáctanos directamente.';
    } else if(tipo === 'envío') {
        botMsg.textContent = 'Para dudas sobre envíos, revisa nuestra política de envíos o escríbenos por WhatsApp.';
    }
    messages.appendChild(botMsg);
    messages.scrollTop = messages.scrollHeight;
}
</script>