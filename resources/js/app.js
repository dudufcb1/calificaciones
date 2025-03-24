import './bootstrap';
import Swal from 'sweetalert2';

// Configuración global de SweetAlert2
window.Swal = Swal;

// Toast para notificaciones
window.Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

// Función para inicializar botones en el DOM
function initializeExportButtons() {
    const exportButtons = document.querySelectorAll('button[wire\\:click="exportarExcel"]');
    if (exportButtons.length > 0) {
        exportButtons.forEach((btn, index) => {
            if (!btn.dataset.initialized) {
                btn.dataset.initialized = 'true';
                btn.addEventListener('click', function(e) {
                    // Event handled by wire:click
                });
            }
        });
    }

    const showExportModalBtn = document.querySelector('button[wire\\:click="showExportModal"]');
    if (showExportModalBtn && !showExportModalBtn.dataset.initialized) {
        showExportModalBtn.dataset.initialized = 'true';
        showExportModalBtn.addEventListener('click', function(e) {
            // Event handled by wire:click
        });
    }

    const exportMultipleBtn = document.querySelector('button[wire\\:click="exportarMultiplesEvaluaciones"]');
    if (exportMultipleBtn && !exportMultipleBtn.dataset.initialized) {
        exportMultipleBtn.dataset.initialized = 'true';
        exportMultipleBtn.addEventListener('click', function(e) {
            // Event handled by wire:click
        });
    }
}

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    initializeExportButtons();

    document.addEventListener('click', function(e) {
        let target = e.target.closest('button[wire\\:click="showExportModal"]');
        if (target) {
            // Event handled by wire:click
        }

        target = e.target.closest('button[wire\\:click="exportarMultiplesEvaluaciones"]');
        if (target) {
            // Event handled by wire:click
        }
    });

    if (typeof window.Livewire !== 'undefined') {
        window.addEventListener('confirmar-exportacion', event => {
            const mensaje = event.detail.mensaje || '¿Deseas exportar estas evaluaciones?';
            const confirmacion = confirm(mensaje);

            if (confirmacion) {
                if (typeof window.Livewire !== 'undefined') {
                    window.Livewire.dispatch('exportarMultiplesEvaluaciones');
                } else {
                    alert('Error al iniciar la exportación: Livewire no está disponible');
                }
            }
        });

        window.addEventListener('show-export-modal', event => {
            // Event handled by Livewire
        });

        window.addEventListener('hide-export-modal', event => {
            // Event handled by Livewire
        });
    }

    const observer = new MutationObserver(function(mutations) {
        for (const mutation of mutations) {
            if (mutation.type === 'childList' && mutation.addedNodes.length) {
                const hasRelevantChanges = Array.from(mutation.addedNodes).some(node => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        return node.querySelector && (
                            node.querySelector('button[wire\\:click="exportarExcel"]') ||
                            node.matches && node.matches('button[wire\\:click="exportarExcel"]')
                        );
                    }
                    return false;
                });

                if (hasRelevantChanges) {
                    initializeExportButtons();
                }
            }
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });

    document.addEventListener('click', function(e) {
        if (e.target.closest('button[wire\\:click]')) {
            // Event handled by wire:click
        }
    });
});

// Evento para SweetAlert genérico
window.addEventListener('swal', event => {
    const options = event.detail[0] || event.detail;
    Swal.fire(options);
});

// Evento para mostrar el modal de exportación directamente con SweetAlert2
window.addEventListener('mostrar-modal-exportacion', event => {
    const numEvaluaciones = event.detail.evaluaciones || 0;
    const trialMode = document.body.classList.contains('trial-mode');

    let html = `
        <p class="mb-4">Estás a punto de exportar ${numEvaluaciones} evaluaciones en un solo archivo Excel.
        Cada evaluación tendrá su propia hoja en el archivo.</p>
    `;

    if (trialMode) {
        html += `
            <div class="p-2 bg-red-100 text-red-700 rounded mb-4">
                <p>Esta función solo está disponible para usuarios con membresía premium.</p>
            </div>
        `;
    }

    Swal.fire({
        title: 'Exportar Diferentes Campos Formativos',
        html: html,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Exportar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10B981',
        customClass: {
            confirmButton: 'bg-green-600 hover:bg-green-700',
            cancelButton: 'bg-gray-200 hover:bg-gray-300 text-gray-700'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            if (typeof window.Livewire !== 'undefined') {
                window.Livewire.dispatch('exportarMultiplesEvaluaciones');
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'Error al iniciar la exportación'
                });
            }
        } else {
            if (typeof window.Livewire !== 'undefined') {
                window.Livewire.dispatch('cancelExport');
            }
        }
    });
});

