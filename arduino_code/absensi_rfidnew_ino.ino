#include <Arduino.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecure.h>
#include <SPI.h>
#include <MFRC522.h>
#include <Wire.h>
#include <LiquidCrystal_I2C.h>

LiquidCrystal_I2C lcd(0x27, 16, 2); // Menggunakan alamat 0x27 hasil tes yang berhasil

// ── Konfigurasi WiFi ─────────────────────────────────────
const char* ssid = "mamacewuwung259bawah1";
const char* password = "M@m@ce259";
// ── Konfigurasi API ───────────────────────────────────────
const char* apiBase = "http://192.168.100.161:8000";
const char* apiKey = "FD29cD6c7WRT6PiX5DakBghSDphy0mSOVDspkVwxkBp3LO7025MIPTsw64bnPtSF";

// ── Pin (Diubah ke angka GPIO asli agar tidak error) ──────
#define RST_PIN 0   // GPIO 0 (Sama dengan pin D3)
#define SS_PIN 2    // GPIO 2 (Sama dengan pin D4)
#define BUZZER 16   // GPIO 16 (Sama dengan pin D0)
#define MODE_BTN 5  // GPIO 5 (Sama dengan pin D1)

MFRC522 mfrc522(SS_PIN, RST_PIN);

// ── State ─────────────────────────────────────────────────
enum Mode { MODE_PRESENSI, MODE_REGISTER };
Mode currentMode = MODE_PRESENSI;

unsigned long lastTapTime = 0;
unsigned long lastBtnTime = 0;
const unsigned long tapCooldown = 3000;
const unsigned long btnDebounce = 300;

// ─────────────────────────────────────────────────────────
void setup() {
  Serial.begin(9600);
  delay(1000); // Tunggu serial monitor siap
  Serial.println("\n\n====================================");
  Serial.println("[SYSTEM] Memulai Sistem Absensi RFID");
  Serial.println("====================================");

  SPI.begin();
  mfrc522.PCD_Init();
  Serial.println("[SYSTEM] Modul RFID MFRC522 Diinisialisasi.");

  pinMode(BUZZER, OUTPUT);
  digitalWrite(BUZZER, LOW);
  pinMode(MODE_BTN, INPUT_PULLUP);

  // ── PERBAIKAN LCD I2C DI SINI ───────────────────────────
  Wire.begin(4, 5);   // Inisialisasi pin I2C: 4 (SDA/D2), 5 (SCL/D1)
  lcd.init();         // Menggunakan init() sesuai versi library kamu
  lcd.backlight();
  lcd.clear();
  // ───────────────────────────────────────────────────────

  // Koneksi WiFi
  Serial.print("[WIFI] Menghubungkan ke SSID: ");
  Serial.println(ssid);
  lcd.setCursor(0, 0);
  lcd.print("Connecting WiFi");
  WiFi.begin(ssid, password);

  int retry = 0;
  while (WiFi.status() != WL_CONNECTED && retry < 20) {
    delay(500);
    Serial.print(".");
    retry++;
  }
  Serial.println();

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("[WIFI] BERHASIL Terhubung!");
    Serial.print("[WIFI] IP Address ESP: ");
    Serial.println(WiFi.localIP());
    
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Connected!");
    delay(1500);
  } else {
    Serial.println("[WIFI] GAGAL Terhubung ke WiFi.");
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print("WiFi Gagal!");
    lcd.setCursor(0, 1);
    lcd.print("Cek koneksi...");
    delay(3000);
  }

  showStandby();
}

// ─────────────────────────────────────────────────────────
void loop() {
  // ── Cek tombol mode ──────────────────────────────────
  if (digitalRead(MODE_BTN) == LOW) {
    unsigned long now = millis();
    if (now - lastBtnTime > btnDebounce) {
      lastBtnTime = now;
      Serial.println("\n[BTN] Tombol mode ditekan.");
      toggleMode();
    }
  }

  // ── Cek kartu RFID ───────────────────────────────────
  if (!mfrc522.PICC_IsNewCardPresent()) return;
  if (!mfrc522.PICC_ReadCardSerial()) return;

  unsigned long now = millis();
  if (now - lastTapTime < tapCooldown) {
    Serial.println("[RFID] Tap kartu diabaikan (Cooldown).");
    return;
  }
  lastTapTime = now;

  Serial.println("\n------------------------------------");
  Serial.println("[RFID] Kartu Baru Terdeteksi!");

  // Baca UID
  String uid = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) uid += "0";
    uid += String(mfrc522.uid.uidByte[i], HEX);
  }
  uid.toUpperCase();

  Serial.println("[RFID] UID Kartu: " + uid);
  Serial.print("[SYSTEM] Mengeksekusi Mode: ");
  Serial.println(currentMode == MODE_PRESENSI ? "PRESENSI (Checkin)" : "REGISTER");

  buzz(1);

  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("UID: " + uid.substring(0, 10));
  lcd.setCursor(0, 1);
  lcd.print("Memproses...");

  // Jalankan sesuai mode
  if (currentMode == MODE_PRESENSI) {
    sendCheckin(uid);
  } else { 
    sendRegister(uid);
  }

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();

  showStandby();
  Serial.println("------------------------------------\n");
}

