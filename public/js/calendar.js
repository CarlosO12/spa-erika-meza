/**
 * JavaScript para Calendario
 * SPA Erika Meza
 */

class Calendar {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.currentDate = new Date();
        this.selectedDate = null;
        this.minDate = options.minDate || new Date();
        this.maxDate = options.maxDate || null;
        this.disabledDates = options.disabledDates || [];
        this.onDateSelect = options.onDateSelect || function() {};
        
        this.init();
    }
    
    init() {
        this.render();
        this.attachEventListeners();
    }
    
    render() {
        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        
        const monthNames = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];
        
        const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        
        let html = `
            <div class="calendar">
                <div class="calendar-header">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="prevMonth">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <h5 class="mb-0">${monthNames[month]} ${year}</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="nextMonth">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
                <div class="calendar-body">
                    <div class="calendar-days-header">
        `;
        
        // Nombres de días
        dayNames.forEach(day => {
            html += `<div class="calendar-day-name">${day}</div>`;
        });
        
        html += `</div><div class="calendar-days">`;
        
        // Obtener primer día del mes
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        // Días vacíos antes del primer día
        for (let i = 0; i < firstDay; i++) {
            html += `<div class="calendar-day empty"></div>`;
        }
        
        // Días del mes
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dateString = this.formatDate(date);
            const isDisabled = this.isDateDisabled(date);
            const isSelected = this.selectedDate && dateString === this.formatDate(this.selectedDate);
            const isToday = this.isToday(date);
            
            let classes = 'calendar-day';
            if (isDisabled) classes += ' disabled';
            if (isSelected) classes += ' selected';
            if (isToday) classes += ' today';
            
            html += `
                <div class="${classes}" data-date="${dateString}">
                    ${day}
                </div>
            `;
        }
        
        html += `</div></div></div>`;
        
        this.container.innerHTML = html;
    }
    
    attachEventListeners() {
        // Navegación entre meses
        const prevBtn = document.getElementById('prevMonth');
        const nextBtn = document.getElementById('nextMonth');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                this.currentDate.setMonth(this.currentDate.getMonth() - 1);
                this.render();
                this.attachEventListeners();
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                this.currentDate.setMonth(this.currentDate.getMonth() + 1);
                this.render();
                this.attachEventListeners();
            });
        }
        
        // Selección de fecha
        const days = this.container.querySelectorAll('.calendar-day:not(.empty):not(.disabled)');
        days.forEach(day => {
            day.addEventListener('click', () => {
                const dateString = day.dataset.date;
                this.selectedDate = this.parseDate(dateString);
                this.render();
                this.attachEventListeners();
                this.onDateSelect(dateString);
            });
        });
    }
    
    isDateDisabled(date) {
        // Verificar si está antes de la fecha mínima
        if (this.minDate && date < this.minDate) {
            return true;
        }
        
        // Verificar si está después de la fecha máxima
        if (this.maxDate && date > this.maxDate) {
            return true;
        }
        
        // Verificar si está en las fechas deshabilitadas
        const dateString = this.formatDate(date);
        if (this.disabledDates.includes(dateString)) {
            return true;
        }
        
        return false;
    }
    
    isToday(date) {
        const today = new Date();
        return date.getDate() === today.getDate() &&
               date.getMonth() === today.getMonth() &&
               date.getFullYear() === today.getFullYear();
    }
    
    formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    parseDate(dateString) {
        const [year, month, day] = dateString.split('-').map(Number);
        return new Date(year, month - 1, day);
    }
    
    getSelectedDate() {
        return this.selectedDate ? this.formatDate(this.selectedDate) : null;
    }
    
    setSelectedDate(dateString) {
        this.selectedDate = this.parseDate(dateString);
        this.render();
        this.attachEventListeners();
    }
    
    setDisabledDates(dates) {
        this.disabledDates = dates;
        this.render();
        this.attachEventListeners();
    }
}

// Estilos adicionales para el calendario
const calendarStyles = `
    .calendar {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .calendar-days-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
        margin-bottom: 10px;
    }
    
    .calendar-day-name {
        text-align: center;
        font-weight: 600;
        color: #666;
        padding: 10px 0;
        font-size: 0.9rem;
    }
    
    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 5px;
    }
    
    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .calendar-day:not(.empty):not(.disabled):hover {
        background-color: rgba(102, 126, 234, 0.1);
        transform: scale(1.05);
    }
    
    .calendar-day.empty {
        cursor: default;
    }
    
    .calendar-day.disabled {
        color: #ccc;
        cursor: not-allowed;
    }
    
    .calendar-day.selected {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .calendar-day.today {
        border: 2px solid #667eea;
    }
`;

// Inyectar estilos si no existen
if (!document.getElementById('calendar-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'calendar-styles';
    styleSheet.textContent = calendarStyles;
    document.head.appendChild(styleSheet);
}

// Exportar para uso global
window.Calendar = Calendar;