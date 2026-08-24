<?php
$rutaLogoSvg = dirname(__DIR__) . '/img/logo_3d_seduc.svg';
$textoAlternativoLogoCarga = isset($textoAlternativoLogoCarga) && trim((string)$textoAlternativoLogoCarga) !== ''
    ? trim((string)$textoAlternativoLogoCarga)
    : 'Logo institucional SEDUC';
$contenidoLogoSvg = '';
if (is_file($rutaLogoSvg)) {
    $contenidoLogoSvg = (string)file_get_contents($rutaLogoSvg);
    // El SVG se incrusta dentro de HTML: se retiran cabecera XML y DOCTYPE,
    // que solo corresponden cuando el archivo se sirve como documento propio.
    $contenidoLogoSvg = preg_replace('/<\?xml[^?]*\?>/i', '', $contenidoLogoSvg);
    $contenidoLogoSvg = preg_replace('/<!DOCTYPE[^>]*>/is', '', $contenidoLogoSvg);
}

if (empty($GLOBALS['seduc_loader_logo_estilos_emitidos'])) {
    $GLOBALS['seduc_loader_logo_estilos_emitidos'] = true;
    ?>
    <style>
        .seduc-loader-logo {
            --seduc-logo-size: 105px;
            --seduc-rays-size: 138px;
            position: relative;
            display: grid;
            place-items: center;
            width: var(--seduc-rays-size);
            height: var(--seduc-rays-size);
            margin: 0 auto;
        }
        .seduc-loader-logo__center {
            position: relative;
            z-index: 2;
            display: grid;
            place-items: center;
            width: var(--seduc-logo-size);
            height: var(--seduc-logo-size);
        }
        .seduc-loader-logo__center svg {
            display: block;
            width: 100%;
            height: 100%;
            overflow: visible;
        }
        .seduc-loader-logo__center svg path { fill: #73777d; }
        .seduc-loader-logo__fallback {
            color: #334155;
            font: 700 .85rem/1.2 system-ui, -apple-system, "Segoe UI", sans-serif;
            text-align: center;
        }
        .seduc-loader-logo__rays {
            position: absolute;
            z-index: 1;
            inset: 0;
            pointer-events: none;
        }
        .seduc-loader-logo__ray {
            --angle: 0deg;
            --delay: 0s;
            --ray-color: #176fb2;
            position: absolute;
            top: 0;
            left: 50%;
            width: 7px;
            height: 19px;
            border-radius: 999px;
            background: var(--ray-color);
            opacity: .18;
            transform: translateX(-50%) rotate(var(--angle));
            transform-origin: 50% calc(var(--seduc-rays-size) / 2);
            animation: seducRayLoading 1.35s ease-in-out infinite;
            animation-delay: var(--delay);
        }
        .seduc-loader-logo__ray:nth-child(1)  { --angle: -75deg; --delay: 0s;    --ray-color: #1779ba; }
        .seduc-loader-logo__ray:nth-child(2)  { --angle: -50deg; --delay: .11s; --ray-color: #2f91bd; }
        .seduc-loader-logo__ray:nth-child(3)  { --angle: -25deg; --delay: .22s; --ray-color: #68ad72; }
        .seduc-loader-logo__ray:nth-child(4)  { --angle: 0deg;   --delay: .33s; --ray-color: #69ae72; }
        .seduc-loader-logo__ray:nth-child(5)  { --angle: 25deg;  --delay: .44s; --ray-color: #d9dde1; }
        .seduc-loader-logo__ray:nth-child(6)  { --angle: 50deg;  --delay: .55s; --ray-color: #eef1f3; }
        .seduc-loader-logo__ray:nth-child(7)  { --angle: 75deg;  --delay: .66s; --ray-color: #f3c45b; }
        .seduc-loader-logo__ray:nth-child(8)  { --angle: 100deg; --delay: .77s; --ray-color: #f0ae45; }
        .seduc-loader-logo__ray:nth-child(9)  { --angle: 125deg; --delay: .88s; --ray-color: #efbd61; }
        .seduc-loader-logo__ray:nth-child(10) { --angle: 155deg; --delay: .99s; --ray-color: #7bb17a; }
        .seduc-loader-logo__ray:nth-child(11) { --angle: 205deg; --delay: 1.10s; --ray-color: #5aa56d; }
        .seduc-loader-logo__ray:nth-child(12) { --angle: 255deg; --delay: 1.21s; --ray-color: #2583bc; }
        @keyframes seducRayLoading {
            0%, 100% { opacity: .16; filter: brightness(.9); }
            25% { opacity: 1; filter: brightness(1.18) drop-shadow(0 0 4px var(--ray-color)); }
            50% { opacity: .36; }
        }
        @media (max-width: 480px) {
            .seduc-loader-logo { --seduc-logo-size: 88px; --seduc-rays-size: 116px; }
            .seduc-loader-logo__ray { width: 6px; height: 16px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .seduc-loader-logo__ray { animation: none !important; opacity: .72; filter: none; }
        }
    </style>
    <?php
}
?>
<div class="seduc-loader-logo" role="img" aria-label="<?= htmlspecialchars($textoAlternativoLogoCarga, ENT_QUOTES, 'UTF-8') ?>">
    <div class="seduc-loader-logo__rays" aria-hidden="true">
        <?php for ($seducRay = 0; $seducRay < 12; $seducRay++): ?><span class="seduc-loader-logo__ray"></span><?php endfor; ?>
    </div>
    <div class="seduc-loader-logo__center">
        <?php if (trim($contenidoLogoSvg) !== ''): ?>
            <?= $contenidoLogoSvg ?>
        <?php else: ?>
            <span class="seduc-loader-logo__fallback">SEDUC</span>
        <?php endif; ?>
    </div>
</div>
