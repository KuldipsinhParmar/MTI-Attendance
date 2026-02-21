# MTI Attendance System — REST API Documentation

> **Base URL:** `http://<your-server>/api`
> **Local Dev:** `http://localhost:8082/api`
> **Live Server:** `https://attendance.marutitechinnovation.in/api`
> **Format:** All requests and responses use `Content-Type: application/json`
> **Auth:** Employee login uses username + password (bcrypt). No API key on other endpoints — consider adding one for production.

---

## 🐛 Bugs Fixed (Pre-documentation audit)

| # | File | Bug | Fix Applied |
|---|------|-----|-------------|
| 1 | `AttendanceApi.php` | `$settings->get()` called but method is `getSetting()` — PHP fatal error on scan | Fixed: Changed to `->getSetting()` |
| 2 | `ReportApi.php` | `export()` returned `redirect()` — HTML response breaks mobile clients | Fixed: Now returns JSON with `export_url` |
| 3 | `ReportApi.php` | No auth on API group — anyone can query employee data | Known issue — add API key filter before production |

---

## 📋 Table of Contents

1. [Auth APIs](#0-auth-apis)
2. [Attendance APIs](#1-attendance-apis)
3. [Employee APIs](#2-employee-apis)
4. [QR Code APIs](#3-qr-code-apis)
5. [Report APIs](#4-report-apis)
6. [Map / Live View API](#5-map--live-view-api)
7. [Error Response Format](#6-error-response-format)
8. [Data Models Reference](#7-data-models-reference)
9. [Flutter Integration Guide](#8-flutter-integration-guide)

---

## 0. Auth APIs

Used by the **mobile app** to authenticate employees. Credentials (username + bcrypt password) are managed from the **web admin panel** (Employees → Create/Edit).

---

### 0.1 Employee Login

| Field | Value |
|-------|-------|
| **URL** | `POST /api/auth/login` |
| **Auth** | None required |

#### Request Body (JSON)

```json
{
  "username": "john.doe",
  "password": "secret123"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `username` | `string` | ✅ Yes | Employee's login username (set by admin) |
| `password` | `string` | ✅ Yes | Plain-text password (compared against bcrypt hash) |

#### Response — Success (200 OK)

```json
{
  "status": "success",
  "message": "Login successful.",
  "data": {
    "id": 1,
    "employee_code": "EMP0001",
    "username": "john.doe",
    "name": "John Doe",
    "email": "john@mti.com",
    "phone": "9876543210",
    "department": "Engineering",
    "designation": "Developer",
    "photo": null
  }
}
```

#### Error Responses

| HTTP Code | Reason |
|-----------|--------|
| `401` | Invalid username or password |
| `403` | Account not activated (password not set by admin yet) |
| `422` | Missing `username` or `password` field |

---

### 0.2 Set / Reset Employee Password *(Admin Use)*

Used by admins to provision or reset an employee's login credentials. Can also be done via the **web admin panel**.

| Field | Value |
|-------|-------|
| **URL** | `POST /api/auth/set-password` |
| **Auth** | None required *(protect this in production!)* |

#### Request Body (JSON)

```json
{
  "employee_code": "EMP0001",
  "username": "john.doe",
  "password": "newpassword123"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `employee_code` | `string` | ✅ Yes | Employee's system code (e.g. `EMP0001`) |
| `username` | `string` | ✅ Yes | New or existing username |
| `password` | `string` | ✅ Yes | Plain-text password (min 6 chars, stored as bcrypt) |

#### Response — Success (200 OK)

```json
{
  "status": "success",
  "message": "Credentials updated successfully."
}
```

#### Error Responses

| HTTP Code | Reason |
|-----------|--------|
| `404` | Employee not found |
| `422` | Missing field or password < 6 chars |

---

## 1. Attendance APIs

### 1.1 Scan QR Code (Check-In / Check-Out)

Mark attendance by scanning a QR code. The system automatically toggles between `check_in` and `check_out` based on the employee's last scan today.

| Field | Value |
|-------|-------|
| **URL** | `POST /api/attendance/scan` |
| **Auth** | None required |

#### Request Body (JSON)

```json
{
  "employee_id": 1,
  "qr_token": "a3f8b2c1d4e5f6a7b8c9d0e1f2a3b4c5",
  "latitude": 23.0225,
  "longitude": 72.5714
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `employee_id` | `integer` | ✅ Yes | Employee's database ID |
| `qr_token` | `string` | ✅ Yes | Token string from the QR code |
| `latitude` | `float` | ❌ Optional | GPS latitude at scan time |
| `longitude` | `float` | ❌ Optional | GPS longitude at scan time |

#### Response — Success (200 OK)

```json
{
  "status": "success",
  "type": "check_in",
  "employee": "John Doe",
  "employee_code": "EMP0001",
  "location": "Main Office",
  "time": "09:15:30",
  "geofence_status": "inside",
  "message": null
}
```

#### Response — Flagged (Outside Geofence) (200 OK)

```json
{
  "status": "flagged",
  "type": "check_in",
  "employee": "John Doe",
  "employee_code": "EMP0001",
  "location": "Main Office",
  "time": "09:15:30",
  "geofence_status": "flagged",
  "message": "You are 120m away from Main Office (allowed: 50m). Attendance marked but flagged for review."
}
```

| Response Field | Type | Description |
|----------------|------|-------------|
| `status` | `string` | `success` or `flagged` |
| `type` | `string` | `check_in` or `check_out` |
| `employee` | `string` | Employee full name |
| `employee_code` | `string` | Employee code (e.g. EMP0001) |
| `location` | `string` | QR code location name |
| `time` | `string` | Server time of scan (HH:mm:ss) |
| `geofence_status` | `string` | `inside` or `flagged` |
| `message` | `string\|null` | Human-readable message if flagged |

#### Error Responses

| HTTP Code | Reason |
|-----------|--------|
| `422` | Missing `employee_id` or `qr_token` |
| `404` | Invalid/inactive QR token |
| `404` | Employee not found or inactive |

---

### 1.2 Get Today's Attendance for an Employee

Fetch all scan records for the current day.

| Field | Value |
|-------|-------|
| **URL** | `GET /api/attendance/today?employee_id={id}` |
| **Auth** | None required |

#### Query Parameters

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `employee_id` | `integer` | ✅ Yes | Employee's database ID |

#### Response (200 OK)

```json
{
  "status": "success",
  "data": [
    {
      "id": 101,
      "employee_id": 1,
      "qr_token_id": 2,
      "type": "check_in",
      "scan_latitude": "23.02250000",
      "scan_longitude": "72.57140000",
      "geofence_status": "inside",
      "scanned_at": "2026-02-21 09:15:30",
      "date": "2026-02-21",
      "note": null,
      "created_at": "2026-02-21 09:15:30",
      "updated_at": "2026-02-21 09:15:30"
    },
    {
      "id": 105,
      "employee_id": 1,
      "qr_token_id": 2,
      "type": "check_out",
      "scan_latitude": "23.02250000",
      "scan_longitude": "72.57140000",
      "geofence_status": "inside",
      "scanned_at": "2026-02-21 18:00:00",
      "date": "2026-02-21",
      "note": null,
      "created_at": "2026-02-21 18:00:00",
      "updated_at": "2026-02-21 18:00:00"
    }
  ]
}
```

---

### 1.3 Get Attendance History

Fetch attendance records filtered by a date range.

| Field | Value |
|-------|-------|
| **URL** | `GET /api/attendance/history` |
| **Auth** | None required |

#### Query Parameters

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `employee_id` | `integer` | ✅ Yes | Employee's database ID |
| `from` | `string` | ❌ Optional | Start date `YYYY-MM-DD` (default: first day of current month) |
| `to` | `string` | ❌ Optional | End date `YYYY-MM-DD` (default: today) |

#### Example Request

```
GET /api/attendance/history?employee_id=1&from=2026-02-01&to=2026-02-21
```

#### Response (200 OK)

```json
{
  "status": "success",
  "data": [
    {
      "id": 101,
      "employee_id": 1,
      "qr_token_id": 2,
      "type": "check_in",
      "scan_latitude": "23.02250000",
      "scan_longitude": "72.57140000",
      "geofence_status": "inside",
      "scanned_at": "2026-02-21 09:15:30",
      "date": "2026-02-21",
      "note": null,
      "created_at": "2026-02-21 09:15:30",
      "updated_at": "2026-02-21 09:15:30"
    }
  ]
}
```

> **Note:** Records are sorted by `date DESC` (newest first).

---

## 2. Employee APIs

### 2.1 List All Active Employees

| Field | Value |
|-------|-------|
| **URL** | `GET /api/employees` |
| **Auth** | None required |

#### Response (200 OK)

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "employee_code": "EMP0001",
      "name": "John Doe",
      "email": "john@mti.com",
      "phone": "9876543210",
      "department": "Engineering",
      "designation": "Developer",
      "photo": null,
      "join_date": "2024-01-15",
      "is_active": 1,
      "created_at": "2026-02-20 10:00:00",
      "updated_at": "2026-02-20 10:00:00"
    }
  ]
}
```

---

### 2.2 Get Single Employee

| Field | Value |
|-------|-------|
| **URL** | `GET /api/employees/{id}` |
| **Auth** | None required |

#### URL Parameters

| Param | Type | Description |
|-------|------|-------------|
| `id` | `integer` | Employee's database ID |

#### Response (200 OK)

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "employee_code": "EMP0001",
    "name": "John Doe",
    "email": "john@mti.com",
    "phone": "9876543210",
    "department": "Engineering",
    "designation": "Developer",
    "photo": null,
    "join_date": "2024-01-15",
    "is_active": 1,
    "created_at": "2026-02-20 10:00:00",
    "updated_at": "2026-02-20 10:00:00"
  }
}
```

#### Error Responses

| HTTP Code | Reason |
|-----------|--------|
| `404` | Employee not found |

---

### 2.3 Create Employee

| Field | Value |
|-------|-------|
| **URL** | `POST /api/employees` |
| **Auth** | None required (admin use only — consider protecting this) |

#### Request Body (JSON)

```json
{
  "name": "Jane Smith",
  "email": "jane@mti.com",
  "phone": "9876500000",
  "department": "HR",
  "designation": "Manager",
  "join_date": "2026-02-21"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | `string` | ✅ Yes | Full name |
| `email` | `string` | ❌ Optional | Email address |
| `phone` | `string` | ❌ Optional | Phone number |
| `department` | `string` | ❌ Optional | Department name |
| `designation` | `string` | ❌ Optional | Job title |
| `join_date` | `string` | ❌ Optional | Date `YYYY-MM-DD` |

> `employee_code` is **auto-generated** (e.g. `EMP0002`).

#### Response (201 Created)

```json
{
  "status": "success",
  "id": 2
}
```

---

### 2.4 Update Employee

| Field | Value |
|-------|-------|
| **URL** | `PUT /api/employees/{id}` |
| **Auth** | None required |

#### Request Body (JSON) — send only fields to update

```json
{
  "department": "Finance",
  "designation": "Senior Manager"
}
```

#### Response (200 OK)

```json
{
  "status": "success",
  "message": "Updated."
}
```

---

### 2.5 Deactivate Employee (Soft Delete)

| Field | Value |
|-------|-------|
| **URL** | `DELETE /api/employees/{id}` |
| **Auth** | None required |

> ⚠️ This **deactivates** (soft deletes) the employee — sets `is_active = 0`. Data is preserved.

#### Response (200 OK)

```json
{
  "status": "success",
  "message": "Deactivated."
}
```

---

## 3. QR Code APIs

### 3.1 List All Active QR Codes

| Field | Value |
|-------|-------|
| **URL** | `GET /api/qr-codes` |
| **Auth** | None required |

#### Response (200 OK)

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "token": "a3f8b2c1d4e5f6a7b8c9d0e1f2a3b4c5",
      "location_name": "Main Office",
      "latitude": "23.02250000",
      "longitude": "72.57140000",
      "geofence_radius": 50,
      "is_active": 1,
      "created_at": "2026-02-20 10:00:00",
      "updated_at": "2026-02-20 10:00:00"
    }
  ]
}
```

---

### 3.2 Create QR Code

| Field | Value |
|-------|-------|
| **URL** | `POST /api/qr-codes` |
| **Auth** | None required |

#### Request Body (JSON)

```json
{
  "location_name": "Warehouse",
  "latitude": 23.045,
  "longitude": 72.530,
  "geofence_radius": 100
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `location_name` | `string` | ✅ Yes | Human-readable location name |
| `latitude` | `float` | ❌ Optional | GPS latitude of the location |
| `longitude` | `float` | ❌ Optional | GPS longitude of the location |
| `geofence_radius` | `integer` | ❌ Optional | Geofence radius in meters (default: `50`) |

> `token` is **auto-generated** (32-char hex string).

#### Response (201 Created)

```json
{
  "status": "success",
  "id": 3,
  "token": "d1e2f3a4b5c6d7e8f9a0b1c2d3e4f5a6"
}
```

---

### 3.3 Update QR Code

| Field | Value |
|-------|-------|
| **URL** | `PUT /api/qr-codes/{id}` |
| **Auth** | None required |

#### Request Body (JSON)

```json
{
  "geofence_radius": 200
}
```

#### Response (200 OK)

```json
{
  "status": "success",
  "message": "Updated."
}
```

---

### 3.4 Deactivate QR Code

| Field | Value |
|-------|-------|
| **URL** | `DELETE /api/qr-codes/{id}` |
| **Auth** | None required |

#### Response (200 OK)

```json
{
  "status": "success",
  "message": "Deactivated."
}
```

---

## 4. Report APIs

### 4.1 Daily Report

Returns attendance summary for all active employees for a given date.

| Field | Value |
|-------|-------|
| **URL** | `GET /api/reports/daily` |
| **Auth** | None required |

#### Query Parameters

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `date` | `string` | ❌ Optional | Date `YYYY-MM-DD` (default: today) |

#### Example Request

```
GET /api/reports/daily?date=2026-02-21
```

#### Response (200 OK)

```json
{
  "status": "success",
  "date": "2026-02-21",
  "data": [
    {
      "id": 1,
      "employee_code": "EMP0001",
      "name": "John Doe",
      "department": "Engineering",
      "check_in": "2026-02-21 09:15:30",
      "check_out": "2026-02-21 18:00:00",
      "geofence_status": "inside"
    },
    {
      "id": 2,
      "employee_code": "EMP0002",
      "name": "Jane Smith",
      "department": "HR",
      "check_in": null,
      "check_out": null,
      "geofence_status": null
    }
  ]
}
```

> `check_in`/`check_out` are `null` for absent employees.

---

### 4.2 Monthly Report

Returns summary of attendance for all employees in a given month.

| Field | Value |
|-------|-------|
| **URL** | `GET /api/reports/monthly` |
| **Auth** | None required |

#### Query Parameters

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `month` | `string` | ❌ Optional | Month in `YYYY-MM` format (default: current month) |

#### Example Request

```
GET /api/reports/monthly?month=2026-02
```

#### Response (200 OK)

```json
{
  "status": "success",
  "month": "2026-02",
  "data": [
    {
      "id": 1,
      "employee_code": "EMP0001",
      "name": "John Doe",
      "department": "Engineering",
      "days_present": 18,
      "late_days": 2
    }
  ]
}
```

| Field | Type | Description |
|-------|------|-------------|
| `days_present` | `integer` | Number of days employee checked in |
| `late_days` | `integer` | Days where check-in was after work_start_time |

---

### 4.3 Export Report (CSV Download URL)

Returns a URL from which the CSV report can be downloaded via browser/WebView.

| Field | Value |
|-------|-------|
| **URL** | `GET /api/reports/export` |
| **Auth** | None required |

#### Query Parameters

| Param | Type | Required | Description |
|-------|------|----------|-------------|
| `month` | `string` | ❌ Optional | `YYYY-MM` (default: current month) |
| `type` | `string` | ❌ Optional | `attendance` (default) |
| `format` | `string` | ❌ Optional | `csv` (default) |

#### Response (200 OK)

```json
{
  "status": "success",
  "export_url": "http://localhost:8082/reports/export-csv?month=2026-02",
  "month": "2026-02",
  "type": "attendance",
  "format": "csv",
  "note": "Open export_url in a browser or authenticated WebView to download the CSV file."
}
```

> **Flutter usage:** Open `export_url` using `url_launcher` or an in-app WebView. The admin web panel handles the actual CSV generation.

---

## 5. Map / Live View API

### 5.1 Live Checked-In Employees

Returns all QR code locations and currently checked-in employees (not yet checked out).

| Field | Value |
|-------|-------|
| **URL** | `GET /api/map/live` |
| **Auth** | None required |

#### Response (200 OK)

```json
{
  "status": "success",
  "locations": [
    {
      "id": 1,
      "token": "a3f8b2c1d4e5f6a7b8c9d0e1f2a3b4c5",
      "location_name": "Main Office",
      "latitude": "23.02250000",
      "longitude": "72.57140000",
      "geofence_radius": 50,
      "is_active": 1,
      "created_at": "2026-02-20 10:00:00",
      "updated_at": "2026-02-20 10:00:00"
    }
  ],
  "live": [
    {
      "name": "John Doe",
      "employee_code": "EMP0001",
      "location_name": "Main Office",
      "latitude": "23.02250000",
      "longitude": "72.57140000",
      "scanned_at": "2026-02-21 09:15:30",
      "geofence_status": "inside"
    }
  ]
}
```

| Field | Description |
|-------|-------------|
| `locations` | All active QR code locations (for map pins) |
| `live` | All employees currently checked in but not checked out today |

---

## 6. Error Response Format

All errors follow the CodeIgniter 4 ResourceController standard format:

```json
{
  "status": 404,
  "error": 404,
  "messages": {
    "error": "Employee not found."
  }
}
```

### Common HTTP Status Codes

| Code | Meaning |
|------|---------|
| `200` | Success |
| `201` | Created successfully |
| `400` | Bad request |
| `404` | Resource not found |
| `422` | Validation error (missing required fields) |
| `500` | Server error |

---

## 7. Data Models Reference

### Employee Object

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `id` | `integer` | No | Primary key |
| `employee_code` | `string` | No | Auto-generated (EMP0001, EMP0002 …) |
| `name` | `string` | No | Full name |
| `email` | `string` | Yes | Email address |
| `phone` | `string` | Yes | Phone number (max 15 chars) |
| `department` | `string` | Yes | Department |
| `designation` | `string` | Yes | Job title |
| `photo` | `string` | Yes | Photo file path/URL |
| `join_date` | `date` | Yes | `YYYY-MM-DD` |
| `is_active` | `integer` | No | `1` = active, `0` = deactivated |
| `allow_anywhere_attendance` | `integer` | No | `1` = bypass geofence warnings |
| `username` | `string` | Yes | Login username for mobile app (unique) |
| `password` | `string` | Yes | bcrypt-hashed password *(never returned in API responses)* |
| `created_at` | `datetime` | Yes | Auto-set |
| `updated_at` | `datetime` | Yes | Auto-updated |

---

### Attendance Record Object

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `id` | `integer` | No | Primary key |
| `employee_id` | `integer` | No | Foreign key → employees.id |
| `qr_token_id` | `integer` | Yes | Foreign key → qr_tokens.id |
| `type` | `enum` | No | `check_in` or `check_out` |
| `scan_latitude` | `decimal(10,8)` | Yes | GPS latitude at scan |
| `scan_longitude` | `decimal(11,8)` | Yes | GPS longitude at scan |
| `geofence_status` | `enum` | No | `inside` or `flagged` |
| `scanned_at` | `datetime` | No | Server timestamp of scan |
| `date` | `date` | No | `YYYY-MM-DD` (indexed) |
| `note` | `text` | Yes | Optional note |
| `created_at` | `datetime` | Yes | Auto-set |
| `updated_at` | `datetime` | Yes | Auto-updated |

---

### QR Token Object

| Field | Type | Nullable | Description |
|-------|------|----------|-------------|
| `id` | `integer` | No | Primary key |
| `token` | `string` | No | 32-char hex token (auto-generated) |
| `location_name` | `string` | No | Human-readable location name |
| `latitude` | `decimal` | Yes | Location GPS latitude |
| `longitude` | `decimal` | Yes | Location GPS longitude |
| `geofence_radius` | `integer` | Yes | Radius in meters (null = use system default 50m) |
| `is_active` | `integer` | No | `1` = active |
| `created_at` | `datetime` | Yes | Auto-set |
| `updated_at` | `datetime` | Yes | Auto-updated |

---

## 8. Flutter Integration Guide

### 8.1 Recommended Package

```yaml
# pubspec.yaml
dependencies:
  http: ^1.2.0
  geolocator: ^12.0.0
  mobile_scanner: ^5.2.3  # For QR code scanning
  url_launcher: ^6.3.0    # For CSV export download
```

---

### 8.2 Base API Setup

```dart
// lib/services/api_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  static const String baseUrl = 'http://192.168.1.100:8082/api';
  // Use your server IP (not localhost) when testing on a physical device

  static Map<String, String> get headers => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };
}
```

---

### 8.3 Scan QR Code (Core Flow)

```dart
// lib/services/attendance_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:geolocator/geolocator.dart';

class AttendanceService {
  static const _base = 'http://192.168.1.100:8082/api';

  static Future<Map<String, dynamic>> scanAttendance({
    required int employeeId,
    required String qrToken,
  }) async {
    // Get GPS location
    Position? position;
    try {
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (serviceEnabled && permission != LocationPermission.deniedForever) {
        position = await Geolocator.getCurrentPosition(
          desiredAccuracy: LocationAccuracy.high,
        );
      }
    } catch (_) {}

    final response = await http.post(
      Uri.parse('$_base/attendance/scan'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'employee_id': employeeId,
        'qr_token': qrToken,
        if (position != null) 'latitude': position.latitude,
        if (position != null) 'longitude': position.longitude,
      }),
    );

    return jsonDecode(response.body) as Map<String, dynamic>;
  }

  static Future<List<dynamic>> getTodayAttendance(int employeeId) async {
    final response = await http.get(
      Uri.parse('$_base/attendance/today?employee_id=$employeeId'),
    );
    final data = jsonDecode(response.body);
    return data['data'] as List<dynamic>;
  }

  static Future<List<dynamic>> getHistory({
    required int employeeId,
    String? from,
    String? to,
  }) async {
    String url = '$_base/attendance/history?employee_id=$employeeId';
    if (from != null) url += '&from=$from';
    if (to != null) url += '&to=$to';

    final response = await http.get(Uri.parse(url));
    final data = jsonDecode(response.body);
    return data['data'] as List<dynamic>;
  }
}
```

---

### 8.4 Employee Service

```dart
// lib/services/employee_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class EmployeeService {
  static const _base = 'http://192.168.1.100:8082/api';

  static Future<List<dynamic>> getAllEmployees() async {
    final res = await http.get(Uri.parse('$_base/employees'));
    return (jsonDecode(res.body)['data']) as List<dynamic>;
  }

  static Future<Map<String, dynamic>> getEmployee(int id) async {
    final res = await http.get(Uri.parse('$_base/employees/$id'));
    return (jsonDecode(res.body)['data']) as Map<String, dynamic>;
  }
}
```

---

### 8.5 QR Code Scan Flow (with mobile_scanner)

```dart
import 'package:mobile_scanner/mobile_scanner.dart';

// In your scan screen widget:
MobileScanner(
  onDetect: (capture) {
    final barcode = capture.barcodes.first;
    final String? qrToken = barcode.rawValue;

    if (qrToken != null) {
      AttendanceService.scanAttendance(
        employeeId: currentEmployee.id,
        qrToken: qrToken,
      ).then((result) {
        if (result['status'] == 'success') {
          showDialog(/* Show success: checked in/out */);
        } else if (result['status'] == 'flagged') {
          showDialog(/* Show warning: outside geofence */);
        }
      });
    }
  },
)
```

---

### 8.6 API Endpoints Quick Reference

| Method | URL | Description |
|--------|-----|-------------|
| `POST` | `/api/auth/login` | **Employee login** (username + password) |
| `POST` | `/api/auth/set-password` | Set/reset employee password (admin) |
| `POST` | `/api/attendance/scan` | Scan QR → check-in or check-out |
| `GET` | `/api/attendance/today?employee_id=` | Today's records for employee |
| `GET` | `/api/attendance/history?employee_id=&from=&to=` | History with date range |
| `GET` | `/api/employees` | All active employees |
| `GET` | `/api/employees/{id}` | Single employee |
| `POST` | `/api/employees` | Create employee |
| `PUT` | `/api/employees/{id}` | Update employee |
| `DELETE` | `/api/employees/{id}` | Deactivate employee |
| `GET` | `/api/qr-codes` | All active QR codes |
| `POST` | `/api/qr-codes` | Create QR code |
| `PUT` | `/api/qr-codes/{id}` | Update QR code |
| `DELETE` | `/api/qr-codes/{id}` | Deactivate QR code |
| `GET` | `/api/reports/daily?date=` | Daily report |
| `GET` | `/api/reports/monthly?month=` | Monthly report |
| `GET` | `/api/reports/export?month=` | Get CSV export URL |
| `GET` | `/api/map/live` | Live map data |

---

### 8.7 ⚠️ Important Notes for Flutter Developer

1. **Base URL on device:** Use your machine's **local IP** (e.g. `192.168.1.100`) — NOT `localhost` — when testing on a physical Android/iOS device.
2. **Login first:** Always call `POST /api/auth/login` on app start. Store the returned `id`, `name`, `employee_code` etc. in `SharedPreferences`.
3. **QR token value:** The QR code should encode **only the token string** (32 hex chars). Do NOT encode full URL — just the token.
4. **employee_id for scanning:** Use the `id` returned from the login response — NOT the `employee_code` string.
5. **Geofence:** Always send GPS coordinates if possible — the server handles geofence validation automatically.
6. **Check-in logic is automatic:** The server automatically determines `check_in` vs `check_out` by checking the last scan today. No need to send `type` in the request.
7. **Password never returned:** The `password` field is never included in any API response — only `username` is included in the login response `data`.
8. **No JWT / token auth currently:** All API endpoints are public. For production, consider adding an `X-Api-Key` header and a filter on the API route group.

---

*Updated: 2026-02-21 | MTI Attendance System v1.1 — Added Auth (Username + Password login)*
