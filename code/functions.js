//Updates total cost for selected booking during runtime
const updateCost = () => {
    if ((arrivalSelect.value != '') & (departureSelect.value != '')) {
        const arrival = new Date(arrivalSelect.value);
        const departure = new Date(departureSelect.value);
        const bookingMilliseconds = departure.getTime() - arrival.getTime();
        const bookingDays = bookingMilliseconds / (60 * 60 * 24 * 1000);
        if (bookingDays < 1) {
            costViewer.textContent = 'Total Cost:';
        } else {
            var totalCost = bookingDays * roomTypes[roomSelect.value].cost;
            extras.forEach((extra) => {
                if (extra.checked === true) {
                    totalCost += parseFloat(extra.value);
                }
            });

            //Function for checking for discounts should get data from same place for both php and javascript. Hardcoded for now.
            let hasDiscount = false;
            if (bookingDays >= 7) {
                totalCost *= 0.8;
                totalCost = totalCost.toFixed(2); //Compensating for Javascript float inaccuraccy
                hasDiscount = true;
            }
            costViewer.textContent = `Total Cost: ${totalCost}`;
            if (hasDiscount === true)
                costViewer.textContent += ' (20% Discount!)';
        }
    }
};

//Determines active calendar based on data from button or select
const setActiveCalendar = (activeKey) => {
    calendars.forEach((calendar) => {
        calendar.classList.add('hidden');
    });
    calendars[activeKey].classList.remove('hidden');

    calendarButtons.forEach((calendarButton) => {
        calendarButton.classList.remove('selected');
    });
    calendarButtons[activeKey].classList.add('selected');
};
