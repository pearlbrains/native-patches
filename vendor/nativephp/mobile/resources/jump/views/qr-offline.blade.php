<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jump | Bifrost Technology</title>

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
            font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: var(--bg);
            color: #ffffff;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* CSS Animated Background */
        .bg-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .bg-particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--primary);
            border-radius: 50%;
            opacity: 0.6;
            animation: float 20s infinite linear;
        }

        .bg-particle:nth-child(even) {
            background: var(--secondary);
            animation-duration: 25s;
        }

        .bg-particle:nth-child(3n) {
            width: 2px;
            height: 2px;
            animation-duration: 30s;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) translateX(0) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 0.6;
            }
            90% {
                opacity: 0.6;
            }
            100% {
                transform: translateY(-100vh) translateX(100px) rotate(720deg);
                opacity: 0;
            }
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
            padding: 2px;
            background: linear-gradient(135deg, rgba(0, 243, 255, 0.3), rgba(188, 19, 254, 0.3));
            border-radius: 24px;
            box-shadow: 0 0 50px rgba(0, 243, 255, 0.15);
            animation: fadeInUp 1s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            animation: expandLine 1s ease-out 0.5s both;
        }
        .hud-tl { top: 20px; left: 20px; width: 20px; height: 2px; }
        .hud-tr { top: 20px; right: 20px; width: 20px; height: 2px; }

        @keyframes expandLine {
            from { width: 0; }
            to { width: 20px; }
        }

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
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Mono', 'Droid Sans Mono', 'Source Code Pro', monospace;
            font-weight: 700;
            font-size: 3rem;
            letter-spacing: -2px;
            margin-bottom: 5px;
            background: linear-gradient(90deg, #fff, var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-transform: uppercase;
            animation: fadeIn 0.8s ease-out 0.3s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .app-name {
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Mono', 'Droid Sans Mono', 'Source Code Pro', monospace;
            color: #888;
            font-size: 0.9rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            animation: fadeIn 0.8s ease-out 0.4s both;
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
            animation: scaleIn 0.8s cubic-bezier(0.34, 1.56, 0.64, 1) 0.5s both;
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .qr-code {
            width: 100%;
            height: 100%;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            background: white;
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
            animation: fadeIn 0.6s ease-out 0.7s both;
        }

        /* Connection Badge */
        .connection-status {
            background: rgba(0, 243, 255, 0.05);
            border: 1px solid rgba(0, 243, 255, 0.2);
            padding: 15px;
            border-radius: 12px;
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Mono', 'Droid Sans Mono', 'Source Code Pro', monospace;
            font-size: 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 8px;
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.8s ease-out 0.8s both;
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
    </style>
</head>
<body>

<!-- CSS Animated Background -->
<div class="bg-container">
    <div class="bg-particle" style="left: 5%; animation-delay: 0s;"></div>
    <div class="bg-particle" style="left: 15%; animation-delay: 2s;"></div>
    <div class="bg-particle" style="left: 25%; animation-delay: 4s;"></div>
    <div class="bg-particle" style="left: 35%; animation-delay: 1s;"></div>
    <div class="bg-particle" style="left: 45%; animation-delay: 3s;"></div>
    <div class="bg-particle" style="left: 55%; animation-delay: 5s;"></div>
    <div class="bg-particle" style="left: 65%; animation-delay: 0.5s;"></div>
    <div class="bg-particle" style="left: 75%; animation-delay: 2.5s;"></div>
    <div class="bg-particle" style="left: 85%; animation-delay: 4.5s;"></div>
    <div class="bg-particle" style="left: 95%; animation-delay: 1.5s;"></div>
    <div class="bg-particle" style="left: 10%; animation-delay: 6s;"></div>
    <div class="bg-particle" style="left: 30%; animation-delay: 7s;"></div>
    <div class="bg-particle" style="left: 50%; animation-delay: 8s;"></div>
    <div class="bg-particle" style="left: 70%; animation-delay: 9s;"></div>
    <div class="bg-particle" style="left: 90%; animation-delay: 10s;"></div>
</div>
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

        <h1>JUMP</h1>
        <div class="app-name">
            BIFROST TECHNOLOGY
        </div>

        <div class="qr-frame">
            <div class="scan-line"></div>
            <div class="qr-code">
                <img src="{{ $qrCodeDataUri }}" alt="Scan to Connect">
            </div>
        </div>

        <div class="instructions">
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

</body>
</html>
