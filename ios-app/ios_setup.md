# iOS App Setup & Xcode Publishing Guide

Since your driver application is built using **Capacitor**, wrapping it for iPhone/iOS is fully supported and shares the same configuration as your Android app.

---

## ⚠️ Important Prerequisites for iOS
Unlike Android, Apple has strict security and compiler requirements:
1. **Mac Computer Required**: You **must** compile the app on a Mac running macOS (Xcode, the official Apple development compiler, only runs on Apple computers).
2. **Xcode Installed**: Download and install Xcode for free from the Mac App Store.
3. **Cocoapods Installed**: Dependency manager for iOS native libraries. Open Terminal on your Mac and run:
   ```bash
   sudo gem install cocoapods
   ```
4. **Apple Developer Account ($99 USD/year)**: Required if you want to test on physical iPhones via TestFlight or publish the app to the Apple App Store.

---

## Step 1: Install iOS Support in your Project
On your Mac (or within your project folder if shared with a Mac):
1. Navigate to your `driver-app` directory in Terminal.
2. Install the Capacitor iOS package:
   ```bash
   npm install @capacitor/ios
   ```
3. Add the native iOS platform directory:
   ```bash
   npx cap add ios
   ```

---

## Step 2: Sync and Configure the Remote Webview
Your `capacitor.config.json` is already configured for Remote Webview mode (which points to `https://jcgreasecylingrouteapp.com/driver/`). 

To copy this configuration and prepare the iOS files, run:
```bash
npx cap sync
```

---

## Step 3: Configure iOS Permissions (Important)
Since the driver app tracks locations, Apple requires you to provide explicit reasons in Xcode's configuration file (`Info.plist`).

1. Open your project in Xcode by running this command in your `driver-app` directory:
   ```bash
   npx cap open ios
   ```
2. In Xcode's left sidebar, click on the **App** project root, then click on the **Info** tab.
3. Add the following keys (Right-click any row and select **Add Row**):
   * **Privacy - Location When In Use Usage Description**
     * *Value:* "We collect your location to map daily grease collection routes and verify pickup arrivals."
   * **Privacy - Location Always and When In Use Usage Description**
     * *Value:* "We track your route locations in the background to ensure collection logs are recorded accurately."

---

## Step 4: Configure App Signing (Crucial)
Xcode will not run or build the app without valid signing certificates:
1. In Xcode's project editor (left sidebar root), select **App**, then click the **Signing & Capabilities** tab.
2. Check **Automatically manage signing**.
3. Under **Team**, sign in with your Apple Developer Account.
4. Under **Bundle Identifier**, ensure it matches your app's bundle ID (e.g., `com.greasecycling.driver`).

---

## Step 5: Test on a Simulator or Physical iPhone

### Testing on a Simulator
1. In the top toolbar of Xcode, select an iOS Simulator device (e.g., iPhone 15 Pro).
2. Click the Play button (**Run**) in the top-left corner. The simulator will boot and open the JNC GreaseCycling app.

### Testing on a Physical iPhone
1. Connect your iPhone to your Mac via USB.
2. On your iPhone, go to **Settings** > **Privacy & Security** > scroll down and turn on **Developer Mode** (your phone will restart).
3. In Xcode's top toolbar, select your connected iPhone.
4. Click the Play (**Run**) button. Xcode will compile and install the app directly on your phone.

---

## Step 6: Distribute/Publish the App

### Option A: TestFlight (Internal Testing - Easiest)
Apple's TestFlight allows you to invite your drivers to install beta versions of the app without going through the public App Store:
1. In Xcode, change the destination device in the top toolbar to **Any iOS Device (arm64)**.
2. In the top menu, go to **Product** > **Archive**.
3. Once archiving completes, the Organizer window opens. Click **Distribute App** > **App Store Connect** > **Upload**.
4. Log in to [App Store Connect](https://appstoreconnect.apple.com), navigate to your app, click **TestFlight**, and add your drivers' email addresses. They will receive an email invitation to download the app directly.

### Option B: App Store Publication (Public)
1. After archiving and uploading the app (as shown in Option A):
2. Log in to **App Store Connect**.
3. Fill in the App Store Info (Screenshots, Descriptions, Privacy Policy URL).
4. Under the **Build** section, select the uploaded build.
5. Click **Submit for Review**. The Apple App Store Review team normally approves utility apps within **24 to 48 hours**.