// Evento para el manejo de alertas en usuarios Trial - PDF
window.addEventListener('trial-feature-disabled', event => {
    const title = event.detail && event.detail.title ? event.detail.title : 'Función no disponible';
    const message = event.detail && event.detail.message
        ? event.detail.message
        : 'Esta función no está disponible para usuarios en modo prueba. Actualice a la versión completa para acceder a esta funcionalidad.';

    Swal.fire({
        title: title,
        text: message,
        icon: 'info',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#4f46e5',
    });
});

// Evento para confirmar exportación a Excel en modo Trial
window.addEventListener('trial-excel-export', event => {
    console.log('🔥 Evento trial-excel-export recibido - mostrando diálogo de confirmación');

    // Test directo de SweetAlert
    console.log('🧪 Testing SweetAlert availability:', typeof Swal);

    // Test inmediato de SweetAlert
    console.log('🚨 EJECUTANDO TEST INMEDIATO DE SWEETALERT');
    try {
        Swal.fire({
            title: 'TEST INMEDIATO',
            text: 'Si ves esto, SweetAlert funciona',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
        console.log('✅ Test inmediato ejecutado sin errores');
    } catch (error) {
        console.error('❌ Error en test inmediato:', error);
    }

    // Usar setTimeout para asegurar que el diálogo se muestre
    setTimeout(() => {
        console.log('⏰ Mostrando diálogo SweetAlert después de timeout');
        const swalConfig = {
            title: 'Versión de Prueba',
            text: 'Esta es una versión de prueba y se limita a solo 10 registros. Si quieres la versión completa debes adquirirla al 9616085491. ¿Deseas continuar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, exportar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4f46e5',
            backdrop: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                container: 'swal2-container-custom'
            }
        };

        console.log('🔧 Configuración SweetAlert:', swalConfig);

        Swal.fire(swalConfig).then((result) => {
        if (result.isConfirmed) {
            console.log('✅ Usuario confirmó exportación - enviando evento confirmarExportarExcel');
            // Actualizado: usar el nuevo sistema de eventos de Livewire v3
            if (typeof window.Livewire !== 'undefined') {
                console.log('📡 Llamando a Livewire.dispatch("confirmarExportarExcel")');
                window.Livewire.dispatch('confirmarExportarExcel');
            } else {
                console.error('❌ Livewire no está disponible');
                Toast.fire({
                    icon: 'error',
                    title: 'Error al iniciar la exportación'
                });
            }
        } else {
            console.log('❌ Usuario canceló la exportación');
        }
    });
    }, 100); // Timeout de 100ms
});

// Test de SweetAlert - se ejecuta al cargar la página
window.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM cargado, SweetAlert disponible:', typeof window.Swal);

    // Test simple después de 2 segundos
    setTimeout(() => {
        console.log('🧪 Ejecutando test de SweetAlert...');
        if (typeof window.Swal !== 'undefined') {
            console.log('✅ SweetAlert está disponible');
        } else {
            console.error('❌ SweetAlert NO está disponible');
        }
    }, 2000);
});

// Para notificaciones genéricas de Livewire
window.addEventListener('notify', event => {
    console.log('Evento notify recibido:', event.detail);
    let detail = event.detail;

    // Si detail es un array, tomar el primer elemento
    if (Array.isArray(detail) && detail.length > 0) {
        detail = detail[0];
        console.log('Detail extraído del array:', detail);
    }

    // Extraer type y message, manejando ambos órdenes posibles
    let type = detail.type || 'info';
    let message = detail.message || '';

    // Si message está vacío pero hay un mensaje en detail, usarlo
    if (!message && typeof detail === 'string') {
        message = detail;
    }

    // Si aún no hay mensaje, verificar si hay otros campos útiles
    if (!message) {
        if (detail.error) {
            message = detail.error;
            type = 'error';
        } else if (detail.warning) {
            message = detail.warning;
            type = 'warning';
        } else if (detail.info) {
            message = detail.info;
            type = 'info';
        }
    }

    // Asegurarse de que type es válido para SweetAlert2
    if (!['success', 'error', 'warning', 'info', 'question'].includes(type)) {
        type = 'info';
    }

    console.log('Mostrando toast con:', { type, message });

    // Mostrar toast solo si hay un mensaje
    if (message && message.trim() !== '') {
        Toast.fire({
            icon: type,
            title: message
        });
    } else {
        console.warn('Toast no mostrado: mensaje vacío o inválido', { detail, type, message });
        // Mostrar un mensaje genérico para errores sin mensaje específico
        if (type === 'error') {
            Toast.fire({
                icon: 'error',
                title: 'Ha ocurrido un error. Revise la consola para más detalles.'
            });
        }
    }
});

