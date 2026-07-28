# How to Build for iOS from Windows (Without a Mac)

Since Apple requires Xcode (which only runs on macOS) to compile iOS apps, you cannot build an iOS **`.ipa`** file (the iOS equivalent of an Android APK) directly on Windows.

However, here are the **4 practical ways** you can deliver the iOS version to your client:

---

## Easiest Way: Use the PWA (No Xcode or Mac needed)
Since we already built the driver app as a **Progressive Web App (PWA)**, your client can install it on their iPhone instantly without compiling any code:

1. Have your client open **Safari** on their iPhone.
2. Go to: **`https://jcgreasecylingrouteapp.com/driver/`**
3. Tap the **Share** button (the square with an arrow pointing up at the bottom).
4. Scroll down and tap **Add to Home Screen**.
5. The JNC GreaseCycling logo will appear on their home screen. When they tap it, it will launch in full screen, hide the safari address bar, support offline caching, and feel exactly like a native app.

---

## Option 2: Let the Client Compile it on their Mac
Since your client has a Mac, you can send them the source code zip and let Xcode compile it for them:

1. Zip the **`driver-app`** folder and send it to your client.
2. Have your client extract the zip, open Terminal on their Mac, and run:
   ```bash
   # Navigate to the folder
   cd driver-app
   
   # Install dependencies
   npm install
   
   # Add the iOS platform
   npx cap add ios
   
   # Copy configs and web assets
   npx cap sync
   
   # Open the project in Xcode
   npx cap open ios
   ```
3. Xcode will open automatically on their Mac. They can select their connected iPhone or a Simulator and click the **Run (Play)** button in Xcode to launch the app.

---

## Option 3: Cloud Build Services (Ionic Appflow or Codemagic)
You can compile your iOS app on remote Mac servers using cloud build services directly from your Windows web browser:

* **Ionic Appflow** (Official Capacitor Cloud Builder):
  1. Go to [Ionic Appflow](https://ionicframework.com/appflow).
  2. Link your Git repository.
  3. Under the "Builds" menu, select **iOS** and trigger a build. It will compile your project on a cloud Mac and give you a downloadable **`.ipa`** file.
* **Codemagic**:
  1. Go to [Codemagic.io](https://codemagic.io).
  2. Import your Capacitor project.
  3. Run an iOS build pipeline to compile your signed `.ipa` file online.

---

## Option 4: Rent a Cloud Mac (MacinCloud)
If you want to compile it yourself but don't want to buy a Mac:
1. Go to [MacinCloud](https://www.macincloud.com/).
2. Rent a cloud-hosted macOS virtual desktop (starts at around $1/hour).
3. Connect to the virtual Mac from your Windows PC using remote desktop.
4. Clone your project repository, run the iOS Capacitor setup steps, and export your `.ipa` file from Xcode.
