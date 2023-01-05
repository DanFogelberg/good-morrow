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
      const totalCost = bookingDays * roomTypes.basic.cost;
      console.log(totalCost);
    }
  }
};

//roomTypes = $roomTypes from php as object
roomSelect = document.querySelector('select');

roomSelect.addEventListener('change', function () {
  console.log(roomSelect.value);
});

arrivalSelect = document.querySelector('#arrival');
departureSelect = document.querySelector('#departure');

console.log(arrivalSelect.value);

arrivalSelect.addEventListener('change', calculatePrice);
departureSelect.addEventListener('change', calculatePrice);
