# Debug APK vs Release APK (Generating the "Real" App)

Congratulations! If the debug APK is working on your phone, you have successfully wrapped your app. 

Here is the difference between your current **Debug APK** and the **Release APK** (the "real" one), and how to generate the release version for distribution.

---

## 1. What is the difference?

| Feature | Debug APK (`app-debug.apk`) | Release APK (`app-release.apk`) |
| :--- | :--- | :--- |
| **Purpose** | Development testing only. | Production / Driver distribution. |
| **Performance** | Slower (debug bridge is active). | **Fast** (code is shrunk and optimized). |
| **Security** | Low (allows inspection and debugging). | **High** (debug bridge is disabled). |
| **Play Store** | Rejected by Google Play Store. | **Accepted** by Google Play Store. |
| **Installation** | Android shows "untrusted developer" warning. | Standard clean install. |

---

## 2. How to Generate the Release APK (The "Real" App)

To distribute the app to your drivers (either by uploading it to the **Google Play Store** or sending them the `.apk` file directly via WhatsApp/Email), you must generate a **Signed Release APK** in Android Studio:

### Step 1: Open the Build Menu
1. In Android Studio, go to the top menu and select:
   * **Build** > **Generate Signed Bundle / APK...**
2. Choose **APK** and click **Next**.

### Step 2: Create a Keystore (Digital Signature)
Android requires all release apps to be digitally signed with a keystore. If you don't have one:
1. Under **Key store path**, click **Create new...**
2. Fill in the form:
   * **Key store path**: Save it somewhere safe on your computer (e.g., `C:\Users\YourName\greasecycling.jks`).
   * **Password**: Create a strong password (write it down, you will need it for updates!).
   * **Alias**: Enter a name (e.g., `greasecycling-key`).
   * **Validity**: Leave it at `25` years.
   * **Certificate**: Fill in your name/organization.
3. Click **OK**, then click **Next**.

### Step 3: Compile the Release Version
1. Select the **Release** build variant.
2. Under **Signature Versions**, check both **V1 (Jar Signature)** and **V2 (Full APK Signature)** if visible.
3. Click **Finish**.

Android Studio will compile, shrink, and sign the app. Once complete, a popup will appear in the bottom-right corner. Click **Locate** to find the file **`app-release.apk`**.

---

## 3. Distributing the App
Once you have the signed **`app-release.apk`**:
* **Direct Install**: You can upload this file to your server (e.g., `jcgreasecylingrouteapp.com/driver.apk`) and send the download link to your drivers, or send it to them via WhatsApp.
* **Google Play Store**: You can register for a Google Play Console account and upload this APK to publish it on the Play Store.
