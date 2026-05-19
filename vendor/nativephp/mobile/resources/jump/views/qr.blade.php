<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jump | Bifrost Technology</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Space+Grotesk:wght@300;500;700&display=swap" rel="stylesheet">

    <!-- GSAP & Three.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

    <style>
        :root {
            --primary: #00f3ff;
            --secondary: #bc13fe;
            --bg: #050505;
            --glass: rgba(255, 255, 255, 0.03);
            --border: rgba(255, 255, 255, 0.1);
            --scan-line: rgba(0, 243, 255, 0.5);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: var(--bg);
            color: #ffffff;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Three.js Canvas */
        #canvas-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: all; /* Allow mouse interaction */
        }

        /* Overlay Gradient/Vignette */
        .vignette {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, transparent 0%, #000000 120%);
            z-index: 1;
            pointer-events: none;
        }

        /* Main Card */
        .interface-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            padding: 2px; /* For border gradient */
            background: linear-gradient(135deg, rgba(0, 243, 255, 0.3), rgba(188, 19, 254, 0.3));
            border-radius: 24px;
            box-shadow: 0 0 50px rgba(0, 243, 255, 0.15);
            opacity: 0; /* Hidden for GSAP intro */
            transform: translateY(20px);
        }

        .interface-inner {
            background: rgba(10, 10, 12, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 22px;
            padding: 20px 10px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        /* Decorative HUD lines */
        .hud-line {
            position: absolute;
            background: var(--primary);
            opacity: 0.5;
        }
        .hud-tl { top: 20px; left: 20px; width: 20px; height: 2px; }
        .hud-tr { top: 20px; right: 20px; width: 20px; height: 2px; }
        .hud-bl { bottom: 20px; left: 20px; width: 20px; height: 2px; }
        .hud-br { bottom: 20px; right: 20px; width: 20px; height: 2px; }

        .hud-corner {
            position: absolute;
            width: 8px;
            height: 8px;
            border: 2px solid var(--primary);
            opacity: 0.7;
        }
        .c-tl { top: 10px; left: 10px; border-right: none; border-bottom: none; }
        .c-tr { top: 10px; right: 10px; border-left: none; border-bottom: none; }
        .c-bl { bottom: 10px; left: 10px; border-right: none; border-top: none; }
        .c-br { bottom: 10px; right: 10px; border-left: none; border-top: none; }

        h1 {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-size: 3rem;
            letter-spacing: -2px;
            margin-bottom: 5px;
            background: linear-gradient(90deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
        }

        .app-name {
            font-family: 'JetBrains Mono', monospace;
            color: #888;
            font-size: 0.9rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .app-name::before, .app-name::after {
            content: '';
            width: 20px;
            height: 1px;
            background: #333;
        }

        /* QR Section */
        .qr-frame {
            position: relative;
            width: 260px;
            height: 260px;
            margin: 0 auto 30px;
            padding: 15px;
            background: rgba(255,255,255,0.03);
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-code {
            width: 100%;
            height: 100%;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            background: white; /* Ensure high contrast for QR */
        }

        .qr-code img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        /* Scanning Laser Effect */
        .scan-line {
            position: absolute;
            top: 0;
            left: -10%;
            width: 120%;
            height: 4px;
            background: var(--primary);
            box-shadow: 0 0 15px var(--primary), 0 0 30px var(--primary);
            opacity: 0.6;
            animation: scan 2.5s ease-in-out infinite;
            z-index: 5;
            pointer-events: none;
        }

        @keyframes scan {
            0% { top: 0%; opacity: 0; }
            10% { opacity: 0.8; }
            90% { opacity: 0.8; }
            100% { top: 100%; opacity: 0; }
        }

        .instructions {
            color: #ccc;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        /* Connection Badge */
        .connection-status {
            background: rgba(0, 243, 255, 0.05);
            border: 1px solid rgba(0, 243, 255, 0.2);
            padding: 15px;
            border-radius: 12px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        /* Animated glow behind status */
        .connection-status::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(0, 243, 255, 0.1), transparent);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            50%, 100% { left: 200%; }
        }

        .status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .label { color: #666; }
        .value { color: var(--primary); font-weight: 700; text-shadow: 0 0 10px rgba(0, 243, 255, 0.5); }
        .value-alt { color: var(--secondary); font-weight: 700; }

        .pulse-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: var(--primary);
            border-radius: 50%;
            margin-right: 8px;
            box-shadow: 0 0 8px var(--primary);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.4); opacity: 0.5; }
            100% { transform: scale(1); opacity: 1; }
        }

        .hot-reload-text {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            color: var(--primary);
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Success Overlay */
        .success-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            opacity: 0;
        }

        .success-overlay.active {
            opacity: 1;
            background-color: rgba(5, 5, 5, 0.95);
            pointer-events: all;
        }

        .success-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(0, 243, 255, 0.15) 0%, rgba(188, 19, 254, 0.1) 40%, rgba(0, 0, 0, 0.9) 70%);
        }

        .success-content {
            position: relative;
            text-align: center;
            z-index: 1;
        }

        .success-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 30px;
            position: relative;
        }

        .success-ring {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 3px solid var(--primary);
            border-radius: 50%;
            animation: ringPulse 1.5s ease-out infinite;
        }

        .success-ring:nth-child(2) {
            border-color: var(--secondary);
        }

        .success-ring:nth-child(2) { animation-delay: 0.3s; }
        .success-ring:nth-child(3) { animation-delay: 0.6s; }

        @keyframes ringPulse {
            0% { transform: scale(1); opacity: 1; }
            100% { transform: scale(2); opacity: 0; }
        }

        .success-check {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 60px;
            height: 60px;
        }

        .success-check svg {
            width: 100%;
            height: 100%;
            stroke: var(--primary);
            stroke-width: 3;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .success-check svg path {
            stroke-dasharray: 100;
            stroke-dashoffset: 100;
            animation: drawCheck 0.6s ease-out 0.3s forwards;
        }

        @keyframes drawCheck {
            to { stroke-dashoffset: 0; }
        }

        .success-title {
            font-family: 'JetBrains Mono', monospace;
            font-size: 2.5rem;
            font-weight: 700;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 6px;
            margin-bottom: 15px;
            text-shadow:
                0 0 10px #00f3ff,
                0 0 20px #bc13fe,
                0 0 40px #00f3ff,
                0 0 80px #bc13fe;
        }

        .success-subtitle {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 1.1rem;
            color: #ccc;
            margin-bottom: 20px;
        }

        .success-device {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            color: var(--primary);
            background: rgba(0, 243, 255, 0.1);
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid rgba(0, 243, 255, 0.3);
            display: inline-block;
        }

        /* Particle burst effect */
        .particle-burst {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 10px;
            height: 10px;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0;
        }

        .particle:nth-child(even) {
            background: var(--secondary);
        }

    </style>
</head>
<body>

<!-- Three.js Background -->
<div id="canvas-container"></div>
<div class="vignette"></div>

<!-- Interface -->
<div class="interface-container">
    <div class="interface-inner">
        <!-- Decorative Corners -->
        <div class="hud-corner c-tl"></div>
        <div class="hud-corner c-tr"></div>
        <div class="hud-corner c-bl"></div>
        <div class="hud-corner c-br"></div>

        <!-- Decorative Lines -->
        <div class="hud-line hud-tl"></div>
        <div class="hud-line hud-tr"></div>

        <h1 class="split-text">JUMP</h1>
        <div class="app-name">
            BIFROST TECHNOLOGY
        </div>

        <div class="qr-frame">
            <div class="scan-line"></div>
            <div class="qr-code">
                <img src="{{ $qrCodeDataUri }}" alt="Scan to Connect">
            </div>
        </div>

        <div class="instructions split-text">
            Initialize neural handshake.<br>Scan via mobile terminal.
        </div>

        <div class="connection-status">
            <div class="status-row">
                <span class="label">HOST</span>
                <span class="value">{{ $displayHost }}</span>
            </div>
            <div class="status-row">
                <span class="label">PORT</span>
                <span class="value-alt">{{ $port }}</span>
            </div>
            <div class="hot-reload-text">
                <span class="pulse-dot"></span> System Online
            </div>
        </div>
    </div>
</div>

<!-- Success Overlay -->
<div class="success-overlay" id="successOverlay">
    <div class="success-backdrop"></div>
    <div class="success-content">
        <div class="success-icon">
            <div class="success-ring"></div>
            <div class="success-ring"></div>
            <div class="success-ring"></div>
            <div class="success-check">
                <svg viewBox="0 0 50 50">
                    <path d="M14 27 L22 35 L37 16" />
                </svg>
            </div>
        </div>
        <div class="success-title">LINKED</div>
        <div class="success-subtitle">Neural handshake established</div>
        <div class="success-device" id="deviceInfo">Downloading bundle...</div>
        <div class="particle-burst" id="particleBurst"></div>
    </div>
</div>

<script>
    /**
     * Create particle burst effect (unused, kept for potential future use)
     */
    const createParticleBurst = (container) => {
        container.innerHTML = '';
        const particleCount = 20;

        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            container.appendChild(particle);

            const angle = (i / particleCount) * Math.PI * 2;
            const distance = 100 + Math.random() * 100;
            const x = Math.cos(angle) * distance;
            const y = Math.sin(angle) * distance;

            gsap.fromTo(particle,
                {
                    x: 0,
                    y: 0,
                    scale: 1,
                    opacity: 1
                },
                {
                    x: x,
                    y: y,
                    scale: 0,
                    opacity: 0,
                    duration: 1 + Math.random() * 0.5,
                    ease: 'power2.out',
                    delay: Math.random() * 0.2
                }
            );
        }
    };

    /**
     * 1. THREE.JS BACKGROUND ANIMATION
     * Creates a floating network of particles.
     */
    const initThree = () => {
        const container = document.getElementById('canvas-container');
        const scene = new THREE.Scene();

        // Camera setup
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        camera.position.z = 30;

        // Renderer setup
        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        container.appendChild(renderer.domElement);

        // Particles
        const geometry = new THREE.BufferGeometry();
        const count = 400; // Number of particles
        const positions = new Float32Array(count * 3);
        const velocities = []; // To move them

        for(let i = 0; i < count * 3; i++) {
            positions[i] = (Math.random() - 0.5) * 100; // Spread wide
            if (i % 3 === 0) velocities.push((Math.random() - 0.5) * 0.05); // x vel
            if (i % 3 === 1) velocities.push((Math.random() - 0.5) * 0.05); // y vel
            if (i % 3 === 2) velocities.push((Math.random() - 0.5) * 0.05); // z vel
        }

        geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));

        const material = new THREE.PointsMaterial({
            size: 0.4,
            color: 0x00f3ff,
            transparent: true,
            opacity: 0.8,
            blending: THREE.AdditiveBlending
        });

        const particlesMesh = new THREE.Points(geometry, material);
        scene.add(particlesMesh);

        // Lines connecting close particles
        const lineMaterial = new THREE.LineBasicMaterial({
            color: 0xbc13fe,
            transparent: true,
            opacity: 0.15
        });

        // Mouse Interaction
        let mouseX = 0;
        let mouseY = 0;
        let targetX = 0;
        let targetY = 0;

        const windowHalfX = window.innerWidth / 2;
        const windowHalfY = window.innerHeight / 2;

        document.addEventListener('mousemove', (event) => {
            mouseX = (event.clientX - windowHalfX);
            mouseY = (event.clientY - windowHalfY);
        });

        // Animation Loop
        const animate = () => {
            requestAnimationFrame(animate);

            targetX = mouseX * 0.001;
            targetY = mouseY * 0.001;

            // Rotate entire system slowly based on mouse
            particlesMesh.rotation.y += 0.001; // Constant rotation
            particlesMesh.rotation.x += 0.05 * (targetY - particlesMesh.rotation.x);
            particlesMesh.rotation.y += 0.05 * (targetX - particlesMesh.rotation.y);

            // Update particle positions
            const positions = particlesMesh.geometry.attributes.position.array;

            for(let i = 0; i < count; i++) {
                // Update X
                positions[i * 3] += velocities[i * 3];
                // Update Y
                positions[i * 3 + 1] += velocities[i * 3 + 1];
                // Update Z
                positions[i * 3 + 2] += velocities[i * 3 + 2];

                // Boundary Check (reset if too far)
                if(Math.abs(positions[i * 3]) > 50) positions[i * 3] *= -0.9;
                if(Math.abs(positions[i * 3 + 1]) > 50) positions[i * 3 + 1] *= -0.9;
                if(Math.abs(positions[i * 3 + 2]) > 50) positions[i * 3 + 2] *= -0.9;
            }

            particlesMesh.geometry.attributes.position.needsUpdate = true;

            renderer.render(scene, camera);
        };

        animate();

        // Resize Handler
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    };

    /**
     * 2. GSAP ANIMATIONS
     * Smooth reveal of UI elements.
     */
    const initAnimations = () => {
        const tl = gsap.timeline({ defaults: { ease: "power3.out" } });

        // Animate Card Entry
        tl.to(".interface-container", {
            duration: 1.5,
            opacity: 1,
            y: 0,
            delay: 0.2
        });

        // Animate Internal Elements Staggered
        tl.from("h1", {
            y: 20,
            opacity: 0,
            duration: 0.8
        }, "-=1.0");

        tl.from(".app-name", {
            y: 10,
            opacity: 0,
            duration: 0.8
        }, "-=0.6");

        tl.from(".qr-frame", {
            scale: 0.8,
            opacity: 0,
            duration: 0.8,
            ease: "back.out(1.7)"
        }, "-=0.6");

        tl.from(".instructions", {
            opacity: 0,
            y: 10,
            duration: 0.6
        }, "-=0.4");

        tl.from(".connection-status", {
            opacity: 0,
            y: 20,
            duration: 0.8
        }, "-=0.4");

        // Decorative lines expansion
        tl.from(".hud-line", {
            width: 0,
            duration: 1,
            stagger: 0.1
        }, "-=1.2");
    };

    // Initialize Everything
    window.onload = () => {
        initThree();
        initAnimations();
    };

</script>
</body>
</html>