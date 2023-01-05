const calculatePrice = () => {
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
          totalCost += parseFloat(extra.value) * bookingDays;
        }
      });

      //Function for checking for discounts should get data from same place for both php and javascript. Hardcoded for now.
      let hasDiscount = false;
      if (bookingDays >= 7) {
        totalCost *= 0.8;
        totalCost = totalCost.toFixed(2);
        hasDiscount = true;
      }
      costViewer.textContent = `Total Cost: ${totalCost}`;
      if (hasDiscount === true)
        //costViewer.textContent += ' Discount active! 20% off!';
        console.log(totalCost);
    }
  }
};

//roomTypes = $roomTypes from php as object
roomSelect = document.querySelector('select');
arrivalSelect = document.querySelector('#arrival');
departureSelect = document.querySelector('#departure');
extras = document.querySelectorAll('.extra');

roomSelect.addEventListener('change', calculatePrice);
arrivalSelect.addEventListener('change', calculatePrice);
departureSelect.addEventListener('change', calculatePrice);
extras.forEach((extra) => {
  extra.addEventListener('change', calculatePrice);
});

costViewer = document.querySelector('.booking-row p');
