# Muhammadiyah ID SSO Setup

Integrasi Muhammadiyah ID menggunakan OAuth 2.0 authorization code flow. Dokumen ini hanya mencatat environment dan alur konfigurasi; jangan pernah commit credential asli.

## Environment

Tambahkan nilai berikut di `.env` lokal/production setelah OAuth client disetujui:

```env
MUHAMMADIYAH_ID_CLIENT_ID=
MUHAMMADIYAH_ID_CLIENT_SECRET=
MUHAMMADIYAH_ID_REDIRECT_URI="${APP_URL}/auth/muhammadiyah/callback"
MUHAMMADIYAH_ID_BASE_URL=https://sso.muhammadiyah.id
MUHAMMADIYAH_ID_API_URL=https://sso.muhammadiyah.id/api
MUHAMMADIYAH_ID_SCOPE=user-info
MUHAMMADIYAH_ID_REQUIRE_PRE_REGISTERED=true
```

Rekomendasi redirect URI untuk Developer Console Muhammadiyah ID:

```text
http://sistem-pesantrenmu.test/auth/muhammadiyah/callback
```

Untuk production, gunakan domain HTTPS production.

## Mode aman pre-registration

`MUHAMMADIYAH_ID_REQUIRE_PRE_REGISTERED=true` berarti user hanya bisa login jika sudah ada di tabel `users` melalui fitur **Tambah / Undang Pengguna** Super Admin. Matching dilakukan berdasarkan data Muhammadiyah ID berikut jika tersedia:

1. `sso_id`
2. `m_id`
3. `nbm`
4. `email`

Role lokal tetap menjadi sumber authorization aplikasi (`super_admin`, `admin`, `asesor`, `pesantren`). Field `role`, `level`, dan `group` dari Muhammadiyah ID disimpan sebagai metadata SSO, bukan langsung mengganti role lokal.

## Endpoint lokal

- `GET /auth/muhammadiyah/redirect` — redirect user ke OAuth authorize Muhammadiyah ID.
- `GET /auth/muhammadiyah/callback` — menerima `code` dan `state`, exchange token, ambil `/api/user-info`, lalu login user lokal.

## Catatan keamanan

- Jangan commit nilai `client_secret` ke repository.
- Gunakan HTTPS untuk production redirect URI.
- `state` OAuth disimpan di session dan divalidasi di callback.
- Access token dipakai hanya untuk mengambil `/api/user-info`; token tidak disimpan permanen.
- User dengan `status=inactive` tetap ditolak walaupun autentikasi SSO berhasil.
