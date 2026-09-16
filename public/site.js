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

// Giriş fotoğrafları: 6 sn'de bir yumuşak geçiş (hareket azaltma tercihinde sabit kalır)
(function () {
  var slides = document.querySelectorAll('.hero-slide');
  var dots = document.querySelectorAll('.hero-dots span');
  if (slides.length < 2 || (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) return;
  var i = 0;
  setInterval(function () {
    slides[i].classList.remove('on'); if (dots[i]) dots[i].classList.remove('on');
    i = (i + 1) % slides.length;
    slides[i].classList.add('on'); if (dots[i]) dots[i].classList.add('on');
  }, 6000);
})();

// Giriş başlığı: yazılan metin efekti (hareket azaltma tercihinde anında görünür)
(function () {
  var els = document.querySelectorAll('.type, .type-p');
  if (!els.length) return;
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var tgt = function (el) { return el.querySelector('.typed') || el; };
  els.forEach(function (el) { if (reduce) tgt(el).textContent = el.getAttribute('data-text'); });
  if (reduce) return;
  function typeInto(el, text, speed, done) {
    var i = 0; el.classList.add('typing');
    var t = tgt(el);
    (function step() { t.textContent = text.slice(0, ++i); if (i < text.length) setTimeout(step, speed); else { el.classList.remove('typing'); done && done(); } })();
  }
  var h = document.querySelectorAll('.type'); var p = document.querySelector('.type-p');
  typeInto(h[0], h[0].getAttribute('data-text'), 45, function () {
    typeInto(h[1], h[1].getAttribute('data-text'), 45, function () { if (p) typeInto(p, p.getAttribute('data-text'), 9); });
  });
})();

// Basında: YouTube videosu tıklanınca yüklenir (çerezsiz gömme)
document.querySelectorAll('.yt[data-yt]').forEach(function (b) {
  b.addEventListener('click', function () {
    var f = document.createElement('iframe');
    f.src = 'https://www.youtube-nocookie.com/embed/' + b.getAttribute('data-yt') + '?autoplay=1&rel=0';
    f.allow = 'accelerometer; autoplay; encrypted-media; picture-in-picture'; f.allowFullscreen = true; f.title = b.getAttribute('aria-label') || 'Video';
    b.innerHTML = ''; b.appendChild(f);
  });
});

// Dil menüsü
document.querySelectorAll('.lang').forEach(function (w) {
  var b = w.querySelector('.lang-btn'); if (!b) return;
  b.addEventListener('click', function (e) { e.stopPropagation(); var o = w.classList.toggle('open'); b.setAttribute('aria-expanded', o ? 'true' : 'false'); });
  document.addEventListener('click', function () { w.classList.remove('open'); b.setAttribute('aria-expanded', 'false'); });
});
