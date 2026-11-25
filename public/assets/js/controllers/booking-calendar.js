const bookingForm = document.querySelector('[data-booking-calendar]');

if (bookingForm) {
    const calendar = bookingForm.querySelector('[data-calendar]');
    const start = bookingForm.elements.moveIn;
    const end = bookingForm.elements.moveOut;
    const today = start.min;
    const reserved = JSON.parse(bookingForm.dataset.reserved);
    const days = calendar.querySelector('[data-calendar-days]');
    const heading = calendar.querySelector('[data-calendar-month]');
    const help = calendar.querySelector('[data-calendar-help]');
    const previous = calendar.querySelector('[data-calendar-previous]');
    const next = calendar.querySelector('[data-calendar-next]');
    const locale = document.documentElement.lang;
    const weekday = new Intl.DateTimeFormat(locale, { weekday: 'short', timeZone: 'UTC' });
    const number = new Intl.NumberFormat(locale);
    const monthName = new Intl.DateTimeFormat(locale, { month: 'long', year: 'numeric', timeZone: 'UTC' });
    const dateName = new Intl.DateTimeFormat(locale, { dateStyle: 'full', timeZone: 'UTC' });

    const monthOf = (value) => new Date(value.slice(0, 7) + '-01T00:00:00Z');
    const dateKey = (date) => date.toISOString().slice(0, 10);
    const isReserved = (date) => reserved.some(([from, to]) => from <= date && date < to);
    const canEnd = (date) => start.value && date > start.value
        && !reserved.some(([from, to]) => from < date && start.value < to);

    let month = monthOf(start.value || today);
    let choosingEnd = Boolean(start.value && !end.value);

    function render(focusDate) {
        const firstDay = month.getUTCDay();
        heading.textContent = monthName.format(month);
        previous.disabled = dateKey(month) <= dateKey(monthOf(today));
        help.textContent = choosingEnd
            ? window.makanTranslate('Choose a checkout date. Your stay cannot cross reserved nights.')
            : window.makanTranslate('Choose a start date. Reserved nights are unavailable.');
        days.replaceChildren();

        for (let day = 1; day <= 7; day++) {
            const label = document.createElement('small');
            label.textContent = weekday.format(new Date(Date.UTC(2023, 0, day)));
            days.append(label);
        }
        for (let space = 0; space < firstDay; space++) {
            days.append(document.createElement('span'));
        }
        for (const day = new Date(month); day.getUTCMonth() === month.getUTCMonth(); day.setUTCDate(day.getUTCDate() + 1)) {
            const value = dateKey(day);
            const button = document.createElement('button');
            const selected = value === start.value || value === end.value;
            button.type = 'button';
            button.className = 'btn btn-sm px-0 ' + (selected ? 'btn-accent' : 'btn-outline-secondary');
            button.textContent = number.format(day.getUTCDate());
            button.setAttribute('aria-label', dateName.format(day));
            button.setAttribute('aria-pressed', String(selected));
            button.disabled = choosingEnd ? !canEnd(value) : value < today || isReserved(value);
            button.addEventListener('click', () => {
                if (choosingEnd) {
                    end.value = value;
                    choosingEnd = false;
                    render(value);
                } else {
                    start.value = value;
                    end.value = '';
                    choosingEnd = true;
                    render();
                    end.focus();
                }
            });
            days.append(button);
            if (focusDate === value) {
                button.focus();
            }
        }
    }

    for (const input of [start, end]) {
        input.type = 'text';
        input.readOnly = true;
        input.placeholder = window.makanTranslate('Select from the calendar');
        input.addEventListener('click', () => {
            choosingEnd = input === end && Boolean(start.value);
            month = monthOf(input.value || start.value || today);
            render();
        });
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                input.click();
                days.querySelector('button:not(:disabled)')?.focus();
            }
        });
    }
    previous.addEventListener('click', () => {
        month.setUTCMonth(month.getUTCMonth() - 1);
        render();
    });
    next.addEventListener('click', () => {
        month.setUTCMonth(month.getUTCMonth() + 1);
        render();
    });
    bookingForm.addEventListener('submit', (event) => {
        if (!start.value || start.value < today || isReserved(start.value) || !canEnd(end.value)) {
            event.preventDefault();
            choosingEnd = Boolean(start.value && start.value >= today && !isReserved(start.value));
            render();
            help.textContent = window.makanTranslate('Choose an available start and checkout date before sending your request.');
            (choosingEnd ? end : start).focus();
        }
    });

    calendar.hidden = false;
    render();
}
