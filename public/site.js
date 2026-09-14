(function () {
  var menu = document.getElementById('menu');
  var burger = document.getElementById('burger');
  if (burger && menu) {
    burger.addEventListener('click', function () {
      var open = menu.classList.toggle('open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    menu.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { menu.classList.remove('open'); });
    });
  }
  var f = document.getElementById('randevuForm');
  if (f) {
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = f.querySelector('button'); btn.disabled = true; btn.textContent = 'Gönderiliyor…';
      fetch('/randevu.php', { method: 'POST', body: new FormData(f) })
        .then(function (r) { return r.json(); })
        .then(function (j) { if (!j.ok) throw 0; f.reset(); document.getElementById('formOk').hidden = false; document.getElementById('formErr').hidden = true; })
        .catch(function () { document.getElementById('formErr').hidden = false; })
        .finally(function () { btn.disabled = false; btn.textContent = 'Beni arayın'; });
    });
  }
})();
