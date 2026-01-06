// This file contains the JavaScript code associated with the landing page. 
// It may include functionality for interactivity, animations, or other dynamic features.

document.addEventListener('DOMContentLoaded', function() {
    // Example of a simple interactive feature
    const button = document.getElementById('cta-button');
    if (button) {
        button.addEventListener('click', function() {
            alert('Button clicked! Welcome to our landing page!');
        });
    }

    // ========================================================================
    // FORMULARIO DE CONTACTO - Envío a API externa
    // ========================================================================
    const contactForm = document.getElementById('contact-form');
    const formStatus = document.getElementById('form-status');
    
    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Limpiar errores previos
            clearErrors();
            
            const submitBtn = document.getElementById('submit-form-btn');
            const originalBtnText = submitBtn.textContent;
            
            // Obtener datos del formulario
            const formData = {
                name: contactForm.querySelector('#name').value.trim(),
                email_address: contactForm.querySelector('#email_address').value.trim(),
                subject: contactForm.querySelector('#subject').value.trim(),
                phone: contactForm.querySelector('#phone').value.trim(),
                message: contactForm.querySelector('#message').value.trim()
            };
            
            // Validación del lado del cliente
            let hasErrors = false;
            
            if (!formData.name) {
                showFieldError('name', 'El nombre es requerido');
                hasErrors = true;
            }
            
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!formData.email_address) {
                showFieldError('email_address', 'El email es requerido');
                hasErrors = true;
            } else if (!emailRegex.test(formData.email_address)) {
                showFieldError('email_address', 'Ingresa un email válido');
                hasErrors = true;
            }
            
            if (!formData.phone) {
                showFieldError('phone', 'El teléfono es requerido');
                hasErrors = true;
            }
            
            if (!formData.message) {
                showFieldError('message', 'El mensaje es requerido');
                hasErrors = true;
            }
            
            if (hasErrors) return;
            
            // Deshabilitar botón y mostrar estado de carga
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';
            showFormStatus('Enviando mensaje...', 'loading');
            
            try {
                const response = await fetch(contactForm.querySelector('form').action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (response.ok && result.success !== false) {
                    showFormStatus('¡Mensaje enviado correctamente! Nos pondremos en contacto pronto.', 'success');
                    contactForm.reset();
                } else {
                    showFormStatus(result.message || 'Error al enviar el mensaje.', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showFormStatus('Error de conexión. Por favor, intenta nuevamente.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        });
    }
    
    /**
     * Muestra error en un campo específico
     */
    function showFieldError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const errorSpan = document.getElementById(`${fieldId}-error`);
        if (field) field.classList.add('error');
        if (errorSpan) errorSpan.textContent = message;
    }
    
    /**
     * Limpia todos los errores del formulario
     */
    function clearErrors() {
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(el => el.textContent = '');
        
        const errorFields = document.querySelectorAll('.contact-input-field.error');
        errorFields.forEach(el => el.classList.remove('error'));
        
        if (formStatus) {
            formStatus.textContent = '';
            formStatus.className = 'contact-section-text';
        }
    }
    
    /**
     * Muestra mensaje de estado del formulario
     */
    function showFormStatus(message, type) {
        if (formStatus) {
            formStatus.textContent = message;
            formStatus.className = `contact-section-text ${type}`;
        }
    }
});