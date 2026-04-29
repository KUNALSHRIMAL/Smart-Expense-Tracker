<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="theme-color" content="#080808">
  <meta name="description" content="Smart Expense Tracker — Personal Monthly Money Manager">
  <title><?= APP_NAME ?></title>
  <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
  <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/icon.php?size=192">
  <!-- Set config on window object before CDN loads so it never throws ReferenceError -->
  <script>
    window.tailwind = {
      config: {
        theme: {
          extend: {
            colors: {
              surface:  '#1c1c1e',
              surface2: '#2c2c2e',
              bdr:      '#3a3a3c',
            },
            fontFamily: {
              sans: ['-apple-system','BlinkMacSystemFont','SF Pro Display','Segoe UI','system-ui','sans-serif'],
            },
          }
        }
      }
    };
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    *{-webkit-tap-highlight-color:transparent;box-sizing:border-box}
    html,body{background:#080808;min-height:100vh;overscroll-behavior-y:none}
    /* Explicit fallbacks so cards/inputs are visible even before Tailwind CDN parses config */
    .bg-surface  { background-color: #1c1c1e !important; }
    .bg-surface2 { background-color: #2c2c2e !important; }
    .border-bdr  { border-color:     #3a3a3c !important; }
    ::-webkit-scrollbar{display:none}
    .pb-nav{padding-bottom:calc(72px + env(safe-area-inset-bottom,0px))}
    /* Fallbacks so form elements are usable if Tailwind CDN fails to load.
       Element selectors (specificity 0,0,1) lose to Tailwind classes (0,1,0),
       so these are silently overridden when the CDN works fine. */
    input,select,textarea{-webkit-appearance:none;outline:none;width:100%;background:#2c2c2e;border:1px solid #3a3a3c;border-radius:.875rem;padding:.875rem 1rem;color:#fff;font-size:1rem}
    button[type=submit],button[type=button]{cursor:pointer}
    input[type="date"]::-webkit-calendar-picker-indicator{filter:invert(1) opacity(.5)}
    select option{background:#1c1c1e}
    .toast{transform:translateY(120px);opacity:0;transition:all .35s cubic-bezier(.34,1.56,.64,1);pointer-events:none}
    .toast.show{transform:translateY(0);opacity:1}
    .tap:active{transform:scale(.96);transition:transform .1s ease}
    .slide-up{animation:slideUp .3s ease-out both}
    .slide-up-2{animation:slideUp .3s .05s ease-out both}
    .slide-up-3{animation:slideUp .3s .1s ease-out both}
    @keyframes slideUp{from{transform:translateY(18px);opacity:0}to{transform:translateY(0);opacity:1}}
    .num{font-variant-numeric:tabular-nums}
    .bar{transition:width .9s cubic-bezier(.34,1.56,.64,1)}
    .modal-bg{backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px)}
    .ring-violet{box-shadow:0 0 0 2px #8b5cf6}
    :focus-visible{outline:2px solid #8b5cf6;outline-offset:2px}
    /* Smooth scroll for horizontal shortcut strip */
    .scroll-x{overflow-x:auto;-webkit-overflow-scrolling:touch;scroll-snap-type:x mandatory}
    .scroll-x>*{scroll-snap-align:start}
  </style>
</head>
