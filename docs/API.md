# Dokumentasi REST API ChargeBing (v1)

Base URL: `http://localhost:8080/api/v1`  
Header Autentikasi: `Authorization: Bearer <TOKEN>`  
Header Request: `Accept: application/json`, `Content-Type: application/json`

---

## 1. Autentikasi & Profil Pengguna

### POST `/auth/login`
Rate limited: 5 request / menit.

**Request:**
```json
{
  "email": "admin@chargebing.local",
  "password": "password123",
  "device_name": "iPhone 16 Pro"
}
```

**Response (200 OK):**
```json
{
  "message": "Authentication successful.",
  "token": "1|qXyZ...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Admin ChargeBing",
    "email": "admin@chargebing.local",
    "ev_model_id": 4,
    "created_at": "2026-11-16T05:00:00.000000Z"
  }
}
```

### GET `/me` atau GET `/auth/me`
Mengambil detail akun beserta data mobil listrik aktif yang dipilih.

**Response (200 OK):**
```json
{
  "user": {
    "id": 1,
    "name": "Admin ChargeBing",
    "email": "admin@chargebing.local",
    "ev_model_id": 4,
    "ev_model": {
      "id": 4,
      "brand": "Wuling",
      "model_name": "BinguoEV",
      "variant": "410 km",
      "full_name": "Wuling BinguoEV (410 km)",
      "battery_capacity_kwh": 37.9,
      "range_km": 410,
      "ac_plug_type": "GBT",
      "dc_plug_type": "GBT",
      "has_dc": true,
      "max_ac_kw": 3.3,
      "max_dc_kw": 50.0,
      "is_active": true
    },
    "created_at": "2026-11-16T05:00:00.000000Z"
  }
}
```

### PUT `/me`
Memilih atau mengubah tipe mobil listrik aktif pemilik dari mobile app:

**Request:**
```json
{
  "ev_model_id": 4
}
```

**Response (200 OK):**
```json
{
  "message": "Profile updated successfully.",
  "user": {
    "id": 1,
    "name": "Admin ChargeBing",
    "email": "admin@chargebing.local",
    "ev_model_id": 4,
    "ev_model": { ... }
  }
}
```

### POST `/auth/logout`
Mencabut token aktif.

---

## 2. Master Mobil Listrik (EV Models)

Endpoint untuk mengambil katalog mobil listrik resmi di Indonesia sehingga pengguna iOS dapat langsung memilih merk/tipe kendaraannya.

### GET `/ev-models`
Mendukung query parameter:
- `?all=1`: Mengembalikan seluruh daftar tanpa pagination (ideal untuk picker / dropdown di iOS SwiftUI).
- `?q=`: Pencarian teks pada merk, model, dan varian (contoh: `?q=Binguo`).
- `?brand=`: Filter berdasarkan pabrikan (contoh: `?brand=Wuling`).
- `?has_dc=1`: Hanya mobil yang mendukung DC Fast Charging (`has_dc=0` untuk yang hanya port AC).
- `?plug_type=`: Filter berdasarkan colokan (`GBT`, `TYPE2`, `CCS2`, `CHADEMO`).

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 4,
      "brand": "Wuling",
      "model_name": "BinguoEV",
      "variant": "410 km",
      "full_name": "Wuling BinguoEV (410 km)",
      "battery_capacity_kwh": 37.9,
      "range_km": 410,
      "ac_plug_type": "GBT",
      "dc_plug_type": "GBT",
      "has_dc": true,
      "max_ac_kw": 3.3,
      "max_dc_kw": 50.0,
      "is_active": true,
      "notes": "Dual port GB/T (AC & DC Fast Charging)."
    },
    {
      "id": 7,
      "brand": "Hyundai",
      "model_name": "Ioniq 5",
      "variant": "Long Range",
      "full_name": "Hyundai Ioniq 5 (Long Range)",
      "battery_capacity_kwh": 72.6,
      "range_km": 481,
      "ac_plug_type": "TYPE2",
      "dc_plug_type": "CCS2",
      "has_dc": true,
      "max_ac_kw": 11.0,
      "max_dc_kw": 232.0,
      "is_active": true,
      "notes": "Arsitektur 800V ultra fast charging hingga 232 kW."
    }
  ]
}
```

### GET `/ev-models/{id}`
Detail spesifikasi satu tipe mobil listrik.

### POST `/ev-models`
Menambah tipe mobil listrik baru.

**Request:**
```json
{
  "brand": "Wuling",
  "model_name": "Cloud EV",
  "variant": "460 km",
  "battery_capacity_kwh": 50.6,
  "range_km": 460,
  "ac_plug_type": "GBT",
  "dc_plug_type": "GBT",
  "max_ac_kw": 6.6,
  "max_dc_kw": 60.0,
  "is_active": true,
  "notes": "Port GB/T AC 6.6 kW & DC Fast Charging."
}
```

### PUT `/ev-models/{id}`
Memperbarui data spesifikasi mobil listrik.

### DELETE `/ev-models/{id}`
Soft delete data mobil listrik.

---

## 3. Stasiun SPKLU (Stations)

### GET `/stations`
Parameter Filter:
- `?current_type=AC|DC`: Filter berdasarkan mode arus yang tersedia.
- `?plug_type=GBT|TYPE2|CCS2|CHADEMO`: Filter tipe colokan fisik.
- `?compat_status=native|adapter_ok|untested|incompatible`: Filter status kesesuaian.
- `?usable_only=1`: Hanya stasiun yang memiliki colokan dengan status `native` (langsung colok GB/T) atau `adapter_ok` (berhasil pakai adaptor).
- `?q=`: Pencarian nama stasiun, operator, atau alamat.
- `?lat=-6.1789&lng=106.8324`: Koordinat pengguna (otomatis mengurutkan hasil berdasarkan jarak terdekat dengan formula Haversine).
- `?radius_km=15`: Membatasi hasil hanya dalam radius kilometer tertentu dari `lat`/`lng`.
- `?updated_since=2026-11-01T00:00:00Z`: Sinkronisasi data inkremental untuk mobile app.

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "SPKLU PLN UID Jakarta Raya (Gambir)",
      "operator": "PLN SPKLU",
      "address": "Jl. M.I. Ridwan Rais No.1, Gambir, Jakarta Pusat",
      "latitude": -6.178921,
      "longitude": 106.832412,
      "pricing_note": "Rp 2.466 / kWh + PJU 3%",
      "opening_hours": "24 Jam",
      "notes": "Posisi charger dekat lobi gedung utama.",
      "is_favorite": true,
      "distance_km": 0.42,
      "connectors": [
        {
          "id": 1,
          "current_type": "DC",
          "plug_type": "GBT",
          "power_kw": 60.0,
          "unit_count": 2,
          "compat_status": "native",
          "adapter_note": null,
          "last_tested_at": "2026-11-13"
        }
      ]
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}
```

