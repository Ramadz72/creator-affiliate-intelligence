# creator-affiliate-intelligence

## Deploy produksi

1. `composer install --no-dev --optimize-autoloader`
2. Salin `.env.production.example` ke `.env`, lalu isi `APP_KEY`, `APP_URL`, kredensial DB/mail/Google OAuth, `TRUSTED_PROXIES`, dan `MAX_REQUEST_BYTES`.
3. `php artisan migrate --force`
4. `php artisan storage:link`
5. `npm ci && npm run build:ssr`
6. `php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan event:cache`
7. Jalankan queue worker (`php artisan queue:work`) dan proses SSR (`node bootstrap/ssr/ssr.js`) di process manager.
8. Pastikan `upload_max_filesize`, `post_max_size`, dan batas body web server minimal sama dengan `MAX_REQUEST_BYTES` (default 25 MB).

`composer setup` untuk development juga sudah membangun bundle SSR. CI menjalankan `composer setup` sebelum `composer ci:check`.
