# API Documentation - Attendance Data

## Base URL
```
https://absen-monday.online/api
```

## Endpoints

### 1. Get All Attendance Data
Mengambil semua data absensi dengan filter dan pagination.

**Endpoint:** `GET /api/attendance`

**Query Parameters:**
- `start_date` (optional) - Filter mulai tanggal (format: YYYY-MM-DD)
- `end_date` (optional) - Filter sampai tanggal (format: YYYY-MM-DD)
- `token` (optional) - Filter berdasarkan token
- `unit_source` (optional) - Filter berdasarkan unit source
- `name` (optional) - Search berdasarkan nama (partial match)
- `division` (optional) - Filter berdasarkan divisi (partial match)
- `per_page` (optional) - Jumlah data per halaman (default: 15, max: 100)
- `page` (optional) - Halaman yang ingin ditampilkan (default: 1)

**Example Request:**
```bash
# Get all attendance
GET /api/attendance

# Get attendance with date range
GET /api/attendance?start_date=2024-01-01&end_date=2024-01-31

# Get attendance by token
GET /api/attendance?token=ATT-42GMCJG6

# Get attendance with pagination
GET /api/attendance?per_page=20&page=2

# Get attendance by unit source
GET /api/attendance?unit_source=UPKD

# Search by name
GET /api/attendance?name=John
```

**Response Success (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "division": "IT",
      "position": "Developer",
      "token": "ATT-42GMCJG6",
      "time": "2024-01-15 08:30:00",
      "signature": "data:image/png;base64,iVBORw0KG...",
      "unit_source": "UPKD",
      "is_backdate": false,
      "backdate_reason": null,
      "source_ip": "192.168.1.1",
      "user_agent": "Mozilla/5.0...",
      "created_at": "2024-01-15T08:30:00.000000Z",
      "updated_at": "2024-01-15T08:30:00.000000Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75,
    "from": 1,
    "to": 15
  }
}
```

---

### 2. Get Single Attendance Data
Mengambil data absensi berdasarkan ID.

**Endpoint:** `GET /api/attendance/{id}`

**URL Parameters:**
- `id` (required) - ID dari attendance record

**Example Request:**
```bash
GET /api/attendance/1
```

**Response Success (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "division": "IT",
    "position": "Developer",
    "token": "ATT-42GMCJG6",
    "time": "2024-01-15 08:30:00",
    "signature": "data:image/png;base64,iVBORw0KG...",
    "unit_source": "UPKD",
    "is_backdate": false,
    "backdate_reason": null,
    "source_ip": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "created_at": "2024-01-15T08:30:00.000000Z",
    "updated_at": "2024-01-15T08:30:00.000000Z"
  }
}
```

**Response Error (404):**
```json
{
  "success": false,
  "message": "Attendance not found"
}
```

---

## Cara Menggunakan dari Web Lain

### 1. Menggunakan cURL (PHP)
```php
<?php
$url = 'https://absen-monday.online/api/attendance?start_date=2024-01-01&end_date=2024-01-31';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if ($data['success']) {
    foreach ($data['data'] as $attendance) {
        echo $attendance['name'] . ' - ' . $attendance['time'] . "\n";
    }
}
?>
```

### 2. Menggunakan JavaScript/Fetch
```javascript
fetch('https://absen-monday.online/api/attendance?start_date=2024-01-01&end_date=2024-01-31', {
    method: 'GET',
    headers: {
        'Accept': 'application/json'
    }
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        data.data.forEach(attendance => {
            console.log(attendance.name, attendance.time);
        });
    }
})
.catch(error => console.error('Error:', error));
```

### 3. Menggunakan Axios (JavaScript)
```javascript
const axios = require('axios');

axios.get('https://absen-monday.online/api/attendance', {
    params: {
        start_date: '2024-01-01',
        end_date: '2024-01-31',
        per_page: 50
    },
    headers: {
        'Accept': 'application/json'
    }
})
.then(response => {
    if (response.data.success) {
        console.log('Total:', response.data.pagination.total);
        response.data.data.forEach(attendance => {
            console.log(attendance);
        });
    }
})
.catch(error => {
    console.error('Error:', error);
});
```

### 4. Menggunakan Guzzle (PHP Laravel)
```php
use Illuminate\Support\Facades\Http;

$response = Http::get('https://absen-monday.online/api/attendance', [
    'start_date' => '2024-01-01',
    'end_date' => '2024-01-31',
    'per_page' => 50
]);

if ($response->successful() && $response->json('success')) {
    $attendances = $response->json('data');
    foreach ($attendances as $attendance) {
        // Process attendance data
    }
}
```

### 5. Menggunakan Python Requests
```python
import requests

url = 'https://absen-monday.online/api/attendance'
params = {
    'start_date': '2024-01-01',
    'end_date': '2024-01-31',
    'per_page': 50
}

response = requests.get(url, params=params, headers={'Accept': 'application/json'})
data = response.json()

if data['success']:
    for attendance in data['data']:
        print(f"{attendance['name']} - {attendance['time']}")
```

---

## Pagination

API menggunakan pagination untuk membatasi jumlah data yang dikembalikan. Default adalah 15 data per halaman, maksimal 100 data per halaman.

**Contoh Navigasi Pagination:**
```bash
# Halaman 1 (default)
GET /api/attendance?per_page=20

# Halaman 2
GET /api/attendance?per_page=20&page=2

# Halaman terakhir
GET /api/attendance?per_page=20&page={last_page}
```

---

## Error Handling

Semua error response mengikuti format:
```json
{
  "success": false,
  "message": "Error message"
}
```

**HTTP Status Codes:**
- `200` - Success
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## Notes

1. **Signature Data**: Signature disimpan dalam format base64 image (data:image/png;base64,...)
2. **Time Format**: Waktu absensi menggunakan format datetime database (YYYY-MM-DD HH:MM:SS)
3. **Date Filter**: Filter tanggal menggunakan format YYYY-MM-DD
4. **Rate Limiting**: Saat ini belum ada rate limiting, tapi disarankan untuk tidak melakukan request terlalu sering