### GET `/stations/{id}`
Detail stasiun SPKLU beserta relasi konektor.

### POST `/stations`
Membuat data stasiun baru beserta repeater konektor.

**Request:**
```json
{
  "name": "SPKLU Rest Area KM 57 Tol Japek",
  "operator": "PLN SPKLU",
  "address": "Rest Area KM 57 Tol Jakarta - Cikampek",
  "latitude": -6.3751234,
  "longitude": 107.2845678,
  "opening_hours": "24 Jam",
  "pricing_note": "Rp 2.466 / kWh",
  "is_favorite": false,
  "connectors": [
    {
      "current_type": "DC",
      "plug_type": "GBT",
      "power_kw": 120.0,
      "unit_count": 2,
      "compat_status": "native"
    }
  ]
}
```

---

## 4. Konektor SPKLU (Station Connectors)

- `GET /stations/{station_id}/connectors`
- `POST /stations/{station_id}/connectors`
- `GET /stations/{station_id}/connectors/{id}`
- `PUT /stations/{station_id}/connectors/{id}`
- `DELETE /stations/{station_id}/connectors/{id}`

*Aturan*: Jika `plug_type = "GBT"`, `compat_status` otomatis default `'native'`. Colokan lain default `'untested'`.

---

## 5. Sesi Pengisian (Charging Sessions)

### GET `/charging-sessions`
Filter: `?charging_mode=AC|DC`, `?station_id=`, `?started_from=`, `?started_until=`.

### POST `/charging-sessions`
**Request:**
```json
{
  "station_id": 1,
  "connector_id": 1,
  "ev_model_id": 4,
  "charging_mode": "DC",
  "started_at": "2026-11-16T10:00:00",
  "ended_at": "2026-11-16T10:45:00",
  "kwh": 32.5,
  "cost": 83000,
  "soc_start": 20,
  "soc_end": 85,
  "notes": "Pengisian cepat DC GB/T dari 20% ke 85%."
}
```

---

## 6. Ringkasan Statistik (Stats Summary)

### GET `/stats/summary`
Mengembalikan akumulasi data pengisian dengan pemisahan AC vs DC untuk konsumsi baterai mobil listrik.

**Response (200 OK):**
```json
{
  "all_time": {
    "total_sessions": 25,
    "total_kwh": 745.8,
    "total_cost": 1890000,
    "ac": {
      "sessions": 10,
      "kwh": 185.0,
      "cost": 460000
    },
    "dc": {
      "sessions": 15,
      "kwh": 560.8,
      "cost": 1430000
    }
  },
  "current_month": {
    "month": "2026-11",
    "total_sessions": 4,
    "total_kwh": 128.4,
    "total_cost": 325000,
    "ac": { "sessions": 1, "kwh": 18.2, "cost": 45000 },
    "dc": { "sessions": 3, "kwh": 110.2, "cost": 280000 }
  },
  "monthly": [ ... ]
}
```

