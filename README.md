## Cara menjalankan

**Kebutuhan:** PHP 8.0 atau lebih baru (disertakan di XAMPP). MySQL **tidak** diperlukan,
data pengguna disimpan di file `users.json`.

1. Buka terminal di folder proyek (di VS Code: klik kanan folder, lalu **Open in Integrated Terminal**).
2. Jalankan server bawaan PHP:
```bash
   php -S localhost:8001
```
3. Buka `http://localhost:8001` di browser (port 8001).
4. Daftar akun baru lewat halaman registrasi, lalu login untuk membuka dashboard.
5. Hentikan server dengan **Ctrl + C**.

**Catatan:**
- Live Server **tidak** bisa menjalankan PHP, gunakan perintah di atas.
- Jika file pintu masuk ada di subfolder (misalnya `public/`), jalankan
  `php -S localhost:8001 -t public`.
- `users.json` hanya berisi data contoh. Password disimpan dalam bentuk hash (`password_hash`).