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
    console.log('Comprobando botones de exportación en el DOM');

    // Buscar todos los botones que tengan wire:click="exportarExcel"
    const exportButtons = document.querySelectorAll('button[wire\\:click="exportarExcel"]');
    if (exportButtons.length > 0) {
        console.log(`Encontrados ${exportButtons.length} botones de exportación Excel`);

        // Añadir listeners a cada botón encontrado
        exportButtons.forEach((btn, index) => {
            // Evitar duplicar event listeners usando un atributo de datos
            if (!btn.dataset.initialized) {
                btn.dataset.initialized = 'true';
                btn.addEventListener('click', function(e) {
                    console.log(`Botón exportar Excel #${index + 1} clicado manualmente`);
                    // Esto es sólo para depuración, el wire:click sigue funcionando normalmente
                });
                console.log(`Inicializado botón #${index + 1}:`, btn.textContent.trim());
            }
        });
    } else {
        console.log('No se encontraron botones de exportación en el DOM actual');
    }

    // Buscar botones del modal de exportación
    const showExportModalBtn = document.querySelector('button[wire\\:click="showExportModal"]');
    if (showExportModalBtn && !showExportModalBtn.dataset.initialized) {
        showExportModalBtn.dataset.initialized = 'true';
        showExportModalBtn.addEventListener('click', function(e) {
            console.log('Botón showExportModal clicado manualmente');
            // Registro adicional para depuración
            setTimeout(() => {
                const exportModal = document.querySelector('[x-data]');
                if (exportModal) {
                    console.log('Estado del modal después del clic:', exportModal.classList.contains('hidden') ? 'oculto' : 'visible');
                }
            }, 500);
        });
        console.log('Inicializado botón showExportModal:', showExportModalBtn.textContent.trim());
    }

    // Buscar botones de exportación múltiple
    const exportMultipleBtn = document.querySelector('button[wire\\:click="exportarMultiplesEvaluaciones"]');
    if (exportMultipleBtn && !exportMultipleBtn.dataset.initialized) {
        exportMultipleBtn.dataset.initialized = 'true';
        exportMultipleBtn.addEventListener('click', function(e) {
            console.log('Botón exportarMultiplesEvaluaciones clicado manualmente');
        });
        console.log('Inicializado botón exportarMultiplesEvaluaciones:', exportMultipleBtn.textContent.trim());
    }
}

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado - inicializando eventos');

    // Primera comprobación inmediata
    initializeExportButtons();

    // Añadir escuchador específico para el botón de exportación múltiple
    document.addEventListener('click', function(e) {
        // Buscar botones específicos relacionados con exportación
        let target = e.target.closest('button[wire\\:click="showExportModal"]');
        if (target) {
            console.log('Botón showExportModal detectado y clicado');
            // El evento wire:click de Livewire se encargará de la acción
            console.log('Verificando estado del modal inmediato...');
        }

        // Detectar botón exportar múltiples evaluaciones
        target = e.target.closest('button[wire\\:click="exportarMultiplesEvaluaciones"]');
        if (target) {
            console.log('Botón exportarMultiplesEvaluaciones detectado y clicado');
            // El evento wire:click de Livewire se encargará de la acción
        }
    });

    // Escuchar eventos Livewire específicos
    if (typeof window.Livewire !== 'undefined') {
        console.log('Registrando oyentes para eventos Livewire');

        // Añadir manejador para el evento confirmar-exportacion
        window.addEventListener('confirmar-exportacion', event => {
            console.log('Evento confirmar-exportacion recibido:', event.detail);

            const mensaje = event.detail.mensaje || '¿Deseas exportar estas evaluaciones?';
            const confirmacion = confirm(mensaje);

            console.log('Respuesta de confirmación:', confirmacion);

            if (confirmacion) {
                console.log('Usuario confirmó la exportación - llamando a exportarMultiplesEvaluaciones');
                // Llamar directamente al método de Livewire
                if (typeof window.Livewire !== 'undefined') {
                    window.Livewire.dispatch('exportarMultiplesEvaluaciones');
                } else {
                    console.error('Livewire no está disponible');
                    alert('Error al iniciar la exportación: Livewire no está disponible');
                }
            } else {
                console.log('Usuario canceló la exportación');
            }
        });

        window.addEventListener('show-export-modal', event => {
            console.log('Evento Livewire show-export-modal recibido');
        });

        window.addEventListener('hide-export-modal', event => {
            console.log('Evento Livewire hide-export-modal recibido');
        });
    } else {
        console.warn('Livewire no detectado en la página');
    }

    // Observar todo el body para detectar cambios
    const observer = new MutationObserver(function(mutations) {
        for (const mutation of mutations) {
            if (mutation.type === 'childList' && mutation.addedNodes.length) {
                // Verificar si alguno de los nodos añadidos contiene un botón de exportación
                const hasRelevantChanges = Array.from(mutation.addedNodes).some(node => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Buscar en el nodo añadido o sus descendientes
                        return node.querySelector && (
                            node.querySelector('button[wire\\:click="exportarExcel"]') ||
                            node.matches && node.matches('button[wire\\:click="exportarExcel"]')
                        );
                    }
                    return false;
                });

                if (hasRelevantChanges) {
                    console.log('Detectados cambios relevantes en el DOM - reinicializando botones');
                    initializeExportButtons();
                }
            }
        }
    });

    // Observar todo el body para detectar cambios
    observer.observe(document.body, { childList: true, subtree: true });

    // También inicializar un escuchador global para los botones de Livewire
    document.addEventListener('click', function(e) {
        if (e.target.closest('button[wire\\:click]')) {
            const wireClickValue = e.target.closest('button[wire\\:click]').getAttribute('wire:click');
            console.log('Botón con wire:click detectado:', wireClickValue);
        }
    });
});

