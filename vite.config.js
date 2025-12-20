import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
  // Carga variables .env para el modo (dev / production)
  const env = loadEnv(mode, process.cwd(), '');

  // Usa VITE_BASE si está definido en .env, por ejemplo: VITE_BASE=/genack/public/
  // Si no está, por defecto se usa '/' (raíz).
  const base = env.VITE_BASE || '/';

  return {
    base,
    plugins: [
      laravel({
        input: ['resources/css/app.css', 'resources/js/app.js'],
        refresh: true,
      }),
      tailwindcss(),
    ],
  };
});