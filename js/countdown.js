function CountDownTimer(dt, id) {
  const end = new Date(dt);

  const _second = 1000;
  const _minute = _second * 60;
  const _hour = _minute * 60;
  const _day = _hour * 24;
  let timer;

  function showRemaining() {
    const now = new Date();
    const distance = end - now;
    if (distance <= 0) {
      clearInterval(timer);
    }
    const days = Math.floor(distance / _day);
    const hours = Math.floor((distance % _day) / _hour);
    const minutes = Math.floor((distance % _hour) / _minute);
    const seconds = Math.floor((distance % _minute) / _second);

    document.getElementById('count-min-1').innerHTML = days;
    document.getElementById('count-min-2').innerHTML = hours;
    document.getElementById('count-min-3').innerHTML = minutes;
    document.getElementById('count-min-4').innerHTML = seconds;
  }
  showRemaining();
  timer = setInterval(showRemaining, 1000);
}