// Evento para SweetAlert genérico
window.addEventListener('swal', event => {
    console.log('Evento swal recibido:', event.detail);
    const options = event.detail[0] || event.detail;

    Swal.fire(options);
});

// Evento para mostrar el modal de exportación directamente con SweetAlert2
window.addEventListener('mostrar-modal-exportacion', event => {
    console.log('Evento mostrar-modal-exportacion recibido:', event.detail);

    const numEvaluaciones = event.detail.evaluaciones || 0;
    const trialMode = document.body.classList.contains('trial-mode');

    console.log(`Mostrando modal de exportación para ${numEvaluaciones} evaluaciones`);

    // Crear HTML para el modal
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

    // Mostrar modal con SweetAlert2
    Swal.fire({
        title: 'Exportar Diferentes Campos Formativos',
        html: html,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Exportar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10B981', // verde
        customClass: {
            confirmButton: 'bg-green-600 hover:bg-green-700',
            cancelButton: 'bg-gray-200 hover:bg-gray-300 text-gray-700'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Usuario confirmó exportación - llamando a exportarMultiplesEvaluaciones');
            if (typeof window.Livewire !== 'undefined') {
                window.Livewire.dispatch('exportarMultiplesEvaluaciones');
            } else {
                console.error('Livewire no está disponible');
                Toast.fire({
                    icon: 'error',
                    title: 'Error al iniciar la exportación'
                });
            }
        } else {
            console.log('Usuario canceló la exportación');
            if (typeof window.Livewire !== 'undefined') {
                window.Livewire.dispatch('cancelExport');
            }
        }
    });
});

// Evento para el manejo de alertas en usuarios Trial - PDF
window.addEventListener('trial-feature-disabled', event => {
    console.log('Evento trial-feature-disabled recibido');

    // Usar título y mensaje personalizados si se proporcionan, o valores predeterminados si no
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
    console.log('Evento trial-excel-export recibido - mostrando diálogo de confirmación');
    Swal.fire({
        title: 'Exportación limitada',
        text: 'En modo Trial solo puedes exportar hasta 10 registros. ¿Deseas continuar?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, exportar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#4f46e5',
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Usuario confirmó exportación - enviando evento confirmarExportarExcel');
            // Actualizado: usar el nuevo sistema de eventos de Livewire v3
            if (typeof window.Livewire !== 'undefined') {
                console.log('Llamando a Livewire.dispatch("confirmarExportarExcel")');
                window.Livewire.dispatch('confirmarExportarExcel');
            } else {
                console.error('Livewire no está disponible');
                Toast.fire({
                    icon: 'error',
                    title: 'Error al iniciar la exportación'
                });
            }
        } else {
            console.log('Usuario canceló la exportación');
        }
    });
});

// Para notificaciones genéricas de Livewire
window.addEventListener('notify', event => {
    console.log('Evento notify recibido:', event.detail);
    const detail = event.detail;

    // Extraer type y message, manejando ambos órdenes posibles
    let type = detail.type || 'info';
    let message = detail.message || '';

    // Si message está vacío pero hay un mensaje en detail, usarlo
    if (!message && typeof detail === 'string') {
        message = detail;
    }

    // Asegurarse de que type es válido para SweetAlert2
    if (!['success', 'error', 'warning', 'info', 'question'].includes(type)) {
        type = 'info';
    }

    console.log('Mostrando toast con:', { type, message });

    // Mostrar toast solo si hay un mensaje
    if (message) {
        Toast.fire({
            icon: type,
            title: message
        });
    } else {
        console.warn('Toast no mostrado: mensaje vacío');
    }
});

