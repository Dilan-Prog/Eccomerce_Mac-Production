
<div id="floating-image">
    <img src="{{ asset('frontend/images/logo/AVIAzul-Marino.png') }}" alt="Chatbot" />
</div>
<div id="help-alert">
    <button id="close-alert">&times;</button>
    <div class="container-help-alert" style="color: white">
        <p>¿Necesitas ayuda?</p>
        <p>Habla con uno de nuestros especialistas de Mac Del Norte y obtén descuentos exclusivos así como la solución a tu proceso.</p>
    </div>
</div>

<div id="chat-window">
    <div class="chat-header">
        <p>Chatea con tu Especialista de Mac Del Norte</p>
        <button id="close-chat">&times;</button>
    </div>
    <div class="chat-body">
        <!-- Mensajes -->
        <div class="message-container">
            <img src="{{ asset('frontend/images/logo/AVIAzul-Marino.png') }}" alt="Avatar" class="avatar" />
            <div class="messages">
                <div class="message single-message">
                    ¿Necesitas ayuda?
                </div>
                <div class="message">
                    Hola. Soy tu Especialista Mac Del Norte, ¿en qué puedo ayudarte hoy?
                </div>
                <div class="chat-options">
                    <button class="chat-option-btn" data-option="comprar">Quiero comprar</button>
                    <button class="chat-option-btn" data-option="soporte">Quiero soporte tecnico</button>
                    <button class="chat-option-btn" data-option="ayuda">Necesito ayuda</button>
                </div>
            </div>
        </div>
    </div>
    <div class="chat-footer">
        {{-- <input type="text" placeholder="Escribe tu mensaje aquí..." />
        <button>Enviar</button> --}}
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const helpAlert = document.getElementById("help-alert");
    const closeAlertButton = document.getElementById("close-alert");
    const floatingImage = document.getElementById("floating-image");
    const chatWindow = document.getElementById("chat-window");
    const closeChatButton = document.getElementById("close-chat");
    const chatBody = chatWindow.querySelector('.chat-body');

    let helpAlertClicked = false;

    // Mostrar la alerta después de 10 segundos
    setTimeout(() => {
        if (!helpAlertClicked) {
            helpAlert.style.display = "block";
        }
    }, 10000);

    // Cerrar la alerta al hacer clic en la "X"
    closeAlertButton.addEventListener("click", () => {
        helpAlert.style.display = "none";
    });

    // Alternar la ventana de chat al hacer clic en la imagen flotante
    floatingImage.addEventListener("click", () => {
        helpAlert.style.display = "none";
        helpAlertClicked = true;

        if (chatWindow.style.display === "block") {
            chatWindow.style.display = "none";
        } else {
            chatWindow.style.display = "block";
        }
    });

    // Abrir el chat directamente si se hace clic en help-alert
    helpAlert.addEventListener("click", () => {
        helpAlert.style.display = "none";
        chatWindow.style.display = "block";
        helpAlertClicked = true;
    });

    // Cerrar el chat al hacer clic en la "X" del chat
    closeChatButton.addEventListener("click", () => {
        chatWindow.style.display = "none";
    });

    // Función para agregar mensaje del usuario al chat
    function addUserMessage(text) {
        const userMsgDiv = document.createElement('div');
        userMsgDiv.className = 'message user-message';
        userMsgDiv.textContent = text;
        chatBody.appendChild(userMsgDiv);
        chatBody.scrollTop = chatBody.scrollHeight; // Scroll al final
    }

    // Función para agregar mensaje del bot al chat
    function addBotMessage(html) {
        const botMsgDiv = document.createElement('div');
        botMsgDiv.innerHTML = html;
        chatBody.appendChild(botMsgDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    // Función para crear nuevas opciones
    function addOptions(options) {
        removeOldOptions();
        const optionsDiv = document.createElement('div');
        optionsDiv.className = 'chat-options';
        options.forEach(opt => {
            let el;
            // Si la opción tiene href, crea un <a>, si no, un <button>
            if(opt.href) {
                el = document.createElement('a');
                el.href = opt.href;
                el.target = opt.target || '_blank';
                el.rel = 'noopener noreferrer';
                el.textContent = opt.label;
            } else {
                el = document.createElement('button');
                el.textContent = opt.label;
                el.onclick = opt.onClick;
            }
            el.className = 'chat-option-btn';
            el.dataset.option = opt.value;
            optionsDiv.appendChild(el);
        });
        chatBody.appendChild(optionsDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    // Limpiar opciones anteriores (opcional)
    function removeOldOptions() {
        chatBody.querySelectorAll('.chat-options').forEach(el => el.remove());
    }

    // Opciones de ayuda iniciales
    document.querySelectorAll('.chat-option-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const option = this.dataset.option;
            addUserMessage(this.textContent);
            removeOldOptions();

            if(option === 'ayuda') {
                addBotMessage('<div class="message-container">\
                    <img src="{{ asset('frontend/images/logo/AVIAzul-Marino.png') }}" alt="Avatar" class="avatar" />\
                    <div class="messages">\
                        <div class="message">Claro estamos para ayudarte. Contáctanos a través de estos medios para una atención mas personalizada.</div>\
                    </div>\
                </div>');
                addOptions([
                    { label: 'Whatsapp', value: 'whatsapp', href: 'https://wa.link/f28njw' },
                    { label: 'Correo', value: 'correo', href: 'mailto:product.manager@macdelnorte.com' },
                    { label: 'Llamada', value: 'llamada', href: 'tel:8124738768' }
                ]);
            }
            // Puedes agregar más condicionales para otras opciones aquí
            if(option === 'comprar') {
                addBotMessage('<div class="message-container">\
                    <img src="{{ asset('frontend/images/logo/AVIAzul-Marino.png') }}" alt="Avatar" class="avatar" />\
                    <div class="messages">\
                        <div class="message">¡Perfecto! Un asesor te contactará para ayudarte con tu compra.</div>\
                    </div>\
                </div>');
                addOptions([
                    { label: 'Whatsapp', value: 'whatsapp', href: 'https://wa.link/f28njw' },
                    { label: 'Correo', value: 'correo', href: 'mailto:product.manager@macdelnorte.com' },
                    { label: 'Llamada', value: 'llamada', href: 'tel:8124738768' }
                ]);
            }
            if(option === 'soporte') {
                addBotMessage('<div class="message-container">\
                    <img src="{{ asset('frontend/images/logo/AVIAzul-Marino.png') }}" alt="Avatar" class="avatar" />\
                    <div class="messages">\
                        <div class="message">Por favor describe tu problema y un especialista te asistirá.</div>\
                    </div>\
                </div>');
                addOptions([
                    { label: 'Whatsapp', value: 'whatsapp', href: 'https://wa.link/f28njw' },
                    { label: 'Correo', value: 'correo', href: 'mailto:product.manager@macdelnorte.com' },
                    { label: 'Llamada', value: 'llamada', href: 'tel:8124738768' }
                ]);
            }
        });
    });
});
</script>
{{-- <script>
document.addEventListener("DOMContentLoaded", () => {
    const helpAlert = document.getElementById("help-alert");
    const closeAlertButton = document.getElementById("close-alert");
    const floatingImage = document.getElementById("floating-image");
    const chatWindow = document.getElementById("chat-window");
    const closeChatButton = document.getElementById("close-chat");
    const chatBody = chatWindow.querySelector('.chat-body');

    let helpAlertClicked = false;

    // Mostrar la alerta después de 10 segundos
    setTimeout(() => {
        if (!helpAlertClicked) {
            helpAlert.style.display = "block";
        }
    }, 10000);

    // Cerrar la alerta al hacer clic en la "X"
    closeAlertButton.addEventListener("click", () => {
        helpAlert.style.display = "none";
    });

    // Alternar la ventana de chat al hacer clic en la imagen flotante
    floatingImage.addEventListener("click", () => {
        helpAlert.style.display = "none";
        helpAlertClicked = true;

        if (chatWindow.style.display === "block") {
            chatWindow.style.display = "none";
        } else {
            chatWindow.style.display = "block";
        }
    });

    // Abrir el chat directamente si se hace clic en help-alert
    helpAlert.addEventListener("click", () => {
        helpAlert.style.display = "none";
        chatWindow.style.display = "block";
        helpAlertClicked = true;
    });

    // Cerrar el chat al hacer clic en la "X" del chat
    closeChatButton.addEventListener("click", () => {
        chatWindow.style.display = "none";
    });

    // Función para agregar mensaje del usuario al chat
    function addUserMessage(text) {
        const userMsgDiv = document.createElement('div');
        userMsgDiv.className = 'message user-message';
        userMsgDiv.textContent = text;
        chatBody.appendChild(userMsgDiv);
        chatBody.scrollTop = chatBody.scrollHeight; // Scroll al final
    }

    // Función para agregar mensaje del bot al chat
    function addBotMessage(html) {
        const botMsgDiv = document.createElement('div');
        botMsgDiv.innerHTML = html;
        chatBody.appendChild(botMsgDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    // Función para crear nuevas opciones
    function addOptions(options) {
        const optionsDiv = document.createElement('div');
        optionsDiv.className = 'chat-options';
        options.forEach(opt => {
            const btn = document.createElement('button');
            btn.className = 'chat-option-btn';
            btn.textContent = opt.label;
            btn.dataset.option = opt.value;
            btn.onclick = opt.onClick;
            optionsDiv.appendChild(btn);
        });
        chatBody.appendChild(optionsDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    // Limpiar opciones anteriores (opcional)
    function removeOldOptions() {
        chatBody.querySelectorAll('.chat-options').forEach(el => el.remove());
    }

    // Opciones de ayuda
    document.querySelectorAll('.chat-option-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const option = this.dataset.option;
            addUserMessage(this.textContent);
            removeOldOptions();

            if(option === 'ayuda') {
                addBotMessage('<div class="chat-body"><div class="message-container"> <img src="{{ asset('frontend/images/logo/AVIAzul-Marino.png') }}" alt="Avatar" class="avatar" /> <div class="messages"> <div class="message">Claro estamos para ayudarte. Contáctanos a través de estos medios</div> </div> </div></div>');
                addOptions([
                    { label: 'Whatsapp', value: 'whatsapp', onClick: function() { addUserMessage('Whatsapp'); } },
                    { label: 'Correo', value: 'correo', onClick: function() { addUserMessage('Correo'); } },
                    { label: 'Llamada', value: 'llamada', onClick: function() { addUserMessage('Llamada'); } }
                ]);
            }
            // Puedes agregar más condicionales para otras opciones aquí
        });
    });
});
</script> --}}
