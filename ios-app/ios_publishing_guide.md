# iOS App Store Publishing & Distribution Guide

This guide outlines the step-by-step process to compile, upload, and distribute the **JNC GreaseCycling Driver App** for iOS devices. It covers standard App Store submission, Unlisted App distribution (recommended for private/workforce apps), and TestFlight distribution.

---

## 📋 Overview of Distribution Channels

Before starting, choose the distribution option that best fits your rollout:

| Method | Lifespan | App Store Visibility | Setup Complexity | Best For |
| :--- | :--- | :--- | :--- | :--- |
| **Standard App Store** | Permanent | Publicly searchable | Medium | Public or partner driver rollouts. |
| **Unlisted App Distribution** (Recommended) | Permanent | Hidden (Direct Link Only) | Medium-High | Private fleet drivers without public clutter. |
| **Apple Business Manager** | Permanent | Private to Organization | High | Managed corporate devices. |
| **TestFlight** | 90 Days | None (Invite Only) | Low | Beta testing & immediate internal testing. |

---

## ⚙️ Step 1: Prerequisites

1. **Apple Developer Account ($99/year)**:
   * Register at [developer.apple.com](https://developer.apple.com).
2. **Mac Computer & Xcode**:
   * Xcode can only run on macOS.
3. **App Store Listing Details**:
   * **App Icon**: `1024x1024 px` PNG (no transparency).
   * **Screenshots**: At least 3-5 screenshots for:
     * **6.7" Display** (iPhone 15 Pro Max / 14 Pro Max): `1290 x 2796 px`.
     * **6.5" Display** (iPhone 11 Pro Max / XS Max): `1242 x 2688 px`.
   * **Privacy Policy URL**: A link to a live webpage disclosing how driver data (specifically location) is stored and used.
   * **Demo Account**: Valid test credentials (driver email & password) for the Apple review team.

---

## 🛠️ Step 2: Preparing the App in Xcode

On your Mac, open the project terminal and execute the following:

1. **Build and Sync Web Assets**:
   ```bash
   cd driver-app
   npm run build
   npx cap sync ios
   ```

2. **Open the Project in Xcode**:
   ```bash
   npx cap open ios
   ```

3. **Configure App Signing**:
   * In Xcode's left sidebar, click the **App** root project.
   * Navigate to the **Signing & Capabilities** tab.
   * Check **Automatically manage signing**.
   * Select your developer team in the **Team** dropdown.
   * Set your unique **Bundle Identifier** (e.g., `com.jncgreasecycling.driver`).

4. **Verify Info.plist Permissions**:
   Under the **Info** tab of the App target, ensure the following privacy keys are defined with clear, human-readable descriptions:
   * `Privacy - Location When In Use Usage Description`
     * *Value:* "Location is used to display your route map and guide you to grease collection locations."
   * `Privacy - Location Always and When In Use Usage Description`
     * *Value:* "Location is tracked in the background during active collection routes to confirm driver pickups."

---

## 📦 Step 3: Archiving and Uploading

1. **Change Destination Device**:
   * In the top bar of Xcode, click the device selector and choose **Any iOS Device (arm64)**.
2. **Create Archive**:
   * Go to the top menu and select **Product > Archive**.
   * Xcode will build the production-ready IPA package.
3. **Upload to App Store Connect**:
   * Once archiving completes, the **Organizer** window will pop up.
   * Select the latest archive and click **Distribute App**.
   * Choose **App Store Connect** -> **Upload**.
   * Accept the default compilation/signing settings and click **Next** until upload finishes.

---

## 🌐 Step 4: Configuring App Store Connect

1. Go to [App Store Connect](https://appstoreconnect.apple.com) and log in.
2. Click **Apps**, then click the **+** icon -> **New App**.
   * **Platform:** iOS
   * **Name:** `JNC GreaseCycling Driver` (Must be unique across the App Store)
   * **Primary Language:** English
   * **Bundle ID:** Select the bundle ID you created in Step 2.
   * **SKU:** E.g., `jnc-driver-01`
   * **User Access:** Full Access
3. **Fill App Information**:
   * Enter the **Privacy Policy URL**.
   * Choose **Primary Category**: Utilities or Business.
4. **Prepare the Version (1.0.0)**:
   * Drag and drop your screenshots into the 6.7" and 6.5" slots.
   * Write a clear **Description** and add **Keywords** to help drivers find the app.
   * Enter a **Support URL** (can be your company website).
5. **Attach the Build**:
   * Scroll down to the **Build** section.
   * Click the **+** button (if the upload was recent, it might take 5-10 minutes to process). Select the build you uploaded from Xcode.
6. **Provide Review Details (CRITICAL)**:
   * Under **App Review Information**:
     * Check **Sign-in required**.
     * Provide the **Username** and **Password** for your demo driver account.
     * In the **Notes** section, write:
       > "This is a closed logistics utility app for registered drivers of JNC GreaseCycling. The drivers log oil collection weights at pre-assigned client locations. Please use the test credentials provided to access the driver dashboard."

---

## 🚀 Step 5: Submission & Distribution Options

Depending on your distribution strategy, proceed with one of the following:

### Option A: Standard Public App Store
1. Click **Save** in the top right.
2. Click **Add for Review** / **Submit for Review**.
3. Apple's automated and manual review process takes **24 to 48 hours**. Once approved, the app is public on the App Store.

### Option B: Request Unlisted App Distribution (Recommended for Fleet/Drivers)
If you do not want the app to be searchable by the general public but want permanent installs that do not expire:
1. Submit your app to App Store Connect for standard review as shown in Option A.
2. Once the app is in the review queue or approved, submit a request via Apple's official request page:
   👉 [Request Unlisted App Distribution](https://developer.apple.com/contact/request/unlisted-app/)
3. Explain that the app is for internal driver operations.
4. Once approved, the app remains on Apple's servers permanently (no 90-day expiration), but is only downloadable via a secret link (e.g., `https://apps.apple.com/app/idXXXXXXXXX`).

### Option C: Distribution via TestFlight (90-Day Lifespan)
Use this option to instantly deploy the app to your drivers' devices for testing/short-term use:
1. In App Store Connect, go to the **TestFlight** tab.
2. Click **External Groups** on the left menu, click **+**, and name it `Drivers`.
3. Select the build you uploaded.
4. Under **Tester Emails**, add the email addresses of your drivers.
5. They will receive an email invitation to download the **TestFlight app** from the App Store and install your driver app instantly.
6. **Note:** Remember that these builds **expire in 90 days**. You must upload a new build version number from Xcode before the 90 days run out to keep it working.
