# 🚀 Automated Meter Reading System - Complete Setup Guide

## Overview

Your Water Billing System now supports **fully automated meter reading capture**, automatic bill generation, and water consumption tracking. Smart meters can automatically send readings to your system.

---

## 📋 System Components

### 1. **auto_capture_reading.php** - API Endpoint
Receives automated readings from smart meters via HTTP POST requests.
- Validates meter number and API key
- Detects abnormal consumption
- Automatically generates bills
- Logs all capture attempts

### 2. **process_automated_bills.php** - Billing Processor
Scheduled job that processes all captured readings and generates bills daily.

### 3. **automated_meter_manager.php** - Management Dashboard
Web interface to:
- Generate and manage API keys for each meter
- View capture logs
- Monitor system status

### 4. **get_consumption_analytics.php** - Analytics API
Provides consumption trends and analytics data.

---

## ⚡ Quick Setup (5 minutes)

### Step 1: Initialize the System

Run the setup script:
```
/setup_automated_readings.php
```

This will create required database tables and columns.

### Step 2: Generate API Keys

1. Go to **Dashboard** → **Automated Meter Management**
2. Click "Generate Key" for each meter
3. Copy the API key and provide it to your meter device

### Step 3: Configure Meter Devices

Configure each smart meter to send readings to:

**Endpoint URL:**
```
http://[YOUR_SERVER]/Water_Billing_Project/auto_capture_reading.php
```

**HTTP Method:** POST

**Content-Type:** application/json

**Request Body:**
```json
{
  "meter_number": "M12345",
  "current_reading": 15500,
  "timestamp": "2026-07-19 14:30:00",
  "api_key": "meter_xxxxxxxxxxxxxxxxxx_[timestamp]"
}
```

### Step 4: Set Up Automated Billing (Cron Job)

Add to your server's cron job scheduler to run daily:

**Cron Expression (daily at 2 AM):**
```
0 2 * * * php /path/to/process_automated_bills.php
```

**On Windows (Task Scheduler):**
```
Program: C:\PHP\php.exe
Arguments: -f C:\xampp_new\htdocs\Water_Billing_Project\process_automated_bills.php
Schedule: Daily at 2:00 AM
```

---

## 🔌 API Reference

### Auto-Capture Reading Endpoint

**URL:** `/auto_capture_reading.php`

**Method:** POST

**Headers:**
```
Content-Type: application/json
```

**Request Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| meter_number | string | Yes | Unique meter identifier (e.g., M12345) |
| current_reading | number | Yes | Current meter reading value |
| timestamp | string | No | Date/time of reading (ISO 8601 format) |
| api_key | string | Yes | Generated API key for the meter |

**Example Request:**
```bash
curl -X POST http://localhost/Water_Billing_Project/auto_capture_reading.php \
  -H "Content-Type: application/json" \
  -d '{
    "meter_number": "M12345",
    "current_reading": 15500,
    "timestamp": "2026-07-19 14:30:00",
    "api_key": "meter_abcd1234efgh5678ijkl9012_1721398200"
  }'
```

**Successful Response (200):**
```json
{
  "success": true,
  "message": "Reading captured successfully",
  "customer_id": 1,
  "previous_reading": 15000,
  "current_reading": 15500,
  "consumption": 500,
  "amount": 25000,
  "billing_date": "2026-07-19"
}
```

**Error Response (400):**
```json
{
  "error": "Current reading cannot be less than previous reading"
}
```

**Error Response (401):**
```json
{
  "error": "Invalid meter number or API key"
}
```

---

## 📊 Database Schema

### New Tables Created:

#### `automated_readings_log`
Logs all automated reading capture attempts
```sql
- id (PK)
- customer_id (FK)
- meter_number
- previous_reading
- current_reading
- consumption
- status (success/failed)
- error_message
- captured_at
```

#### `meter_config`
Stores meter configuration
```sql
- id (PK)
- customer_id (FK)
- meter_number
- api_key
- billing_cycle (days)
- consumption_threshold
- rate_per_unit
- status (active/inactive)
- last_sync
```

#### `consumption_alerts`
Tracks consumption anomalies
```sql
- id (PK)
- customer_id (FK)
- alert_type
- consumption_value
- alert_message
- status
- created_at
```

### Modified Tables:

#### `customers` (new columns)
```sql
- api_key (unique)
- meter_status (active/inactive)
- last_reading_date
- auto_billing_enabled (boolean)
```

#### `bills` (new columns)
```sql
- auto_generated (boolean)
- reading_id (FK to meter_readings)
```

---

## 🔒 Security Features

