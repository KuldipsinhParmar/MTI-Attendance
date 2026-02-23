# MTI Attendance — Flutter API Documentation

**Base URL:** `http://<your-server-ip>:8082`  
**All requests** must include the header:
```
Content-Type: application/json
Accept: application/json
```

---

## Table of Contents
1. [Authentication](#1-authentication)
2. [Employee](#2-employee)
3. [QR Codes](#3-qr-codes)
4. [Attendance — Scan](#4-attendance--scan)
5. [Attendance — Today's Log](#5-attendance--todays-log)
6. [Attendance — History](#6-attendance--history)
7. [Full Scan Cycle & Break Flow](#7-full-scan-cycle--break-flow)
8. [Error Reference](#8-error-reference)

---

## 1. Authentication

### `POST /api/auth/login`
Employee login with username & password.

**Request Body:**
```json
{
  "username": "rahul.sharma",
  "password": "secret123"
}
```

**Success Response — `200 OK`:**
```json
{
  "status": "success",
  "message": "Login successful.",
  "data": {
    "id": 1,
    "employee_code": "EMP0001",
    "username": "rahul.sharma",
    "name": "Rahul Sharma",
    "email": "rahul@mti.com",
    "phone": "9876543210",
    "department": "Engineering",
    "designation": "Software Engineer",
    "photo": "uploads/photos/emp1.jpg"
  }
}
```

> **Flutter tip:** Store `data.id` as `employee_id` in SharedPreferences — it is required for all attendance API calls.

**Error Responses:**
| Status | Reason |
|--------|--------|
| `401` | Wrong username or password |
| `403` | Account not activated — contact admin |
| `422` | Missing username or password |

---

## 2. Employee

### `GET /api/employees`
Returns all active employees.

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "employee_code": "EMP0001",
      "name": "Rahul Sharma",
      "department": "Engineering",
      "designation": "Software Engineer",
      "phone": "9876543210",
      "email": "rahul@mti.com",
      "photo": "uploads/photos/emp1.jpg",
      "is_active": 1
    }
  ]
}
```

### `GET /api/employees/{id}`
Returns a single employee by ID.

---

## 3. QR Codes

### `GET /api/qr-codes`
Returns all active QR codes (location tokens).

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "location_name": "Main Gate",
      "token": "abc123xyz...",
      "latitude": "23.02250000",
      "longitude": "72.57140000",
      "geofence_radius": 50,
      "is_active": 1
    }
  ]
}
```

> **Flutter tip:** The `token` embedded inside each printed QR code is the `qr_token` value you pass to the scan API.

---

## 4. Attendance — Scan

### `POST /api/attendance/scan`
Record an attendance scan event.

**Request Body:**
```json
{
  "employee_id": 1,
  "qr_token": "abc123xyz...",
  "latitude": 23.02265,
  "longitude": 72.57152,
  "scan_type": "check_in"
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `employee_id` | `int` | ✅ Yes | From login response `data.id` |
| `qr_token` | `string` | ✅ Yes | Decoded from QR code |
| `latitude` | `float` | ⚠️ Optional | Employee's GPS latitude |
| `longitude` | `float` | ⚠️ Optional | Employee's GPS longitude |
| `scan_type` | `string` | ⚠️ Optional | See [Scan Cycle](#7-full-scan-cycle--break-flow). If omitted, server auto-detects. |

**Valid `scan_type` values:**
| Value | Label |
|-------|-------|
| `check_in` | Shift In |
| `break_start` | Break Start |
| `break_end` | Break End |
| `check_out` | Shift Out |

---

**Success Response — `200 OK`:**
```json
{
  "status": "success",
  "type": "check_in",
  "label": "Shift In",
  "employee": "Rahul Sharma",
  "employee_code": "EMP0001",
  "location": "Main Gate",
  "time": "09:02:15",
  "geofence_status": "inside",
  "message": null
}
```

**Flagged Response (outside geofence) — `200 OK`:**
```json
{
  "status": "flagged",
  "type": "check_in",
  "label": "Shift In",
  "employee": "Rahul Sharma",
  "employee_code": "EMP0001",
  "location": "Main Gate",
  "time": "09:05:00",
  "geofence_status": "flagged",
  "message": "You are 120m away from Main Gate (allowed: 50m). Attendance marked but flagged for review."
}
```

> **Flutter tip:** Always show `message` to the user when `geofence_status === "flagged"`. The scan **is still recorded** — it is just flagged for admin review.

---

## 5. Attendance — Today's Log

### `GET /api/attendance/today?employee_id=1`
Returns all scan events for the employee today (ordered oldest → newest).

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 101,
      "employee_id": 1,
      "type": "check_in",
      "scan_label": "Shift In",
      "scanned_at": "2026-02-23 09:02:15",
      "date": "2026-02-23",
      "geofence_status": "inside"
    },
    {
      "id": 102,
      "employee_id": 1,
      "type": "break_start",
      "scan_label": "Break Start",
      "scanned_at": "2026-02-23 13:01:00",
      "date": "2026-02-23",
      "geofence_status": "inside"
    }
  ]
}
```

> **Flutter tip:** Use this response to determine `_nextScanType` on the client side (see flow below) so you can show the correct action button or choice sheet before the QR is scanned.

---

## 6. Attendance — History

### `GET /api/attendance/history?employee_id=1&from=2026-02-01&to=2026-02-23`
Returns attendance records in a date range (ordered newest → oldest).

| Query Param | Required | Default |
|-------------|----------|---------|
| `employee_id` | ✅ Yes | — |
| `from` | ⚠️ Optional | First day of current month |
| `to` | ⚠️ Optional | Today |

**Response:**
```json
{
  "status": "success",
  "data": [
    {
      "id": 105,
      "employee_id": 1,
      "type": "check_out",
      "scan_label": "Shift Out",
      "scanned_at": "2026-02-23 18:05:30",
      "date": "2026-02-23",
      "geofence_status": "inside"
    }
  ]
}
```

---

## 7. Full Scan Cycle & Break Flow

### 🔄 Automatic Scan Cycle (Server Side)

The server tracks each employee's last scan for the day and automatically determines the next expected scan type:

```
check_in  →  break_start  →  break_end  →  check_out  →  (restarts with check_in next day)
```

If the `scan_type` field is **omitted** from the request, the server auto-detects the next step based on the last recorded scan. This works for simple flows.

---

### 🧠 Recommended Flutter App Flow

For the best user experience, implement **client-side next-type detection** and show a **choice sheet** when the action is ambiguous:

```
1. Employee opens app
2. App calls GET /api/attendance/today?employee_id=X
3. App stores today's scan records locally
4. Employee scans QR code
5. App reads the LAST scan's `type` from stored records
6. App determines nextAutoType:
   - No scans today               → "check_in"
   - Last scan = "check_in"       → "break_start"  (ambiguous — show choice)
   - Last scan = "break_start"    → "break_end"
   - Last scan = "break_end"      → "check_out"    (ambiguous — show choice)
   - Last scan = "check_out"      → "check_in"     (new day or second shift)
7. IF nextAutoType is "break_start" or "check_out"
      → Show bottom sheet: "Going on Break" vs "Ending My Shift"
      → User picks → set chosen scan_type
   ELSE
      → Use nextAutoType directly
8. POST /api/attendance/scan with chosen scan_type
9. Show result dialog with label + geofence message if flagged
```

---

### 📋 Client-Side `_nextScanType` Logic (Dart Example)

```dart
String _nextScanType(String? lastType) {
  switch (lastType) {
    case null:        return 'check_in';
    case 'check_in':  return 'break_start';
    case 'break_start': return 'break_end';
    case 'break_end': return 'check_out';
    default:          return 'check_in';
  }
}
```

### 🍽️ Break Flow Step-by-Step

| Step | Employee Action | `scan_type` sent | `label` returned |
|------|----------------|------------------|-----------------|
| 1 | Arrives at office, scans QR | `check_in` | `Shift In` |
| 2 | Goes for lunch break, scans QR | `break_start` | `Break Start` |
| 3 | Returns from break, scans QR | `break_end` | `Break End` |
| 4 | Leaves office, scans QR | `check_out` | `Shift Out` |

**Net working hours** = Total time (check_in → check_out) **minus** break duration (break_start → break_end)

---

### 💡 Choice Bottom Sheet (When to Show)

Show a choice bottom sheet **after scanning** when the next auto-detected type is ambiguous:

| Situation | Options to show |
|-----------|----------------|
| After `check_in` (next would be `break_start`) | "Going on Break" OR "Ending My Shift" |
| After `break_end` (next would be `check_out`) | "Going on Break Again" OR "Ending My Shift" |

Pass the user's chosen option as `scan_type` in the API request.

---

## 8. Error Reference

All error responses follow this format:
```json
{
  "status": 400,
  "error": 400,
  "messages": {
    "error": "Description of the problem."
  }
}
```

| HTTP Code | Meaning |
|-----------|---------|
| `200` | Success (also used for flagged scans) |
| `201` | Created |
| `400` | Bad request |
| `401` | Unauthorized / wrong credentials |
| `403` | Forbidden (account not activated) |
| `404` | Not found (invalid employee_id, qr_token) |
| `422` | Validation error (missing required fields) |
| `500` | Server error |

---

## Quick Reference — All Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/auth/login` | Employee login |
| `GET` | `/api/employees` | List all active employees |
| `GET` | `/api/employees/{id}` | Get single employee |
| `GET` | `/api/qr-codes` | List all active QR codes |
| `POST` | `/api/attendance/scan` | Record a scan (check-in/break/check-out) |
| `GET` | `/api/attendance/today` | Today's scans for an employee |
| `GET` | `/api/attendance/history` | Date-range scan history |

---

*Generated: 2026-02-23 | MTI Attendance System*