// ─────────────────────────────────────────────────────────
void toggleMode() {
  if (currentMode == MODE_PRESENSI) {
    currentMode = MODE_REGISTER;
    buzz(2);
    lcd.clear();
    lcd.setCursor(0, 0);
    lcd.print(">> MODE DAFTAR <<");
    lcd.setCursor(0, 1);
    lcd.print("Tap kartu baru");
    Serial.println("[SYSTEM] Mode diubah ke: REGISTER");
  } else {
    currentMode = MODE_PRESENSI;
    buzz(1);
    showStandby();
    Serial.println("[SYSTEM] Mode diubah ke: PRESENSI");
  }
}

// ─────────────────────────────────────────────────────────
void showStandby() {
  lcd.clear();
  lcd.setCursor(0, 0);
  if (currentMode == MODE_PRESENSI) {
    lcd.print("Scan your Card");
    lcd.setCursor(0, 1);
    lcd.print("[ ] Presensi");
  } else {
    lcd.print(">> MODE DAFTAR <<");
    lcd.setCursor(0, 1);
    lcd.print("Tap kartu baru");
  }
  Serial.println("[SYSTEM] Menunggu Tap Kartu...");
}

// ─────────────────────────────────────────────────────────
void sendCheckin(String uid) {
  if (!isWifiConnected()) {
    Serial.println("[HTTP] Batal kirim Checkin, WiFi tidak terhubung.");
    return;
  }

  WiFiClient wifiClient;
  HTTPClient http;

  String url = String(apiBase) + "/api/rfid/checkin";
  Serial.println("[HTTP] Memulai Request Checkin...");
  Serial.println("[HTTP] Target URL: " + url);

  http.setTimeout(10000);
  http.begin(wifiClient, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-KEY", apiKey);
  http.addHeader("ngrok-skip-browser-warning", "true");

  String body = "{\"uid\":\"" + uid + "\"}";
  Serial.println("[HTTP] Payload (Body): " + body);

  Serial.println("[HTTP] Mengirim POST Request...");
  int httpCode = http.POST(body);
  
  Serial.print("[HTTP] Response Code: ");
  Serial.println(httpCode);

  lcd.clear();

  if (httpCode > 0) {
    String response = http.getString();
    Serial.println("[HTTP] Response Body: " + response);

    if (httpCode == 200 || httpCode == 201) {
      if (response.indexOf("\"action\":\"tap_in\"") >= 0) {
        String name = extractJson(response, "employee");
        bool isLate = response.indexOf("\"status\":\"late\"") >= 0;
        lcd.setCursor(0, 0);
        lcd.print("Tap In: " + name.substring(0, 8));
        lcd.setCursor(0, 1);
        lcd.print(isLate ? "Terlambat!" : "Tepat Waktu!");
        buzz(1);
      } else if (response.indexOf("\"action\":\"tap_out\"") >= 0) {
        String dur = extractJson(response, "work_duration");
        lcd.setCursor(0, 0);
        lcd.print("Tap Out OK!");
        lcd.setCursor(0, 1);
        lcd.print(dur.substring(0, 16));
        buzz(2);
      } else if (response.indexOf("\"action\":\"blocked\"") >= 0) {
        lcd.setCursor(0, 0);
        lcd.print("DIBLOKIR!");
        lcd.setCursor(0, 1);
        lcd.print("KPI Tidak Valid");
        digitalWrite(BUZZER, HIGH);
        delay(800);
        digitalWrite(BUZZER, LOW);
      } else if (response.indexOf("\"action\":\"already_done\"") >= 0) {
        lcd.setCursor(0, 0);
        lcd.print("Sudah Absen");
        lcd.setCursor(0, 1);
        lcd.print("Hari Ini!");
        buzz(3);
      } else if (response.indexOf("\"action\":\"unassigned\"") >= 0) {
        lcd.setCursor(0, 0);
        lcd.print("Kartu Belum");
        lcd.setCursor(0, 1);
        lcd.print("Di-assign!");
        buzz(2);
      } else if (response.indexOf("\"action\":\"task_incomplete\"") >= 0) {
        lcd.setCursor(0, 0);
        lcd.print("Task Belum 70%!");
        lcd.setCursor(0, 1);
        lcd.print("Cek di website");
        buzz(3);
      }
    } else if (httpCode == 404) {
      lcd.setCursor(0, 0);
      lcd.print("Kartu Tidak");
      lcd.setCursor(0, 1);
      lcd.print("Dikenali!");
      buzz(2);
    } else {
      lcd.setCursor(0, 0);
      lcd.print("Gagal Kirim");
      lcd.setCursor(0, 1);
      lcd.print("HTTP: " + String(httpCode));
      buzz(3);
    }
  } else {
    Serial.print("[HTTP-ERROR] Request Gagal! Detail Error: ");
    Serial.println(http.errorToString(httpCode).c_str());
    
    lcd.setCursor(0, 0);
    lcd.print("Koneksi Error!");
    lcd.setCursor(0, 1);
    lcd.print("HTTP: " + String(httpCode));
    buzz(3);
  }

  http.end();
  Serial.println("[HTTP] Request Checkin Selesai.");
  delay(2500);
}

// ─────────────────────────────────────────────────────────
void sendRegister(String uid) {
  if (!isWifiConnected()) {
    Serial.println("[HTTP] Batal kirim Register, WiFi tidak terhubung.");
    return;
  }

  WiFiClient wifiClient;
  HTTPClient http;

  String url = String(apiBase) + "/api/rfid/register";
  Serial.println("[HTTP] Memulai Request Register...");
  Serial.println("[HTTP] Target URL: " + url);

  http.setTimeout(10000);
  http.begin(wifiClient, url);
  http.addHeader("Content-Type", "application/json");
  http.addHeader("X-API-KEY", apiKey);
  http.addHeader("ngrok-skip-browser-warning", "true");

  String body = "{\"uid\":\"" + uid + "\"}";
  Serial.println("[HTTP] Payload (Body): " + body);

  Serial.println("[HTTP] Mengirim POST Request...");
  int httpCode = http.POST(body);
  
  Serial.print("[HTTP] Response Code: ");
  Serial.println(httpCode);

  lcd.clear();

  if (httpCode > 0) {
    String response = http.getString();
    Serial.println("[HTTP] Response Body: " + response);

    if (httpCode == 201) {
      lcd.setCursor(0, 0);
      lcd.print("Kartu Terdaftar!");
      lcd.setCursor(0, 1);
      lcd.print("Assign di web");
      buzz(2);
    } else if (httpCode == 200) {
      lcd.setCursor(0, 0);
      lcd.print("Sudah Terdaftar");
      lcd.setCursor(0, 1);
      lcd.print("Cek web HRD");
      buzz(3);
    } else if (httpCode == 401) {
      lcd.setCursor(0, 0);
      lcd.print("Auth Error");
      lcd.setCursor(0, 1);
      lcd.print("Cek API Key");
      buzz(3);
    } else {
      lcd.setCursor(0, 0);
      lcd.print("Gagal Daftar");
      lcd.setCursor(0, 1);
      lcd.print("HTTP: " + String(httpCode));
      buzz(3);
    }
  } else {
    Serial.print("[HTTP-ERROR] Request Gagal! Detail Error: ");
    Serial.println(http.errorToString(httpCode).c_str());
    
    lcd.setCursor(0, 0);
    lcd.print("Koneksi Error!");
    lcd.setCursor(0, 1);
    lcd.print("HTTP: " + String(httpCode));
    buzz(3);
  }

  http.end();
  Serial.println("[HTTP] Request Register Selesai.");
  delay(2500);
}

// ─────────────────────────────────────────────────────────
bool isWifiConnected() {
  if (WiFi.status() == WL_CONNECTED) {
    return true;
  }
  
  Serial.println("[WIFI] Status Terputus! Mencoba Reconnect...");
  lcd.clear();
  lcd.setCursor(0, 0);
  lcd.print("WiFi Terputus!");
  lcd.setCursor(0, 1);
  lcd.print("Reconnecting..");
  
  WiFi.reconnect();
  
  int counter = 0;
  while (WiFi.status() != WL_CONNECTED && counter < 10) {
    delay(500);
    Serial.print(".");
    counter++;
  }
  Serial.println();
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("[WIFI] Reconnect Berhasil.");
    return true;
  } else {
    Serial.println("[WIFI] Reconnect Gagal.");
    return false;
  }
}

// ─────────────────────────────────────────────────────────
void buzz(int n) {
  for (int i = 0; i < n; i++) {
    digitalWrite(BUZZER, HIGH);
    delay(100);
    digitalWrite(BUZZER, LOW);
    if (i < n - 1) delay(100);
  }
}

// ─────────────────────────────────────────────────────────
String extractJson(String json, String key) {
  String search = "\"" + key + "\":\"";
  int start = json.indexOf(search);
  if (start == -1) return "";
  start += search.length();
  int end = json.indexOf("\"", start);
  if (end == -1) return "";
  return json.substring(start, end);
}