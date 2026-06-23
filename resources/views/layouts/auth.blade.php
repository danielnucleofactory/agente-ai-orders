<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <title>RAGA Orders - Iniciar Sesión</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @livewireStyles
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #f8f9fa;
        }
        .raga-shell { display: flex; width: 100%; min-height: 100vh; }

        .raga-form-panel {
            flex: 0 0 100%;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 24px;
            position: relative;
        }
        @media (min-width: 768px) {
            .raga-form-panel { flex: 0 0 50%; padding: 48px 56px; }
        }
        .raga-form-inner { width: 100%; max-width: 420px; }

        .raga-logo-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }
        .raga-logo-wrap img { height: 52px; }

        .raga-form-header { margin-bottom: 28px; }
        .raga-form-header h1 {
            color: #1a1a1a;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.4px;
            margin-bottom: 6px;
        }
        .raga-form-header p { color: #6b7280; font-size: 14px; }

        .raga-field { margin-bottom: 20px; }
        .raga-field label {
            display: block;
            color: #1AAD8A;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
        }
        .raga-field-wrap { position: relative; }
        .raga-input {
            width: 100%;
            background: #ffffff;
            border: 1.5px solid #1AAD8A;
            border-radius: 10px;
            padding: 12px 40px 12px 14px;
            color: #1a1a1a;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            -webkit-appearance: none;
        }
        .raga-input:focus {
            border-color: #1AAD8A;
            box-shadow: 0 0 0 3px rgba(26,173,138,0.12);
        }
        .raga-input::placeholder { color: #9ca3af; }
        .raga-input.is-error { border-color: #ef4444; }

        /* Ocultar ojo nativo del navegador */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear { display: none !important; }
        input[type="password"]::-webkit-credentials-auto-fill-button { visibility: hidden; }
        input::-webkit-contacts-auto-fill-button,
        input::-webkit-credentials-auto-fill-button { visibility: hidden; pointer-events: none; }

        .raga-input-btn {
            position: absolute; right: 12px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; cursor: pointer;
            background: none; border: none; padding: 0;
            display: flex; align-items: center;
            transition: color 0.15s;
            z-index: 2;
        }
        .raga-input-btn:hover { color: #1AAD8A; }
        .raga-error-msg { color: #ef4444; font-size: 12px; margin-top: 5px; }

        .raga-row {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 24px;
            flex-wrap: wrap; gap: 8px;
        }
        .raga-remember { display: flex; align-items: center; gap: 7px; cursor: pointer; }
        .raga-remember input[type="checkbox"] { accent-color: #1AAD8A; width: 15px; height: 15px; }
        .raga-remember span { color: #6b7280; font-size: 13px; }
        .raga-forgot { color: #6b7280; font-size: 13px; text-decoration: none; transition: color 0.15s; }
        .raga-forgot:hover { color: #1AAD8A; }

        .raga-btn-primary {
            width: 100%;
            background: #1AAD8A;
            color: #ffffff;
            border: none; border-radius: 10px;
            padding: 13px; font-size: 15px; font-weight: 600;
            font-family: 'Inter', sans-serif; cursor: pointer;
            transition: background 0.2s ease, transform 0.1s ease;
            margin-bottom: 12px;
        }
        .raga-btn-primary:hover { background: #159876; }
        .raga-btn-primary:active { transform: scale(0.99); }

        .raga-btn-secondary {
            width: 100%;
            background: #1AAD8A;
            color: #ffffff;
            border: none; border-radius: 10px;
            padding: 13px; font-size: 15px; font-weight: 600;
            font-family: 'Inter', sans-serif; cursor: pointer;
            text-decoration: none; display: block; text-align: center;
            transition: background 0.2s ease, transform 0.1s ease;
        }
        .raga-btn-secondary:hover { background: #159876; }
        .raga-btn-secondary:active { transform: scale(0.99); }

        /* Panel visual derecho */
        .raga-visual-panel {
            display: none;
            flex: 1;
            position: relative;
            overflow: hidden;
            flex-direction: column;
            justify-content: flex-end;
            padding: 40px;
            background: #0d1b2a;
        }
        @media (min-width: 768px) { .raga-visual-panel { display: flex; } }

        .raga-visual-logo {
            position: absolute; top: 32px; left: 36px;
            display: flex; align-items: center; z-index: 5;
        }
        .raga-visual-logo-name {
            color: #ffffff; font-size: 22px;
            font-weight: 700; line-height: 1;
        }
        .raga-visual-logo-suite {
            color: #1AAD8A; font-size: 10px; font-weight: 500;
            letter-spacing: 3px; text-transform: uppercase; margin-top: 3px;
        }

        canvas#ragaNetworkCanvas { position: absolute; inset: 0; width: 100%; height: 100%; }

        .raga-visual-content { position: relative; z-index: 5; }
        .raga-visual-content h2 {
            color: #ffffff; font-size: 26px; font-weight: 600;
            line-height: 1.35; letter-spacing: -0.4px; margin-bottom: 10px;
        }
        .raga-visual-content p { color: #8fa8bf; font-size: 14px; line-height: 1.7; }
    </style>
</head>
<body>
<div class="raga-shell">

    <div class="raga-form-panel">
        <div class="raga-form-inner">
            <div class="raga-logo-wrap">
                <img src="{{ asset('img/logo-olo.svg') }}" alt="RAGA Orders">
            </div>
            {{ $slot }}
        </div>
    </div>

    <div class="raga-visual-panel">
        <div class="raga-visual-logo">
            <div>
                <div class="raga-visual-logo-name">RAGA-x</div>
                <div class="raga-visual-logo-suite">Orders</div>
            </div>
        </div>
        <canvas id="ragaNetworkCanvas"></canvas>
        <div class="raga-visual-content">
            <h2>Trazabilidad total para tu<br>cadena de suministro</h2>
            <p>Visibilidad en tiempo real sobre órdenes de compra,<br>embarques y operaciones logísticas globales.</p>
        </div>
    </div>

</div>

@livewireScripts

<script>
function ragaTogglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (!input) return;
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) { icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
    } else {
        input.type = 'password';
        if (icon) { icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
    }
}

(function() {
    const canvas = document.getElementById('ragaNetworkCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    function resize() { canvas.width = canvas.offsetWidth; canvas.height = canvas.offsetHeight; }
    resize();
    window.addEventListener('resize', resize);
    const NODES = [
        {rx:0.10,ry:0.14,code:'SHG'},{rx:0.32,ry:0.07,code:'TOK'},
        {rx:0.65,ry:0.20,code:'LAX'},{rx:0.85,ry:0.42,code:'MIA'},
        {rx:0.52,ry:0.50,code:'HUB'},{rx:0.22,ry:0.40,code:'SNG'},
        {rx:0.76,ry:0.70,code:'SAO'},{rx:0.40,ry:0.78,code:'RTD'},
        {rx:0.14,ry:0.62,code:'MUM'},{rx:0.60,ry:0.35,code:'DXB'},
    ];
    const EDGES = [[0,1],[1,2],[2,3],[3,4],[4,5],[5,0],[2,9],[9,4],[4,6],[6,7],[7,8],[8,5],[9,3],[1,9],[5,9],[0,8],[3,6]];
    const packets = EDGES.filter(()=>Math.random()>0.3).map(edge=>({
        edge, t:Math.random(), speed:0.0018+Math.random()*0.0025, dir:Math.random()>0.5?1:-1
    }));
    let frame = 0;
    function nx(i){return NODES[i].rx*canvas.width;}
    function ny(i){return NODES[i].ry*canvas.height;}
    function draw(){
        ctx.clearRect(0,0,canvas.width,canvas.height);
        EDGES.forEach(([a,b])=>{
            ctx.beginPath();ctx.moveTo(nx(a),ny(a));ctx.lineTo(nx(b),ny(b));
            ctx.strokeStyle='rgba(26,173,138,0.12)';ctx.lineWidth=0.8;ctx.stroke();
        });
        packets.forEach(p=>{
            const [a,b]=p.edge;
            const x=nx(a)+(nx(b)-nx(a))*p.t, y=ny(a)+(ny(b)-ny(a))*p.t;
            ctx.beginPath();ctx.arc(x,y,2.2,0,Math.PI*2);
            ctx.fillStyle='rgba(26,173,138,0.9)';ctx.fill();
            p.t+=p.speed*p.dir; if(p.t>1||p.t<0)p.dir*=-1;
        });
        NODES.forEach((n,i)=>{
            const x=n.rx*canvas.width, y=n.ry*canvas.height;
            const pulse=6+Math.sin(frame*0.045+i*0.8)*2.5;
            ctx.beginPath();ctx.arc(x,y,pulse,0,Math.PI*2);
            ctx.strokeStyle=`rgba(26,173,138,${0.10+0.06*Math.sin(frame*0.045+i)})`;
            ctx.lineWidth=1;ctx.stroke();
            ctx.beginPath();ctx.arc(x,y,3.5,0,Math.PI*2);
            ctx.fillStyle='rgba(26,173,138,0.85)';ctx.fill();
            ctx.fillStyle='rgba(143,168,191,0.8)';
            ctx.font='9px Inter,sans-serif';ctx.fillText(n.code,x+6,y-4);
        });
        frame++;requestAnimationFrame(draw);
    }
    draw();
})();
</script>
</body>
</html>