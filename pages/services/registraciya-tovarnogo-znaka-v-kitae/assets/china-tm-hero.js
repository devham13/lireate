/**
 * Hero: маршрут заявки на товарный знак в КНР (уникальная сцена для LIREATE).
 */
document.addEventListener('DOMContentLoaded', function () {
  var canvas = document.getElementById('china-tm-hero-canvas');
  if (!canvas) return;
  var ctx = canvas.getContext('2d');
  var w = 0;
  var h = 0;
  var t = 0;

  function resize() {
    var box = canvas.parentElement;
    if (!box) return;
    w = box.clientWidth;
    h = Math.max(320, Math.min(520, box.clientHeight || 420));
    canvas.width = w;
    canvas.height = h;
  }
  window.addEventListener('resize', resize);
  resize();

  var packets = [];
  for (var i = 0; i < 5; i++) {
    packets.push({ phase: i * 0.22, label: ['Заявка', 'Перевод', 'МКТУ', 'Экспертиза', 'Свидетельство'][i] });
  }

  function drawGrid() {
    ctx.strokeStyle = 'rgba(121,242,255,0.06)';
    ctx.lineWidth = 1;
    for (var x = 0; x < w; x += 28) {
      ctx.beginPath();
      ctx.moveTo(x, 0);
      ctx.lineTo(x, h);
      ctx.stroke();
    }
    for (var y = 0; y < h; y += 28) {
      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.lineTo(w, y);
      ctx.stroke();
    }
  }

  function drawNode(x, y, r, color, title, sub) {
    ctx.fillStyle = 'rgba(8,11,23,0.85)';
    ctx.strokeStyle = color;
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.arc(x, y, r, 0, Math.PI * 2);
    ctx.fill();
    ctx.stroke();
    ctx.fillStyle = '#e8f4ff';
    ctx.font = '600 11px system-ui,sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(title, x, y + 4);
    if (sub) {
      ctx.fillStyle = '#94a3b8';
      ctx.font = '10px system-ui,sans-serif';
      ctx.fillText(sub, x, y + r + 14);
    }
  }

  function frame() {
    t += 0.016;
    ctx.clearRect(0, 0, w, h);
    var g = ctx.createRadialGradient(w * 0.7, h * 0.3, 10, w * 0.5, h * 0.5, w * 0.8);
    g.addColorStop(0, 'rgba(139,92,246,0.18)');
    g.addColorStop(1, 'rgba(5,7,17,0)');
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, w, h);
    drawGrid();

    var pathY = h * 0.55;
    var x0 = w * 0.08;
    var x1 = w * 0.92;
    ctx.strokeStyle = 'rgba(121,242,255,0.35)';
    ctx.lineWidth = 3;
    ctx.setLineDash([8, 10]);
    ctx.beginPath();
    ctx.moveTo(x0, pathY);
    ctx.lineTo(x1, pathY);
    ctx.stroke();
    ctx.setLineDash([]);

    var nodes = [
      { x: x0 + (x1 - x0) * 0.05, t: 'RU', s: 'бренд' },
      { x: x0 + (x1 - x0) * 0.28, t: 'CNIPA', s: 'подача' },
      { x: x0 + (x1 - x0) * 0.52, t: 'Экспертиза', s: 'классы' },
      { x: x0 + (x1 - x0) * 0.76, t: 'Реестр', s: 'защита' },
      { x: x0 + (x1 - x0) * 0.94, t: '®', s: 'лиды' }
    ];
    nodes.forEach(function (n, i) {
      var pulse = 0.85 + Math.sin(t * 2 + i) * 0.08;
      drawNode(n.x, pathY - 50, 26 * pulse, i === 4 ? '#f59e0b' : '#79f2ff', n.t, n.s);
    });

    packets.forEach(function (p, idx) {
      var prog = (t * 0.12 + p.phase) % 1;
      var px = x0 + (x1 - x0) * prog;
      var py = pathY - 8 + Math.sin(t * 3 + idx) * 6;
      ctx.fillStyle = 'rgba(34,197,94,0.9)';
      ctx.fillRect(px - 18, py - 10, 36, 20);
      ctx.strokeStyle = '#0f172a';
      ctx.strokeRect(px - 18, py - 10, 36, 20);
      ctx.fillStyle = '#0f172a';
      ctx.font = '9px system-ui,sans-serif';
      ctx.textAlign = 'center';
      ctx.fillText(p.label, px, py + 3);
    });

    ctx.fillStyle = 'rgba(245,158,11,0.15)';
    ctx.beginPath();
    ctx.ellipse(w * 0.82, h * 0.22, 70, 40, 0, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = '#fcd34d';
    ctx.font = 'bold 28px system-ui,sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('™', w * 0.82, h * 0.24);

    requestAnimationFrame(frame);
  }
  frame();
});
