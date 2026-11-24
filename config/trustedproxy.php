<?php

/*
| Proxy tepercaya (Cloudflare / load balancer / reverse proxy).
| Tanpa ini, di belakang proxy semua pendaki terbaca ber-IP sama sehingga
| rate-limit pendaftaran memblokir semua orang, dan URL https tidak terdeteksi.
| Isi TRUSTED_PROXIES dengan daftar IP/CIDR dipisah koma, atau "*" bila server
| hanya bisa diakses lewat proxy. Kosongkan bila Nginx langsung menerima trafik.
*/

$proxies = trim((string) env('TRUSTED_PROXIES', ''));

return [
    'proxies' => $proxies === '*' ? '*' : array_values(array_filter(array_map('trim', explode(',', $proxies)))),
];
