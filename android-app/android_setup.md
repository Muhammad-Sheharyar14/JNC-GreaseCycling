# JNC GreaseCycling - Android App Building Guide

This guide explains how to package the Vue 3 driver PWA web application into a native Android app (`.apk`) using **Capacitor** by Ionic. 

Using Capacitor allows us to wrap our web assets (`HTML/JS/CSS`) inside a native Android container while maintaining 100% of our existing features, including offline sync and real-time geolocation tracking.

---

## Prerequisites
Before starting, ensure you have the following installed on your developer computer:
1. **Node.js & npm** (already set up in your local folder).
2. **Android Studio** (Download from [developer.android.com](https://developer.android.com/studio)).
3. **Android SDK Platform** (Installed via Android Studio SDK Manager).
4. **A Physical Android device** (or Emulator) with **USB Debugging** enabled in Developer Options.

---

## Step 1: Install Capacitor in the Driver Web App
Open a terminal in your project's **`driver-app`** directory on your computer and run:

```bash
# Install Capacitor core and CLI
npm install @capacitor/core @capacitor/cli

# Initialize Capacitor configuration
npx cap init "JNC GreaseCycling" "com.greasecycling.driver" --web-dir=dist
```

*Note: This will generate a `capacitor.config.json` (or `.ts`) file in your `driver-app` folder.*

---

## Step 2: Add the Android Platform
Next, install the Capacitor Android package and add it to your project:

```bash
# Install Android integration
npm install @capacitor/android

# Add the Android native folder
npx cap add android
```

*This command generates an `android` folder containing the full Gradle-based Android project.*

---

## Step 3: Configure Geolocation & Hardware Permissions
Our driver app calculates distances and arrival times using GPS. We need to request permissions from the Android OS.

1. Open Android Studio.
2. Open the newly created `android` folder in your project (`c:\xampp\htdocs\JNC GreaseCycling\driver-app\android`).
3. Locate the file **`app/src/main/AndroidManifest.xml`** and add the following lines inside the `<manifest>` tag, above the `<application>` tag:

```xml
<!-- Permissions for Geolocation -->
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION" />
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION" />
<uses-feature android:name="android.hardware.location.gps" />

<!-- Permissions for Network State (Offline Sync) -->
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
<uses-permission android:name="android.permission.INTERNET" />
```

---

## Step 4: Build and Sync Web Assets
Whenever you make updates to the Vue web app, you must compile it and sync the files to the native Android platform:

```bash
# 1. Compile the Vue App
npm run build

# 2. Copy the compiled build to the Android native folder
npx cap sync
```

---

## Step 5: Build the Android APK in Android Studio
1. Open the Android project in Android Studio:
   ```bash
   npx cap open android
   ```
2. Wait for Gradle sync to complete.
3. To build a debug APK for testing:
   * In Android Studio menu, click: **Build** > **Build Bundle(s) / APK(s)** > **Build APK(s)**.
   * Once built, click **Locate** on the popup to retrieve the compiled `app-debug.apk`.
4. Transfer this APK to your phone and install it to begin on-site testing.

---

## Step 6: Generate Production Release APK
To build a signed release APK for distribution:
1. In Android Studio, go to **Build** > **Generate Signed Bundle / APK**.
2. Select **APK** and click Next.
3. Create a keystore path/password and key alias (save these details securely!).
4. Choose **release** build variant and click Finish.
5. The signed, optimized `.apk` file will be generated and ready to run on any driver's phone.
