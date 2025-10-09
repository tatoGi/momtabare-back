const mix = require('laravel-mix');

mix
  .js('resources/js/app.js', 'public/js')
  .postCss('resources/css/app.css', 'public/css', [
    require('tailwindcss'),
  ])
  .version() // Cache busting for production
  .options({
    // Reduce memory usage
    terser: {
      extractComments: false, // Avoid generating large comment files
      compress: {
        drop_console: true, // Remove console.log in production
      },
    },
    // Disable source maps in production to save memory
    sourceMaps: process.env.NODE_ENV !== 'development',
    // Limit concurrency to avoid memory overload
    concurrency: 1,
  })
  .disableNotifications() // Turn off build notifications to save resources
  .setPublicPath('public');

// Optional: Split large JS/CSS files if your project is big
if (mix.inProduction()) {
  mix.extract(); // Extract common dependencies into a separate file
}

// Avoid WebAssembly memory issues by disabling wasm-hash if needed
if (process.env.DISABLE_WASM_HASH) {
  const fs = require('fs');
  const path = require('path');
  const hashPath = path.resolve(__dirname, 'node_modules/webpack/lib/util/hash/wasm-hash.js');
  if (fs.existsSync(hashPath)) {
    const content = fs.readFileSync(hashPath, 'utf8').replace('createWasmHash', '() => {}');
    fs.writeFileSync(hashPath, content);
  }
}