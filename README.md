# Bot Panel — সম্পূর্ণ ফ্রি ডিপ্লয়মেন্ট গাইড

এই প্রজেক্ট দিয়ে আপনি BotBhai.top এর মতো একটা Telegram Bot Hosting Panel চালাতে পারবেন —
ইউজার Bot Token দিয়ে ভেরিফাই করবে, PHP কোড আপলোড/এডিট করবে, আর সেই কোড লাইভ Telegram webhook
হিসেবে কাজ করবে। **পুরোপুরি ফ্রি — কোনো টাকা/কার্ড লাগবে না।**

---

## ধাপ ১: GitHub এ কোড আপলোড করুন

1. [github.com](https://github.com) এ ফ্রি অ্যাকাউন্ট বানান (না থাকলে)।
2. একটা নতুন **Public** বা **Private** রিপোজিটরি বানান, নাম দিন যেমন `bot-panel`।
3. এই পুরো ফোল্ডারের সব ফাইল (Dockerfile সহ) সেই রিপোতে আপলোড করুন। দুইভাবে করতে পারেন:
   - **সহজ উপায়:** GitHub এর "Add file → Upload files" বাটনে ক্লিক করে সব ফাইল/ফোল্ডার টেনে দিন।
   - **Git দিয়ে (যদি Git জানা থাকে):**
     ```bash
     git init
     git add .
     git commit -m "Initial commit"
     git branch -M main
     git remote add origin https://github.com/আপনার-ইউজারনেম/bot-panel.git
     git push -u origin main
     ```

---

## ধাপ ২: Render এ ফ্রি অ্যাকাউন্ট বানান

1. [render.com](https://render.com) এ যান, **Sign Up** করুন (GitHub দিয়ে লগইন করলে সবচেয়ে সহজ হবে)।
2. কোনো কার্ড/পেমেন্ট তথ্য লাগবে না ফ্রি টিয়ারের জন্য।

---

## ধাপ ৩: নতুন Web Service বানান

1. Render Dashboard এ **New → Web Service** ক্লিক করুন।
2. আপনার `bot-panel` GitHub রিপোজিটরি সিলেক্ট করুন (Connect করতে বললে GitHub পারমিশন দিন)।
3. সেটিংস:
   - **Name:** যা খুশি (যেমন `my-bot-panel`)
   - **Region:** যেকোনো একটা কাছেরটা (Singapore ভালো হবে বাংলাদেশের জন্য)
   - **Branch:** `main`
   - **Runtime/Environment:** এটা **Docker** হিসেবেই অটো-ডিটেক্ট হয়ে যাবে (কারণ আমরা Dockerfile দিয়ে দিয়েছি)
   - **Instance Type:** **Free** সিলেক্ট করুন
4. **Create Web Service** ক্লিক করুন। প্রথমবার বিল্ড হতে ৩-৫ মিনিট লাগতে পারে।

---

## ধাপ ৪: APP_URL সেট করুন (গুরুত্বপূর্ণ!)

বিল্ড শেষ হলে Render আপনাকে একটা URL দেবে, যেমন:
`https://my-bot-panel.onrender.com`

এই URL টা কপি করে:
1. Render Dashboard → আপনার সার্ভিস → **Environment** ট্যাবে যান
2. **Add Environment Variable** ক্লিক করুন
3. Key: `APP_URL` — Value: `https://my-bot-panel.onrender.com` (আপনার আসল URL, শেষে `/` ছাড়া)
4. **Save Changes** করুন — এতে সার্ভিস অটো রিডিপ্লয় হবে

**⚠️ এই ধাপ স্কিপ করলে Telegram webhook সঠিক ঠিকানায় সেট হবে না, বট রিপ্লাই দেবে না।**

---

## ধাপ ৫: টেস্ট করুন

1. `https://my-bot-panel.onrender.com` এ ঢুকুন (প্রথমবার লোড হতে ৩০-৫০ সেকেন্ড লাগতে পারে, ফ্রি টিয়ার স্লিপ মোড থেকে জাগে)
2. **Sign Up** করে অ্যাকাউন্ট বানান
3. **Create Bot** এ ক্লিক করে @BotFather থেকে পাওয়া টোকেন দিন
4. সাথে সাথে Telegram এ গিয়ে আপনার বটকে `/start` পাঠান — রিপ্লাই আসবে
5. **File Manager** থেকে `bot.php` এডিট করে নিজের লজিক লিখুন, **Save Changes** দিলে সাথে সাথে লাইভ হয়ে যাবে

---

## ধাপ ৬: cron job দিয়ে সার্ভার সবসময় জাগিয়ে রাখুন

Render ফ্রি টিয়ারে ১৫ মিনিট কোনো রিকোয়েস্ট না এলে সার্ভার ঘুমিয়ে যায় (cold start লাগে জাগতে)।
এটা এড়াতে ফ্রি cron সার্ভিস ব্যবহার করুন:

1. [cron-job.org](https://cron-job.org) এ ফ্রি অ্যাকাউন্ট বানান
2. নতুন Cron Job বানান:
   - **URL:** `https://my-bot-panel.onrender.com/` (আপনার আসল URL)
   - **Interval:** প্রতি ১০-১৪ মিনিটে একবার (Execution schedule → User-defined → every 10 minutes)
3. Save করুন — এখন থেকে এই cron প্রতি ১০ মিনিটে আপনার সাইটে একটা রিকোয়েস্ট পাঠাবে, সার্ভার ঘুমাবে না, বট সবসময় দ্রুত রেসপন্স করবে।

(বিকল্প হিসেবে UptimeRobot.com ব্যবহার করা যায়, একই কাজ করে)

---

## গুরুত্বপূর্ণ সীমাবদ্ধতা (সততার সাথে জেনে রাখুন)

- **ডাটা পার্সিস্টেন্স:** Render ফ্রি টিয়ারে ডিস্ক persistent না। মানে আপনি যখনই নতুন কোড পুশ করে রিডিপ্লয় করবেন (নতুন বিল্ড), তখন পুরনো bot.php ফাইল আর ডাটাবেজ (ইউজার/বট তালিকা) মুছে যাবে। শুধু "sleep→wake" (cron দিয়ে জাগানো) এ ডাটা মুছবে না, কিন্তু কোড আপডেট করলে মুছবে।
  - **সমাধান (ফ্রি না):** Render এ Persistent Disk অ্যাড করা যায় (~$0.25/GB/মাস)। এটা optional — শুরুতে টেস্টিং এর জন্য দরকার নেই।
  - **সমাধান (ফ্রি):** নিয়মিত `storage/database.sqlite` এবং `storage/bots/` ফোল্ডার ডাউনলোড করে ব্যাকআপ রাখুন Render Shell থেকে, অথবা প্রোডাকশনে যাওয়ার আগে persistent disk অ্যাড করুন।

- **নিরাপত্তা:** এই প্যানেল ইউজারের আপলোড করা PHP কোড সরাসরি রান করে। আমি বেসিক প্রোটেকশন রেখেছি (`exec/shell_exec/system` ইত্যাদি ফাংশন বন্ধ, `open_basedir` রেসট্রিকশন, error catching), কিন্তু এটা একটা সত্যিকারের প্রোডাকশন-গ্রেড sandbox (যেমন per-bot আলাদা Docker container) না। **শুধু নিজে ব্যবহার করুন বা বিশ্বস্ত মানুষদের সাথে শেয়ার করুন — অপরিচিত মানুষের জন্য পাবলিকলি ওপেন করলে কেউ ক্ষতিকর কোড আপলোড করার চেষ্টা করতে পারে।**

---

## ফাইল স্ট্রাকচার

```
bot-panel/
├── Dockerfile              # Render এর জন্য PHP + Apache সেটআপ
├── render.yaml             # Render blueprint (optional)
├── public/                 # ওয়েবসাইটের সব পেজ
│   ├── index.php
│   ├── register.php / login.php / logout.php
│   ├── dashboard.php
│   ├── bots.php / create_bot.php / bot.php
│   ├── file_edit.php / logs.php
│   ├── bot_action.php / delete_bot.php
│   ├── webhook.php         # Telegram থেকে মেসেজ এলে এটা কল হয়
│   └── assets/style.css
├── includes/                # কোর লজিক
│   ├── config.php / db.php / auth.php
│   ├── telegram_api.php
│   └── functions.php
└── storage/                 # ডাটাবেজ + প্রতিটা বটের bot.php ফাইল থাকবে এখানে
```
