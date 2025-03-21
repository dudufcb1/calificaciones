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

// Evento para el manejo de alertas en usuarios Trial - PDF
window.addEventListener('trial-feature-disabled', event => {
    Swal.fire({
        title: 'Función no disponible',
        text: 'La exportación a PDF no está disponible para usuarios en modo prueba. Actualice a la versión completa para acceder a esta funcionalidad.',
        icon: 'info',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#4f46e5',
    });
});

// Evento para confirmar exportación a Excel en modo Trial
window.addEventListener('trial-excel-export', event => {
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
            // Llamar al método del componente Livewire
            if (typeof window.Livewire !== 'undefined') {
                window.Livewire.dispatch('confirmarExportarExcel');
            }
        }
    });
});

// Para notificaciones genéricas de Livewire
window.addEventListener('notify', event => {
    const { type, message } = event.detail;

    Toast.fire({
        icon: type,
        title: message
    });
});
