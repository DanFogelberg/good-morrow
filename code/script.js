//roomTypes = $roomTypes from php as object
roomSelect = document.querySelector('select');
arrivalSelect = document.querySelector('#arrival');
departureSelect = document.querySelector('#departure');
extras = document.querySelectorAll('.extra');
costViewer = document.querySelector('.booking-row p');
calendars = document.querySelectorAll('.calendarContainer');
calendarButtons = document.querySelectorAll('.calendarSelect h3');

setActiveCalendar(0);
roomSelect.addEventListener('change', (e) => {
    setActiveCalendar(e.target.selectedIndex);
    updateCost();
});
calendarButtons.forEach((button) => {
    button.addEventListener('click', (e) => {
        setActiveCalendar(e.target.dataset.calendarnumber);
        roomSelect.selectedIndex = e.target.dataset.calendarnumber;
        updateCost();
    });
});

arrivalSelect.addEventListener('change', updateCost);
departureSelect.addEventListener('change', updateCost);
extras.forEach((extra) => {
    extra.addEventListener('change', updateCost);
});
