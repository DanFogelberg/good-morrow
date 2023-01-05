const calculatePrice = () => {
  if ((arrivalSelect.value != '') & (departureSelect.value != '')) {
    console.log('PRAJSTAJM');
    const arrival = new Date(arrivalSelect.value);
    const departure = new Date(departureSelect.value);
    const bookingMilliseconds = departure.getTime() - arrival.getTime();
    const bookingDays = bookingMilliseconds / (60 * 60 * 24 * 1000);
    console.log(bookingDays);
    if (bookingDays < 1) {
      console.log('Booking less than 1 day.');
    } else {
      var totalCost = bookingDays * roomTypes[roomSelect.value].cost;
      extras.forEach((extra) => {
        if (extra.checked === true) {
          totalCost += parseFloat(extra.value) * bookingDays;
        }
      });
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