// Evento para mostrar confirmación de envío de SMS a padres/tutores
window.addEventListener('mostrar-confirmacion-sms', event => {
    // console.log('Evento mostrar-confirmacion-sms recibido:', event.detail);

    // El formato que está llegando parece ser un array con un objeto que tiene la propiedad alumnos
    let alumnos = [];

    // Obtener directamente los datos del formato específico que estamos recibiendo
    if (event.detail && Array.isArray(event.detail) && event.detail.length > 0 && event.detail[0].alumnos) {
        alumnos = event.detail[0].alumnos;
        // console.log('Formato detectado: Array con objeto que contiene alumnos');
    } else if (event.detail && event.detail.alumnos) {
        // Alternativa por si viene directamente como objeto
        alumnos = event.detail.alumnos;
        // console.log('Formato detectado: Objeto con propiedad alumnos');
    } else if (Array.isArray(event.detail)) {
        // Si viene como array directamente
        alumnos = event.detail;
        // console.log('Formato detectado: Array directo');
    }

    // console.log('Alumnos extraídos:', alumnos);

    if (!alumnos || !Array.isArray(alumnos) || alumnos.length === 0) {
        console.warn('No hay alumnos con falta para enviar SMS o formato incorrecto');
        Toast.fire({
            icon: 'warning',
            title: 'No hay alumnos con faltas para notificar por SMS'
        });
        return;
    }

    // console.log(`Se encontraron ${alumnos.length} alumnos con faltas para enviar SMS`);

    // Crear lista de alumnos para mostrar en el mensaje
    let listaAlumnos = '';
    alumnos.forEach(alumno => {
        const nombre = alumno.nombre || (alumno.id ? `Alumno ID ${alumno.id}` : 'Alumno sin nombre');
        listaAlumnos += `<li>${nombre}</li>`;
        // console.log(`Alumno con falta: ${nombre}, teléfono tutor: ${alumno.telefono_tutor || 'No disponible'}`);
    });

    // console.log('Mostrando diálogo de confirmación de SMS');

    // Forzar el diálogo sin retrasos
    Swal.fire({
        title: 'Notificar Faltas por SMS',
        html: `
            <p>Los siguientes alumnos han faltado:</p>
            <ul class="text-left">${listaAlumnos}</ul>
            <p>¿Deseas enviar un SMS a sus padres/tutores?</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar SMS',
        cancelButtonText: 'No, gracias',
        confirmButtonColor: '#4f46e5',
    }).then((result) => {
        // console.log('Resultado del diálogo:', result);
        if (result.isConfirmed) {
            // console.log('Usuario confirmó envío de SMS - despachando evento confirmarEnvioSMS');
            if (typeof window.Livewire !== 'undefined') {
                window.Livewire.dispatch('confirmarEnvioSMS');
                Toast.fire({
                    icon: 'info',
                    title: 'Enviando mensajes SMS...'
                });
            } else {
                console.error('Livewire no está disponible');
                Toast.fire({
                    icon: 'error',
                    title: 'Error al iniciar el envío de SMS'
                });
            }
        } else {
            // console.log('Usuario canceló el envío de SMS');
            Toast.fire({
                icon: 'info',
                title: 'Envío de SMS cancelado'
            });
        }
    });
});

// Evento para mostrar el resultado del envío de SMS
window.addEventListener('mostrar-resultado-sms', event => {
    // console.log('Evento mostrar-resultado-sms recibido:', event.detail);
    const { exito, errores, detalles } = event.detail;

    let mensaje = '';
    let icon = 'info';

    if (exito > 0 && errores === 0) {
        mensaje = `<p>Todos los mensajes se enviaron correctamente:</p>`;
        icon = 'success';
    } else if (exito > 0 && errores > 0) {
        mensaje = `<p>Se enviaron ${exito} mensajes correctamente, pero hubo ${errores} errores:</p>`;
        icon = 'warning';
    } else {
        mensaje = `<p>No se pudo enviar ningún mensaje. Se encontraron ${errores} errores:</p>`;
        icon = 'error';
    }

    // Agregar detalles al mensaje
    mensaje += '<ul class="text-left mt-3" style="max-height: 300px; overflow-y: auto;">';
    detalles.forEach(detalle => {
        mensaje += `<li class="mb-1">${detalle}</li>`;
    });
    mensaje += '</ul>';

    // Mostrar modal con detalles
    Swal.fire({
        title: 'Resultado del envío de SMS',
        html: mensaje,
        icon: icon,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#4f46e5',
    });
});

// Inicializar botones de exportación
function initExportButtons() {
    // console.log('Inicializando botones de exportación...');

    // Manejar clics en botones de exportación
    document.addEventListener('click', function(e) {
        // Para el botón con ID específico
        if (e.target && e.target.id === 'exportarBotonSimple') {
            // console.log('Botón exportarBotonSimple clicado');
        }

        // Para el botón con onclick
        if (e.target && (e.target.matches('button[onclick*="exportarEvaluacionesSeleccionadas"]'))) {
            // console.log('Botón de exportarEvaluacionesSeleccionadas detectado');
        }

        // Para el botón de showExportModal
        if (e.target && e.target.getAttribute('wire:click') === 'showExportModal') {
            // console.log('Botón showExportModal clicado');
        }

        // Para el botón de exportarMultiplesEvaluaciones
        if (e.target && e.target.getAttribute('wire:click') === 'exportarMultiplesEvaluaciones') {
            // console.log('Botón exportarMultiplesEvaluaciones clicado');
        }
    });

    // Agregar manejador global para el evento personalizado exportarSimple
    document.addEventListener('DOMContentLoaded', function() {
        // console.log('DOM totalmente cargado, configurando manejadores adicionales...');

        // Definir una función global para exportarEvaluacionesSeleccionadas
        window.exportarEvaluacionesSeleccionadas = function() {
            // console.log('Función global exportarEvaluacionesSeleccionadas llamada');
            // Esta función será reemplazada por la versión definida en el componente,
            // pero sirve como respaldo
        };
    });
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', function() {
    // console.log('DOM cargado, inicializando componentes...');
    initExportButtons();
});

// Observar cambios en el DOM para reinicializar botones cuando sea necesario
const observer = new MutationObserver(function(mutations) {
    // console.log('Cambios en el DOM detectados, reinicializando botones...');
    initExportButtons();
});

// Observar todo el body para cambios
observer.observe(document.body, {
    childList: true,
    subtree: true
});

// Add PDF download handler for Livewire
document.addEventListener('livewire:init', () => {
    // Handle PDF downloads
    Livewire.on('downloadPdf', (url) => {
        console.log('Initiating PDF download:', url);
        
        // Open in a new tab/window to avoid Livewire's JSON issues
        window.open(url, '_blank');
    });
});

// Direct event handler for PDF downloads (works without livewire:init)
window.addEventListener('downloadPdf', event => {
    try {
        const url = event.detail;
        console.log('PDF download event received:', url);
        
        // Fallback if event detail is in a different format
        const downloadUrl = typeof url === 'string' ? url : 
                          (event.detail && event.detail.url ? event.detail.url : null);
        
        if (!downloadUrl) {
            console.error('Invalid PDF download URL received:', event.detail);
            Toast.fire({
                icon: 'error',
                title: 'Error al iniciar la descarga del PDF'
            });
            return;
        }
        
        console.log('Opening PDF in new window:', downloadUrl);
        
        // Open in a new tab/window to avoid Livewire's JSON issues
        window.open(downloadUrl, '_blank');
        
        // Fallback for popup blockers
        setTimeout(() => {
            Toast.fire({
                icon: 'info',
                title: 'Si la descarga no inicia automáticamente, haga clic en "Aceptar" para intentar nuevamente',
                showConfirmButton: true,
                confirmButtonText: 'Aceptar'
            }).then(result => {
                if (result.isConfirmed) {
                    window.open(downloadUrl, '_blank');
                }
            });
        }, 1000);
    } catch (error) {
        console.error('Error processing PDF download:', error);
        Toast.fire({
            icon: 'error',
            title: 'Error al procesar la descarga del PDF'
        });
    }
});

// Handler for forwarding browser events from Livewire
window.addEventListener('browser-event', event => {
    try {
        const eventName = event.detail.name;
        const eventData = event.detail.data;
        
        console.log(`Received browser-event: ${eventName}`, eventData);
        
        // Create and dispatch a custom event with the same name
        const customEvent = new CustomEvent(eventName, {
            detail: eventData,
            bubbles: true,
            cancelable: true
        });
        
        console.log(`Forwarding to ${eventName} handler`, customEvent);
        window.dispatchEvent(customEvent);
    } catch (error) {
        console.error('Error forwarding browser event:', error);
    }
});
