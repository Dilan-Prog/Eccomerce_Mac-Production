{{-- <div id="floating-image">
    <img src="{{ asset('frontend/images/logo/AVIAzul-Marino.png') }}" alt="Chatbot" />
</div>
<div id="help-alert">
    <button id="close-alert">&times;</button>
    <div class="container-help-alert">
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
            </div>
        </div>
    </div>
    <div class="chat-footer">
        <input type="text" placeholder="Escribe tu mensaje aquí..." />
        <button>Enviar</button>
    </div>
</div>

<script>
        document.addEventListener("DOMContentLoaded", () => {
        const helpAlert = document.getElementById("help-alert");
        const closeAlertButton = document.getElementById("close-alert");
        const floatingImage = document.getElementById("floating-image");
        const chatWindow = document.getElementById("chat-window");
        const closeChatButton = document.getElementById("close-chat");

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
    });
</script> --}}



QUE SE HABRA SOLCITO LA PRIMERA VEZ QUE CARGA
