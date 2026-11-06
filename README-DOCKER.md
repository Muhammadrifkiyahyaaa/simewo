# Menjalankan SIMEWO dengan Docker

Yang dibutuhkan hanya **Docker Desktop** (sudah termasuk Docker Compose).
Tidak perlu PHP, Composer, Node.js, ataupun MySQL di laptop.

## Langkah

1. Pastikan Docker Desktop sudah berjalan.
2. Buka terminal di folder project ini (folder yang berisi `docker-compose.yml`).
3. Jalankan:

   ```bash
   docker compose up -d --build
   ```

   Build pertama membutuhkan beberapa menit (mengunduh image dan dependency).
4. Buka **http://localhost:8000** di browser.
5. Login memakai akun yang ada di database (`simewo.sql`).

Database terisi otomatis dari file `simewo.sql` saat pertama kali dijalankan.

## Perintah berguna

| Tujuan                              | Perintah                                         |
|-------------------------------------|--------------------------------------------------|
| Melihat status                      | `docker compose ps`                              |
| Melihat log aplikasi                | `docker compose logs -f app`                     |
| Menjalankan pengecekan manual       | `docker compose exec app php artisan monitoring:run` |
| Menghentikan                        | `docker compose down`                            |
| Reset total (hapus data DB) + ulang | `docker compose down -v` lalu `docker compose up -d --build` |

## Catatan

- Port 8000 bentrok? Ubah `"8000:80"` di `docker-compose.yml` (mis. `"8080:80"`) dan `APP_URL`.
- Pengecekan otomatis berjalan lewat service `scheduler` (jam 22:00 dan 23:00 WITA).
- Password dan `APP_KEY` di `docker-compose.yml` hanya untuk demo/pengujian lokal.
  Untuk server sungguhan, ganti semuanya.
