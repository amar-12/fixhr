# Git Branching, Pull, Push & Conflict Avoidance Guide

यह गाइड आपको बिना किसी Merge Conflict के Git पर काम करने का सबसे सुरक्षित और बेस्ट तरीका बताएगी। 

---

## 💡 Golden Rules (सुनहरे नियम)

1. **डायरेक्ट Commit न करें:** `main`, `dev`, या `uat` पर सीधे कोई बदलाव करके commit न करें।
2. **हमेशा अपनी फ़ीचर ब्रांच पर काम करें:** जैसे `amar-july` या `feature/login`।
3. **पुल (Pull) रोज़ाना करें:** काम शुरू करने से पहले और रिमोट पर कोड भेजने से पहले हमेशा अपडेटेड कोड लें।
4. **Clean Status:** पुल या ब्रांच चेंज करने से पहले `git status` चेक करें। कोई भी अनकमिटेड कोड (uncommitted code) खुला न छोड़ें।

---

## 🔄 Daily Workflow (रोज़ाना काम करने का सुरक्षित तरीका)

### Step 1: काम शुरू करने से पहले (Get Latest Code)
हर सुबह या नया काम शुरू करने से पहले, अपनी मुख्य ब्रांच (`main` या `dev`) का लेटेस्ट कोड अपनी वर्किंग ब्रांच (`amar-july`) in laayein:

```bash
# 1. main/dev branch par jaayein
git checkout main

# 2. GitHub se latest code download karein
git pull origin main

# 3. Wapas apni working branch par jaayein
git checkout amar-july

# 4. main ke naye badlavon ko apni branch me merge karein
git merge main
```

---

### Step 2: Code me badlav aur Commit karna
Jab aap apna kaam (Coding) poora kar lein:

```bash
# 1. Dekhein kaun-kaun si files badli hain
git status

# 2. Files ko staging area me add karein
git add .

# 3. commit karein
git commit -m "feat: added login authentication screen"
```

---

### Step 3: Code ko Push karne ka safe tarika (Conflict Avoidance)
Push karne se theek pehle:

```bash
# 1. main branch par jaakar latest pull lein
git checkout main
git pull origin main

# 2. Apni branch par wapas aayein
git checkout amar-july

# 3. main ka latest code apni branch me merge karein
git merge main
```
> [!NOTE]
> Agar yahan koi **Conflict** nahi aata hai, toh aap safe hain. Agar Conflict aata hai, toh use local par hi resolve karein.

```bash
# 4. Ab apna code GitHub (origin) par send karein
git push origin amar-july
```

---

## ⚠️ Merge Conflict ko Kaise Suljhayen (Conflict Resolution)

Agar `git merge` karne par conflict error aata hai:

1. **Check Status:** `git status` chalayein. Laal rang me **"Both modified"** files dikhengi.
2. **Open File:** Un files ko VS Code ya editor me open karein.
3. **Conflict Markers ko Samjhein:**
   ```text
   <<<<<<< HEAD (Current Change - Aapka code)
   $app_url = "http://localhost";
   =======
   $app_url = "https://web.fixhr.app";
   >>>>>>> main (Incoming Change - Server/Dusron ka code)
   ```
4. **Resolve:** VS Code me *Accept Current Change*, *Accept Incoming Change*, ya *Accept Both* par click karein ya manual clean up karke markers (`<<<<<<<`, `=======`, `>>>>>>>`) ko delete kar dein.
5. **Save changes** aur terminal me chalayein:
   ```bash
   git add <conflict_resolved_file_path>
   git commit -m "merge: resolved conflicts with main"
   ```
6. **Push:**
   ```bash
   git push origin amar-july
   ```

---

## 🛠️ Git Cheat Sheet

* **Stash Changes:**
  ```bash
  git stash          # Temporary save uncommitted work
  git checkout dev   # Switch to other branch
  # Back to work:
  git checkout amar-july
  git stash pop      # Restore work
  ```

* **Restore Changes:**
  ```bash
  git restore .      # Undo local changes
  ```