// Evento para mostrar confirmación de envío de SMS a padres/tutores
window.addEventListener('mostrar-confirmacion-sms', event => {
    console.log('Evento mostrar-confirmacion-sms recibido:', event.detail);

    // El formato que está llegando parece ser un array con un objeto que tiene la propiedad alumnos
    let alumnos = [];

    // Obtener directamente los datos del formato específico que estamos recibiendo
    if (event.detail && Array.isArray(event.detail) && event.detail.length > 0 && event.detail[0].alumnos) {
        alumnos = event.detail[0].alumnos;
        console.log('Formato detectado: Array con objeto que contiene alumnos');
    } else if (event.detail && event.detail.alumnos) {
        // Alternativa por si viene directamente como objeto
        alumnos = event.detail.alumnos;
        console.log('Formato detectado: Objeto con propiedad alumnos');
    } else if (Array.isArray(event.detail)) {
        // Si viene como array directamente
        alumnos = event.detail;
        console.log('Formato detectado: Array directo');
    }

    console.log('Alumnos extraídos:', alumnos);

    if (!alumnos || !Array.isArray(alumnos) || alumnos.length === 0) {
        console.warn('No hay alumnos con falta para enviar SMS o formato incorrecto');
        Toast.fire({
            icon: 'warning',
            title: 'No hay alumnos con faltas para notificar por SMS'
        });
        return;
    }

    console.log(`Se encontraron ${alumnos.length} alumnos con faltas para enviar SMS`);

    // Crear lista de alumnos para mostrar en el mensaje
    let listaAlumnos = '';
    alumnos.forEach(alumno => {
        const nombre = alumno.nombre || (alumno.id ? `Alumno ID ${alumno.id}` : 'Alumno sin nombre');
        listaAlumnos += `<li>${nombre}</li>`;
        console.log(`Alumno con falta: ${nombre}, teléfono tutor: ${alumno.telefono_tutor || 'No disponible'}`);
    });

    console.log('Mostrando diálogo de confirmación de SMS');

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
        console.log('Resultado del diálogo:', result);
        if (result.isConfirmed) {
            console.log('Usuario confirmó envío de SMS - despachando evento confirmarEnvioSMS');
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
            console.log('Usuario canceló el envío de SMS');
            Toast.fire({
                icon: 'info',
                title: 'Envío de SMS cancelado'
            });
        }
    });
});

// Evento para mostrar el resultado del envío de SMS
window.addEventListener('mostrar-resultado-sms', event => {
    console.log('Evento mostrar-resultado-sms recibido:', event.detail);
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
    console.log('Inicializando botones de exportación...');

    // Manejar clics en botones de exportación
    document.addEventListener('click', function(e) {
        // Para el botón con ID específico
        if (e.target && e.target.id === 'exportarBotonSimple') {
            console.log('Botón exportarBotonSimple clicado');
        }

        // Para el botón con onclick
        if (e.target && (e.target.matches('button[onclick*="exportarEvaluacionesSeleccionadas"]'))) {
            console.log('Botón de exportarEvaluacionesSeleccionadas detectado');
        }

        // Para el botón de showExportModal
        if (e.target && e.target.getAttribute('wire:click') === 'showExportModal') {
            console.log('Botón showExportModal clicado');
        }

        // Para el botón de exportarMultiplesEvaluaciones
        if (e.target && e.target.getAttribute('wire:click') === 'exportarMultiplesEvaluaciones') {
            console.log('Botón exportarMultiplesEvaluaciones clicado');
        }
    });

    // Agregar manejador global para el evento personalizado exportarSimple
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM totalmente cargado, configurando manejadores adicionales...');

        // Definir una función global para exportarEvaluacionesSeleccionadas
        window.exportarEvaluacionesSeleccionadas = function() {
            console.log('Función global exportarEvaluacionesSeleccionadas llamada');
            // Esta función será reemplazada por la versión definida en el componente,
            // pero sirve como respaldo
        };
    });
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, inicializando componentes...');
    initExportButtons();
});

// Observar cambios en el DOM para reinicializar botones cuando sea necesario
const observer = new MutationObserver(function(mutations) {
    console.log('Cambios en el DOM detectados, reinicializando botones...');
    initExportButtons();
});

// Observar todo el body para cambios
observer.observe(document.body, {
    childList: true,
    subtree: true
});
