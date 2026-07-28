# Android White Screen Fix & Real-Time Debugging Guide

If you got a **white screen** after installing the compiled Capacitor app on your phone, it is a very common issue caused by resource routing:

## The Cause of the White Screen
In our web deployment, the app base path is configured as `/driver/` so it can run under `jcgreasecylingrouteapp.com/driver/`. 
However, Capacitor runs the application locally from `http://localhost/` inside the mobile webview. Because of the `/driver/` base path, Capacitor was looking for assets under `http://localhost/driver/assets/...` instead of the root folder, resulting in a silent 404 resource loading failure (white screen).

---

## The Two Solutions (Choose one)

### Approach A: Remote Webview Mode (Easiest & Recommended)
Instead of packaging the compiled static files inside the APK, you can configure Capacitor to directly load the web app from your live production URL: `https://jcgreasecylingrouteapp.com/driver`.

**Why this is great**: Any updates you push to your live website `https://jcgreasecylingrouteapp.com/driver` will instantly update inside all drivers' native Android apps automatically! You do not need to rebuild or reinstall a new APK ever again.

#### Steps:
1. Open the file **`driver-app/capacitor.config.json`** on your computer.
2. Edit the configuration to add the `server` block pointing to your URL:
   ```json
   {
     "appId": "com.greasecycling.driver",
     "appName": "JNC GreaseCycling",
     "webDir": "dist",
     "server": {
       "url": "https://jcgreasecylingrouteapp.com/driver/",
       "cleartext": true
     }
   }
   ```
3. Run the sync command in your `driver-app` directory:
   ```bash
   npx cap sync
   ```
4. Open the project in Android Studio (`npx cap open android`) and compile a new debug APK. It will now load your live website instantly.

---

### Approach B: Local Offline Mode (Compiling Assets into the APK)
If you want to package the compiled files directly inside the APK for offline-first boot capability:

#### Steps:
1. Make sure your local files have the latest codebase. I have updated your **`vite.config.js`** and **`package.json`** to support platform-specific building without affecting the website deploy.
2. Run the specialized Capacitor build script inside `driver-app`:
   ```bash
   # Compiles the app with base path set to '/' (fixes the white screen)
   npm run build:cap
   ```
3. Sync the compiled files to your Android folder:
   ```bash
   npx cap sync
   ```
4. Build the debug APK inside Android Studio again. It will now load the local assets successfully without any white screen.

---

## How to Debug in Real Time Using Android Studio
You can inspect the running mobile application using Google Chrome developer tools to view console logs, networks, and errors:

1. **Connect your physical Android phone** to your computer via USB.
2. Ensure **USB Debugging** is enabled in your phone's Developer Options.
3. Open the project in Android Studio and click the **Run** button to launch the app directly on your connected phone.
4. On your computer, open **Google Chrome** and navigate to:
   ```text
   chrome://inspect/#devices
   ```
5. You will see a list of connected devices. Under your device, find **JNC GreaseCycling** and click the **Inspect** link.
6. A DevTools window will open. You can now:
   * View the console to read JS errors or API responses.
   * Debug elements and styles.
   * Verify networks to see requests hitting the API backend at `https://jcgreasecylingrouteapp.com/api`.
