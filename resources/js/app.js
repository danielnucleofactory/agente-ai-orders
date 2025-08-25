import "./bootstrap";
import Sortable from "sortablejs";

window.Sortable = Sortable;

// Solo restaurar el estado guardado cuando carga la página
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.main-sidebar');
    if (!sidebar) return;

    // Aplicar el estado guardado
    if (localStorage.getItem('sidebarExpanded') === 'true') {
        sidebar.classList.add('sidebar-expanded');
    } else {
        sidebar.classList.remove('sidebar-expanded');
    }

    // Función global para compatibilidad
    window.toggleSidebarSimple = function() {
        sidebar.classList.toggle('sidebar-expanded');
        localStorage.setItem('sidebarExpanded', sidebar.classList.contains('sidebar-expanded'));
    };
});
