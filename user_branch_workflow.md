# Developer Workflow Guide: `amar-july` to `dev`, `uat`, and `main`

यह गाइड आपकी पर्सनल ब्रांच (`amar-july`) पर काम करने से लेकर कोड को क्रमशः `dev`, `uat`, और `main` में सुरक्षित तरीके से भेजने (merge करने) की पूरी प्रक्रिया बताती है।

---

## 🗺️ कोड का सफ़र (Branch Flow Pipeline)

```mermaid
graph TD
    A[amar-july (Your Branch)] -->|PR & Merge| B[dev (Development)]
    B -->|PR & Merge| C[uat (Testing/Staging)]
    C -->|PR & Merge| D[main (Production)]
```

---

## 🏃‍♂️ Step-by-Step Daily Workflow

### Phase 1: `amar-july` पर काम करना और सुरक्षित रूप से सेव करना

जब आप अपने कंप्यूटर पर कोड लिख रहे हों:

1. **सुनिश्चित करें कि आप सही ब्रांच पर हैं:**
   ```bash
   git checkout amar-july
   ```
2. **कोड में बदलाव (Coding) करने के बाद स्थिति जांचें:**
   ```bash
   git status
   ```
3. **बदलावों को स्टेज (add) करें:**
   ```bash
   git add .
   ```
4. **बदलावों को लोकल कमिट (commit) करें:**
   ```bash
   git commit -m "feat: added biometric API integration"
   ```
5. **कोड को अपनी रिमोट ब्रांच पर पुश (push) करें:**
   ```bash
   git push origin amar-july
   ```

---

### Phase 2: कोड को `dev` ब्रांच में भेजना (For Development Test)

जब आपका फीचर पूरा हो जाए और आप उसे **Dev Server** पर देखना चाहते हैं:

1. **GitHub.com** पर जाएं।
2. आपको ऊपर एक विकल्प दिखेगा: **"Compare & pull request"** (या फिर **Pull Requests** टैब में जाकर **New Pull Request** पर क्लिक करें)।
3. **Branches सिलेक्ट करें:**
   * **base:** `dev`
   * **compare:** `amar-july`
4. PR का नाम लिखें (जैसे: `feat: biometric integration`) और **Create pull request** पर क्लिक करें।
5. आपके टीम मेंबर्स कोड को देखकर **Approve** करेंगे।
6. अप्रूव होने के बाद **Merge pull request** पर क्लिक करें। 
   *(अब आपका कोड `dev` ब्रांच में आ जाएगा और Dev Server पर ऑटो-डिप्लॉय हो जाएगा।)*

---

### Phase 3: कोड को `uat` में भेजना (For Testing/QA)

जब `dev` ब्रांच पर सभी फीचर्स अच्छी तरह से test हो जाएं और उन्हें **UAT Server** (क्लाइंट रिव्यू) पर भेजना हो:

1. GitHub पर **New Pull Request** पर क्लिक करें।
2. **Branches सिलेक्ट करें:**
   * **base:** `uat`
   * **compare:** `dev`
3. **Create pull request** पर क्लिक करें।
4. टीम लीडर या सीनियर QA कोड और वर्किंग चेंजेस को वेरीफाई करके इसे अप्रूव करेंगे।
5. **Merge pull request** पर क्लिक करें।
   *(अब यह UAT Server पर ऑटो-डिप्लॉय हो जाएगा।)*

---

### Phase 4: कोड को `main` में भेजना (Go Live!)

जब क्लाइंट UAT पर सब कुछ पास कर दे और कोड को **Live/Production** पर डालना हो:

1. GitHub पर **New Pull Request** पर क्लिक करें।
2. **Branches सिलेक्ट करें:**
   * **base:** `main`
   * **compare:** `uat`
3. **Create pull request** पर क्लिक करें।
4. केवल **Admin या Tech Lead** इस PR को रिव्यू और अप्रूव करेंगे।
5. **Merge pull request** पर क्लिक करें।
   *(अब कोड Live Server पर डिप्लॉय हो जाएगा और लाइव हो जाएगा।)*

---

## 💡 Important Sync Tip (अपनी ब्रांच को हमेशा नया रखें)
जब भी दूसरे डेवलपर्स का कोड `dev` या `main` में मर्ज हो, तो काम शुरू करने से पहले अपनी `amar-july` ब्रांच को अपडेट कर लें:

```bash
# 1. main पर जाकर नया कोड लाएं
git checkout main
git pull origin main

# 2. अपनी ब्रांच पर वापस आएं
git checkout amar-july

# 3. main के अपडेट्स को अपनी ब्रांच में मर्ज करें
git merge main
```