✓ **Unique API Keys** - Each meter has its own secure key
✓ **Input Validation** - All inputs sanitized and validated
✓ **Prepared Statements** - Protection against SQL injection
✓ **Authentication** - Meter validation required for each request
✓ **Audit Trail** - Complete log of all automated readings
✓ **Anomaly Detection** - Consumption > 1000 units rejected

---

## 📈 Key Features

### ✅ Automatic Reading Capture
- Real-time meter reading reception
- Immediate consumption calculation
- Automatic bill generation

### ✅ Smart Validation
- Previous reading comparison
- Abnormal consumption detection
- Duplicate reading prevention

### ✅ Consumption Tracking
- Historical consumption data
- Daily/weekly/monthly analytics
- Trend analysis

### ✅ Automated Billing
- Daily automatic bill processing
- Rate-based calculation (Rs. 50 per unit)
- Complete audit trail

### ✅ Management Dashboard
- API key generation and management
- Real-time activity monitoring
- System status overview

---

## 🐛 Troubleshooting

### Issue: "Invalid meter number or API key"
**Solution:** Verify that the API key matches exactly and hasn't been regenerated.

### Issue: "Abnormal consumption detected"
**Solution:** This occurs when consumption exceeds 1000 units. Manual verification required. Check for:
- Leaks in the water line
- Meter reading error
- Legitimate high usage

### Issue: Bills not generating automatically
**Solution:** 
1. Verify cron job is scheduled correctly
2. Check database for readings with today's date
3. Ensure readings are successful (check logs)

### Issue: API endpoint returns 500 error
**Solution:**
1. Check database connection
2. Verify all tables exist: `automated_readings_log`, `meter_config`
3. Check error logs for PHP errors

---

## 📱 Sample Meter Implementation

### Python Example (for IoT meter):
```python
import requests
import json
from datetime import datetime

API_URL = "http://[YOUR_SERVER]/Water_Billing_Project/auto_capture_reading.php"
METER_NUMBER = "M12345"
API_KEY = "meter_xxxxxxxxxxxxxxxxxx_[timestamp]"

def send_reading(current_value):
    payload = {
        "meter_number": METER_NUMBER,
        "current_reading": current_value,
        "timestamp": datetime.now().isoformat(),
        "api_key": API_KEY
    }
    
    response = requests.post(
        API_URL,
        json=payload,
        headers={"Content-Type": "application/json"}
    )
    
    if response.status_code == 200:
        print("✓ Reading sent successfully!")
        print(response.json())
    else:
        print("✗ Error:", response.json())

# Send reading every hour
if __name__ == "__main__":
    send_reading(15500)
```

### Arduino Example (for smart meter):
```cpp
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

const char* ssid = "YOUR_WIFI";
const char* password = "YOUR_PASSWORD";
const char* serverUrl = "http://[YOUR_SERVER]/Water_Billing_Project/auto_capture_reading.php";

void sendReading(float reading) {
  HTTPClient http;
  http.begin(serverUrl);
  http.addHeader("Content-Type", "application/json");
  
  DynamicJsonDocument doc(200);
  doc["meter_number"] = "M12345";
  doc["current_reading"] = reading;
  doc["api_key"] = "meter_xxxxxxxxxxxxxxxxxx";
  
  String payload;
  serializeJson(doc, payload);
  
  int httpResponseCode = http.POST(payload);
  
  if (httpResponseCode == 200) {
    Serial.println("Reading sent successfully!");
  }
  
  http.end();
}
```

---

## 🎯 Configuration Parameters

Edit these files to customize behavior:

### Rate per Unit (Rs.)
**File:** `auto_capture_reading.php` (line ~80)
```php
$rate = 50; // Change this to your rate
```

### Consumption Threshold
**File:** `auto_capture_reading.php` (line ~70)
```php
if ($consumption > 1000) { // Change threshold
```

### Billing Date Format
**File:** `process_automated_bills.php`
```php
$billing_date = date('Y-m-d'); // Change format if needed
```

---

## 📞 Support

For issues or questions about the automated meter system:

1. Check the Activity Log in **Automated Meter Management**
2. Review `automated_readings_log` table for errors
3. Verify meter device is sending correct JSON format
4. Ensure API key is not expired/regenerated

---

## ✨ Next Steps

1. ✅ Run `/setup_automated_readings.php`
2. ✅ Generate API keys for all meters
3. ✅ Configure meter devices with API endpoint
4. ✅ Set up cron job for automated billing
5. ✅ Monitor Activity Log for successful captures

**Your system is now ready for automated meter reading!** 🎉
