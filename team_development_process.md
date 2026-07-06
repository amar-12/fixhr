# FixHR Team Development Process (Simplified Workflow)

यह दस्तावेज़ हमारी 20+ डेवलपर्स की टीम के लिए वर्तमान Git और CI/CD डेवलपमेंट प्रोसेस को परिभाषित करता है। अभी केवल **`main`** ब्रांच प्रोटेक्टेड (restricted) है, जबकि `dev` और `uat` ओपन हैं।

---

## 🏗️ Branches और उनके नियम (Rules)

| Branch | Status | Restriction (प्रतिबंध) | Deployment Server |
| :--- | :--- | :--- | :--- |
| **`main`** | Production | 🔒 **Protected** (केवल PR के ज़रिए मर्ज होगा, डायरेक्ट पुश बंद है)। | Live App Server |
| **`uat`** | Staging/QA | 🔓 **Open** (डायरेक्ट पुश और मर्ज की अनुमति है)। | Testing/UAT Server |
| **`dev`** | Development | 🔓 **Open** (डायरेक्ट पुश और मर्ज की अनुमति है)। | Dev Server |
| **`user-branch`** (e.g., `amar-july`) | Local Work | 🔓 **Open** (केवल संबंधित डेवलपर काम करेगा)। | N/A |

---

## 🛠️ Step-by-Step Development Process

### Step 1: अपनी लोकल ब्रांच पर काम करना
1. अपनी पर्सनल या फ़ीचर ब्रांच बनाएं/चेकआउट करें:
   ```bash
   git checkout amar-july
   ```
2. कोड में बदलाव करें।
3. बदलावों को कमिट करें:
   ```bash
   git add .
   git commit -m "feat: updated env configurations"
   ```
4. अपनी ब्रांच को GitHub पर पुश करें:
   ```bash
   git push origin amar-july
   ```

---

### Step 2: कोड को `dev` पर भेजना (Development Test)
चूँकि `dev` ब्रांच अभी प्रोटेक्टेड नहीं है, आप सीधे कमांड लाइन से अपने कोड को `dev` में मर्ज करके पुश कर सकते हैं:

```bash
# 1. dev ब्रांच पर जाएं
git checkout dev

# 2. dev का लेटेस्ट कोड पुल करें (ताकि दूसरों के काम से टकराव न हो)
git pull origin dev

# 3. अपनी ब्रांच के कोड को dev में मर्ज करें
git merge amar-july

# 4. dev को पुश करें (इससे Dev Server पर ऑटो-डिप्लॉय हो जाएगा)
git push origin dev

# 5. वापस अपनी वर्किंग ब्रांच पर आएं
git checkout amar-july
```

---

### Step 3: कोड को `uat` पर भेजना (For Client Review)
जब काम `dev` पर ठीक चल रहा हो, तो उसे UAT पर भेजें:

```bash
# 1. uat ब्रांच पर जाएं
git checkout uat

# 2. uat का लेटेस्ट कोड पुल करें
git pull origin uat

# 3. dev के कोड को uat में मर्ज करें
git merge dev

# 4. uat को पुश करें (इससे UAT Server पर ऑटो-डिप्लॉय हो जाएगा)
git push origin uat

# 5. वापस अपनी वर्किंग ब्रांच पर आएं
git checkout amar-july
```

---

### Step 4: कोड को `main` पर भेजना (Go Live!)
चूँकि **`main`** ब्रांच प्रोटेक्टेड है, आप इस पर सीधे पुश नहीं कर सकते। इसके लिए आपको **GitHub Web UI** का उपयोग करना होगा:

1. **GitHub.com** पर अपने रिपॉजिटरी पर जाएं।
2. **New Pull Request** पर क्लिक करें।
3. सिलेक्ट करें:
   * **base:** `main`
   * **compare:** `uat`
4. PR बनाएं (**Create Pull Request**)।
5. टीम के सीनियर / टेक लीडर इस PR को रिव्यू करेंगे और अप्रूव होने के बाद **Merge** करेंगे। (मर्ज होते ही लाइव सर्वर अपडेट हो जाएगा)।

---

## 🚫 Team Guidelines (टीम के लिए सख्त नियम)

1. **No Force Push (`--force`):** कोई भी डेवलपर `dev` या `uat` पर `git push --force` का उपयोग **नहीं** करेगा। इससे दूसरों का काम हमेशा के लिए डिलीट हो सकता है।
2. **Merge Conflict Resolution:** यदि `git merge` के दौरान कोई कॉन्फ्लिक्ट आता है, तो उसे हमेशा अपने लोकल कंप्यूटर पर सुलझाएं और फिर पुश करें। कभी भी सीधे रिमोट पर कॉन्फ्लिक्ट के साथ कोड न भेजें।
3. **Sync with Main:** सप्ताह में कम से कम एक बार अपनी पर्सनल ब्रांच को `main` के लेटेस्ट कोड से अपडेट रखें:
   ```bash
   git checkout main && git pull origin main
   git checkout amar-july && git merge main
   ```
