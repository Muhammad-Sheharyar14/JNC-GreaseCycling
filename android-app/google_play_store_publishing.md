# Google Play Store Publishing Guide

To publish your **JNC GreaseCycling** driver app on the Google Play Store so your drivers can install it securely, follow this step-by-step guide.

---

## ⚠️ Important Note: Build an .AAB, not an .APK!
For all new apps, the Google Play Store **requires** you to upload an **Android App Bundle (.aab)** instead of an `.apk` file. 

To build an `.aab` in Android Studio:
1. In the top menu, go to **Build** > **Generate Signed Bundle / APK...**
2. Choose **Android App Bundle** (instead of APK) and click **Next**.
3. Select your existing keystore file and key alias (the same one you created for the release APK), enter your passwords, and click **Next**.
4. Choose **release** as the destination build folder and click **Finish**.
5. When the build finishes, click **Locate** on the popup to find your **`app-release.aab`** file. This is the file you will upload to Google.

---

## Step 1: Create a Google Play Developer Account
1. Go to the [Google Play Console](https://play.google.com/console/signup).
2. Sign in with a Google account.
3. Pay the **$25 USD one-time registration fee** to open your developer account.
4. Complete your identity verification (Google will ask for a photo of your ID/passport to verify your developer profile).

---

## Step 2: Create a New App in Play Console
1. Log in to the Play Console and click the **Create app** button in the top right.
2. Fill in the initial app details:
   * **App name**: JNC GreaseCycling
   * **Default language**: English (or your preferred language)
   * **App or game**: App
   * **Free or paid**: Free
3. Read and accept the Developer Program Policies and Export Laws, then click **Create app**.

---

## Step 3: Complete the App Set-Up Tasks (Dashboard)
Before you can release the app, Google requires you to complete a set of mandatory questionnaires on the dashboard under **"Set up your app"**:

1. **App Access**: Choose *“All or some functionality is restricted”*. Provide a demo login account for Google's app reviewers (e.g. `driver@greasecycling.com` / `password`) so they can log in and test your app.
2. **Ads**: Choose *“No, my app does not contain ads”*.
3. **Content Rating**: Complete the questionnaire. Select **Utility/Productivity** as the category. Answer "No" to questions about violence, sex, and drugs to receive a safe content rating (3+).
4. **Target Audience**: Select **18 and over** (since drivers are employees).
5. **News Apps**: Choose *“No, this is not a news app”*.
6. **COVID-19 Contact Tracing**: Choose *“My app is not a publicly available contact tracing or status app”*.
7. **Data Safety**:
   * Since this is a driver app that tracks location and pickups, state that you collect **Location (approximate and precise)** and **Personal Info (Email/Name)**.
   * State that this data is **transmitted securely** (HTTPS) and is **required for app functionality** (dispatching routes and tracking operations).
8. **Government Apps**: Choose *“No, this app is not developed by or on behalf of a government agency”*.
9. **Financial Features**: Choose *“No financial features”*.

---

## Step 4: Add Store Listing Details
Go to **Grow** > **Main store listing** in the left menu:

1. **App Details**:
   * **Short description**: *Field operations and route tracking app for JNC GreaseCycling drivers.*
   * **Full description**: *JNC GreaseCycling is the official driver companion app for managing restaurant grease collection routes. Drivers can view daily assigned routes, log restaurant pickups, record grease volume collected, manage status updates offline, and navigate stops efficiently.*
2. **Graphics Assets**:
   * **App Icon**: Upload a `512x512 px` PNG version of your logo.
   * **Feature Graphic**: Upload a `1024x500 px` JPG or PNG banner.
   * **Screenshots**: Upload at least two screenshots of your mobile app interface running (you can take these on your phone or emulator).

---

## Step 5: Upload your App Bundle (.AAB)
1. In the left menu, scroll down to **Release** > **Production**.
2. Click **Create new release** in the top right.
3. If prompted to enroll in **Play App Signing**, click **Continue** (Google will securely manage your app signing key).
4. Drag and drop your **`app-release.aab`** file into the **App bundles** upload area.
5. Provide a release name (e.g., `1.0.0`) and enter **Release notes** (e.g., *Initial release of the JNC GreaseCycling driver companion app.*).
6. Click **Save as draft** at the bottom.

---

## Step 6: Define Target Countries
1. In the **Production** section, switch to the **Countries/regions** tab.
2. Click **Add countries/regions**.
3. Select your target country (e.g., United States) and save.

---

## Step 7: Submit for Review
1. Switch back to the **Releases** tab under **Production** and click **Edit release**.
2. Click **Next** to run validation checks.
3. If there are no blocking errors (warnings are fine), click **Go to publishing overview** or **Start roll-out to Production**.
4. Your app status will change to **"In review"**.

Google's review team will test the app. Since it is a new developer account, the first review usually takes **3 to 7 days**. Once approved, it will be live on the Google Play Store!
