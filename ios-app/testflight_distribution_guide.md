# iOS TestFlight Distribution Guide (Standard Method)

**TestFlight** is Apple's official system for testing pre-release apps. It allows you and your client to install the native driver app on physical iPhones without compiling files locally or registering devices manually.

Here is the complete step-by-step guide to set it up.

---

## Prerequisites
1. **Apple Developer Account ($99/year)**: The client must have an active developer account.
2. **Admin/Developer Access**: The client must add your email to their Apple Developer Team (with Developer or Admin role) via [App Store Connect Users](https://appstoreconnect.apple.com/users).

---

## Step 1: Register the App on App Store Connect
Before uploading any builds, the application must be registered in the Apple catalog:

1. Log in to [App Store Connect](https://appstoreconnect.apple.com).
2. Go to **My Apps** and click the **"+"** button (Add App).
3. Select **New App** and fill out the details:
   * **Platforms**: iOS
   * **Name**: JNC GreaseCycling
   * **Primary Language**: English
   * **Bundle ID**: Select the bundle ID matching your app (e.g., `com.greasecycling.driver`). If not visible, register it under Identifiers in the [Apple Developer Portal](https://developer.apple.com/account/resources/identifiers/list).
   * **SKU**: A unique text string (e.g., `jnc-grease-driver-1`).
   * **User Access**: Full Access.
4. Click **Create**.

---

## Step 2: Configure Code Signing Certificates
To compile an `.ipa` that Apple's App Store Connect servers will accept, you need **App Store Distribution** certificates:

### A. The Client-Side Generation (Or Admin-Side):
On a Mac, or through the [Apple Certificates Portal](https://developer.apple.com/account/resources/certificates/list):
1. Create a **Distribution Certificate** (`iOS Distribution`).
2. Export the certificate as a **`.p12`** file (includes the private key).
3. Create an **App Store Provisioning Profile** linked to your App's Bundle ID. Download the `.mobileprovision` file.

### B. Upload Certificates to Appflow / Codemagic:
* **In Ionic Appflow**: Go to **Signing Certificates** > **iOS** > Add credentials and upload your `.p12` and `.mobileprovision` files.
* **In Codemagic**: Go to **Environment Variables** / **Code Signing** > Upload the `.p12` and `.mobileprovision` files.

---

## Step 3: Compile and Upload the Build
You don't need Xcode or a Mac to upload the build if you configure your cloud pipeline:

### Option A: Ionic Appflow Destination
1. On Appflow, start a new build on the latest commit.
2. Target Platform: **iOS**.
3. Build Type: **App Store** (not Simulator!).
4. Select your **Signing Certificate** credential.
5. Under **Destination** (Deployment), select **App Store Connect**.
6. Trigger the build. Appflow will compile, sign, and **automatically upload** the finished `.ipa` binary directly to Apple’s App Store Connect servers!

### Option B: Codemagic Destination
1. In Codemagic, go to your app settings.
2. Enable **App Store Connect publishing** under the "Publish" section (requires generating an App Store Connect API Key from Users & Access in App Store Connect).
3. Start the build. Once completed, Codemagic will push the build automatically.

---

## Step 4: Add Testers in TestFlight
Once the build is uploaded (it may take 10–20 minutes for Apple to process it and show it in the dashboard):

1. Log in to [App Store Connect](https://appstoreconnect.apple.com).
2. Go to **My Apps** and select your app **JNC GreaseCycling**.
3. Click the **TestFlight** tab at the top.
4. In the left menu, select **Internal Testing** (for team members) or **External Groups** (for client/drivers outside your developer team):
   * Click the **"+"** next to groups, name it (e.g., *Drivers*), and save.
5. Under **Testers**, click **"+"** (Add Testers).
6. Enter the email address and name of your client or drivers.
7. Select the build you uploaded and click **Submit for Review** (if sending to external testers, Apple does a brief 1-time automated check; internal builds go out instantly).

---

## Step 5: Install and Test on iPhone
Once added, the testers will receive an automated invitation email from Apple:

1. On the iPhone, open the App Store and install the free **TestFlight** app.
2. Open the invitation email on the iPhone and tap **"View in TestFlight"** or copy the **Redeem Code**.
3. Open the **TestFlight** app, tap **Redeem**, and enter the code (if not auto-loaded).
4. Click **Install**. The JNC GreaseCycling app will install directly onto the iPhone's home screen with a yellow dot indicating it is a test version.
5. Open the app to begin testing!
