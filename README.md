# 🛍️ ቅዳሜ ገበያ — Community Digital Marketplace

**An AI-integrated, bilingual (Amharic/English) digital marketplace built on Telegram for Kelela's weekly Saturday market, Wollo, Ethiopia.**

[![Live Form](https://img.shields.io/badge/Live%20Form-digitalmender.com%2Flist-2D6A4F?style=flat-square)](https://www.digitalmender.com/list)
[![Telegram Channel](https://img.shields.io/badge/Telegram-@gebeya__qdame-2CA5E0?style=flat-square&logo=telegram)](https://t.me/gebeya_qdame)
[![Language](https://img.shields.io/badge/Language-PHP%20%7C%20JavaScript-777BB4?style=flat-square)]()
[![AI](https://img.shields.io/badge/AI-Google%20Gemini%201.5%20Flash-4285F4?style=flat-square&logo=google)]()

---

## 🌍 What Is This?

Kelela is a semi-urban market town in the Wollo area of Amhara Region, Ethiopia. Every Saturday, hundreds of sellers gather for a traditional market — but most of them have no way to reach buyers digitally. Many cannot type, have limited data connectivity (2G/3G), and are more comfortable communicating in Amharic than English.

**ቅዳሜ ገበያ** (Saturday Market) solves this by giving those sellers a simple, mobile-first digital listing system built on Telegram — a platform they already use and that works reliably on basic smartphones with minimal data.

**Sellers submit a listing by:**
1. Filling a lightweight web form at [digitalmender.com/list](https://www.digitalmender.com/list) — in Amharic, English, or both
2. Sending a voice message in Amharic or English directly to the Telegram bot *(voice module in active development)*

**The system automatically:**
- Saves the listing photo to the server
- Posts the formatted listing to the [Telegram channel](https://t.me/gebeya_qdame)
- Records the seller's data in a Google Sheets database

No manual moderation. No technical knowledge required from the seller.

---

## 🏗️ Architecture

```
Seller (mobile, 2G/3G)
        │
        ├── 1. Web Form (PHP)        → photo upload → server
        │      digitalmender.com/list     ↓
        │                            Make.com Webhook
        │                                 ↓
        │                        ┌────────┴────────┐
        │                        ▼                 ▼
        │               Telegram Channel     Google Sheets
        │                (auto post)          (data record)
        │
        └── 2. Voice Message (Telegram Bot)   [in development]
                       ↓
               Make.com Webhook
                       ↓
               Gemini 1.5 Flash API
            (Amharic/English transcription
             + structured JSON extraction)
                       ↓
               ┌───────┴───────┐
               ▼               ▼
      Telegram Channel    Google Sheets
```

---

## ⚙️ Tech Stack

| Layer | Technology |
|---|---|
| Listing form (front-end) | HTML5, CSS3, Vanilla JavaScript |
| Listing form (back-end) | PHP 8+, cURL |
| File hosting | cPanel shared hosting (digitalmender.com) |
| Automation pipeline | Make.com (webhook + multi-module scenarios) |
| Marketplace channel | Telegram Bot API |
| Data logging | Google Sheets API (via Make.com) |
| AI processing | Google Gemini 1.5 Flash API |
| Voice input | Telegram voice messages (OGG Opus audio) |

---

## ✨ Key Features

- **Bilingual interface** — Amharic and English throughout; sellers can respond in either language
- **Anonymous submission** — no account or login required; anyone can list an item
- **Photo upload** — mobile-first drag-and-drop zone with live preview; saves to server, sends URL to automation
- **Low-bandwidth design** — works on 2G/3G; minimal dependencies, no frameworks
- **AI voice listing** *(in development)* — sellers send a voice message in Amharic or English; Gemini transcribes it and extracts: seller name, phone, item, category, price, description
- **Fully automated** — from submission to channel post to database record with zero human intervention

---

## 🚀 Deployment Guide

### 1. PHP Listing Form

```
public_html/
└── list/
    ├── index.php        ← this file
    └── uploads/         ← create this folder; set permissions to 755
```

Edit the constants at the top of `index.php`:

```php
define('MAKE_WEBHOOK',     'YOUR_MAKE_COM_WEBHOOK_URL');
define('CHANNEL_USERNAME', 'your_channel_username');   // without @
define('SITE_URL',         'https://yourdomain.com/list');
define('UPLOAD_DIR',       __DIR__ . '/uploads/');
define('MAX_FILE_MB',      8);
```

Upload via cPanel File Manager, then set `uploads/` folder permissions to **755**.

### 2. Make.com Scenario

The automation pipeline uses these modules in sequence:

1. **Custom Webhook** (trigger) — receives JSON payload from the PHP form
2. **Telegram Bot › Send a Photo** — posts listing image + caption to channel
3. **Google Sheets › Add a Row** — records seller data to spreadsheet

The webhook payload structure:
```json
{
  "seller_name": "Halid Assen",
  "phone": "0912912291",
  "item": "Samsung Galaxy Phone",
  "category": "Electronics",
  "price": "2500 ETB",
  "description": "Used, good condition, with charger",
  "photo_url": "https://yourdomain.com/list/uploads/item_abc123.jpg",
  "submitted_at": "2026-11-08 14:30"
}
```

### 3. Voice Module (Gemini AI)

A separate Make.com scenario processes voice listings:

1. **Telegram Bot › Watch Messages** — filters for voice messages only
2. **Telegram Bot › Get a File** — retrieves OGG audio file path
3. **HTTP › Get a File** — downloads the binary audio
4. **HTTP › Make a Request** — sends base64 audio to Gemini 1.5 Flash API
5. **JSON › Parse JSON** — extracts structured fields from Gemini response
6. **Telegram Bot › Send a Message** — posts formatted listing to channel
7. **Google Sheets › Add a Row** — logs the voice submission

Gemini prompt is engineered for Amharic/English bilingual extraction with strict JSON output schema.

---

## 📸 Screenshots

| Listing Form | Channel Post |
|---|---|
| `digitalmender.com/list` | `t.me/gebeya_qdame` |
| Mobile-first PHP form with photo upload | Auto-formatted Telegram listings |

---

## 👨‍💻 About the Developer

**Halid Assen** — IT Professional & Digital Skills Trainer, Kelela, Wollo, Ethiopia

- 📧 halidassen@gmail.com
- 💼 linkedin.com/in/halid-assen
- 🌐 digitalmender.com

This project is part of an ongoing effort to build practical digital solutions for semi-urban Ethiopian communities — focusing on low-connectivity resilience, local language support, and accessibility for users with limited digital literacy.

---

*Built with ❤️ for Kelela's Saturday market community.*
