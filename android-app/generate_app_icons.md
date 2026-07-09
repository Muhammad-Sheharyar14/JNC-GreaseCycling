# How to Change App Icons & Splash Screens (Android & iOS)

To replace the default placeholder launcher icons with your custom **JNC GreaseCycling logo** on both Android and iOS devices, you can use the official Capacitor Assets tool. 

Follow these steps to automatically generate all required sizes and replace the icons.

---

## Step 1: Prepare the Source Images
Apple and Android require launcher icons to be square **PNG** files without transparency:

1. Convert your **`logo.jpg`** into a square PNG image (ideally **`1024 x 1024 pixels`**).
2. Inside your **`driver-app`** directory, create a new folder named **`assets`**.
3. Save your square PNG logo inside this new folder as:
   * **`driver-app/assets/icon.png`** (This will become the app launcher icon).
   * **`driver-app/assets/splash.png`** (This will become the splash screen. Ideally **`2732 x 2732 pixels`**).

---

## Step 2: Install the Capacitor Assets Tool
Open your Command Prompt or PowerShell, navigate to the `driver-app` directory, and install the generator tool:

```bash
cd "c:\xampp\htdocs\JNC GreaseCycling\driver-app"
npm install -D @capacitor/assets
```

---

## Step 3: Generate the Icons and Splash Screens
Run the generator command. It will scan your `assets/` folder, resize the images to all 30+ native sizes required by Apple and Android, and automatically place them in the correct resource folders:

```bash
npx capacitor-assets generate
```

---

## Step 4: Sync & Recompile

### For Android:
1. Open your project in Android Studio.
2. Build your debug or release APK again. The launcher icon on your phone will now show your logo.

### For iOS / Xcode:
1. Sync the project assets:
   ```bash
   npx cap sync ios
   ```
2. Build the app in Xcode or via Appflow/Codemagic. The app icon on the iPhone will show your logo.
