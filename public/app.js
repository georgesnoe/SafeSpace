(() => {
  const cursor = document.querySelector('.cursor-glow');
  if (cursor && window.matchMedia('(pointer:fine)').matches) {
    window.addEventListener('mousemove', (e) => {
      cursor.style.left = `${e.clientX}px`;
      cursor.style.top = `${e.clientY}px`;
    });
  } else if (cursor) {
    cursor.style.display = 'none';
  }

  const revealEls = document.querySelectorAll('[data-reveal]');
  const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('in-view');
        revealObserver.unobserve(entry.target);
      }
    });
  }, { threshold: 0.15 });
  revealEls.forEach((el) => revealObserver.observe(el));

  const header = document.querySelector('.site-header');
  const toggleHeaderState = () => {
    if (!header) return;
    if (window.scrollY > 12) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  };
  toggleHeaderState();
  window.addEventListener('scroll', toggleHeaderState, { passive: true });

  const bokehDots = document.querySelectorAll('.bokeh-dot');
  const bokehPalette = ['rgba(255,126,179,.55)', 'rgba(255,69,96,.48)', 'rgba(255,160,122,.45)'];
  bokehDots.forEach((dot, index) => {
    const size = (Math.random() * 2.5 + 1.5).toFixed(2);
    dot.style.width = `${size}px`;
    dot.style.height = `${size}px`;
    dot.style.left = `${Math.random() * 100}%`;
    dot.style.top = `${Math.random() * 100}%`;
    dot.style.opacity = (Math.random() * 0.4 + 0.2).toFixed(2);
    dot.style.background = bokehPalette[index % bokehPalette.length];
    dot.style.animationDuration = `${(Math.random() * 4 + 4).toFixed(2)}s`;
    dot.style.animationDelay = `${(Math.random() * 0.6).toFixed(2)}s`;
  });

  const counterRoot = document.querySelector('[data-counter-root]');
  if (counterRoot) {
    const counters = counterRoot.querySelectorAll('[data-counter-target]');
    const formatValue = (num, target, suffix) => {
      if (target >= 1000) {
        const compact = Math.round(num / 1000);
        return `${compact}K${suffix || ''}`;
      }
      return `${Math.round(num)}${suffix || ''}`;
    };

    const runCounter = (el) => {
      const target = Number(el.getAttribute('data-counter-target') || 0);
      const prefix = el.getAttribute('data-counter-prefix') || '';
      const suffix = el.getAttribute('data-counter-suffix') || '';
      const duration = 1300;
      const start = performance.now();

      const tick = (now) => {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const value = target * eased;
        el.textContent = `${prefix}${formatValue(value, target, suffix)}`;
        if (progress < 1) {
          requestAnimationFrame(tick);
        } else {
          if (target === 12000) {
            el.textContent = `${prefix}12K${suffix}`;
          } else {
            el.textContent = `${prefix}${target}${suffix}`;
          }
        }
      };
      requestAnimationFrame(tick);
    };

    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        counters.forEach(runCounter);
        counterObserver.disconnect();
      });
    }, { threshold: 0.5 });

    counterObserver.observe(counterRoot);
  }
})();
