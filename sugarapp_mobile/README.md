# SugarApp Mobile (Flutter)

A modern, cross-platform mobile application for tracking and monitoring blood sugar levels, meals, and insulin doses. Designed to integrate directly with your Laravel **SugarApp** backend.

---

## 📱 Features

- **Direct Laravel API Integration**: Connects securely via Laravel Sanctum Bearer tokens.
- **Multi-Patient Management**: Switch between patients or add new ones.
- **Daily Sugar Logs**:
  - Nashta (Breakfast) Before & After + Morning Insulin
  - Lunch Before & After + Lunch Insulin
  - Dinner Before & After + Night Insulin
- **Instant Status Badges**: Automatically flags readings as *In Target (Green)*, *High (Red)*, or *Low (Amber)* based on patient's personal target range.
- **Doctor Check Alerts**: Highlights critical logs requiring physician review.
- **Dynamic Server URL**: Set your local WiFi IP or live hosting subdomain directly from the app.

---

## 🚀 How to Build & Install the APK on Your Phone

### Prerequisites
1. **Flutter SDK**: [Download Flutter](https://docs.flutter.dev/get-started/install)
2. **Android Studio** / Android SDK with Command-line tools.

### Step 1: Install Dependencies
Open your terminal in this directory (`sugarapp_mobile`):
```bash
flutter pub get
```

### Step 2: Set your Laravel API URL
Before building, configure your backend URL in `lib/config/api_config.dart` or change it from the settings icon on the Login screen:
- **For Live Subdomain**: `https://api.yourdomain.com/api`
- **For Local Testing via WiFi**: `http://192.168.X.X:8000/api` (Replace with your PC's IP address)

### Step 3: Build the Release APK
Run the following command:
```bash
flutter build apk --release
```

When finished, your installer file will be ready at:
```
sugarapp_mobile/build/app/outputs/flutter-apk/app-release.apk
```

### Step 4: Install on Your Android Phone
1. Transfer `app-release.apk` to your phone via USB cable, WhatsApp, Google Drive, or email.
2. Tap on the `.apk` file on your phone.
3. Allow *"Install from Unknown Sources"* if prompted.
4. Open **SugarApp**, log in, and start tracking!
