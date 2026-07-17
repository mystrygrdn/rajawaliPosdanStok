import './bootstrap';

import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/inter/700.css';
import '@fontsource/inter/800.css';
import '@fontsource/inter/900.css';
import '@fontsource/jetbrains-mono/500.css';
import '@fontsource/jetbrains-mono/700.css';

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
window.Swal = Swal;

import Chart from 'chart.js/auto';
window.Chart = Chart;

import { jsPDF } from 'jspdf';
window.jspdf = { jsPDF };

window.openModal = function (id) {
    const modal = document.getElementById(id);
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
    requestAnimationFrame(function () {
        requestAnimationFrame(function () { modal.classList.add('modal-show'); });
    });
    const panel = modal.querySelector('.modal-card');
    if (panel) panel.focus();
};

window.closeModal = function (id) {
    const modal = document.getElementById(id);
    modal.classList.remove('modal-show');
    document.body.classList.remove('overflow-hidden');
    window.setTimeout(function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 180);
};