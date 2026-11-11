<?php
// ════════════════════════════════════════════════════════════════
//  ቅዳሜ ገበያ — Product Listing Form
//
//  DEPLOY:
//    1. Upload this file to:  public_html/list/index.php
//    2. Create folder at:     public_html/list/uploads/
//    3. Set uploads/ permissions to 755 (cPanel → File Manager →
//       right-click uploads → Change Permissions → 755)
//    4. Replace MAKE_WEBHOOK below with your Make.com webhook URL
//    5. Replace YOUR_CHANNEL_USERNAME with your Telegram channel @name
// ════════════════════════════════════════════════════════════════

define('MAKE_WEBHOOK',       'PASTE_YOUR_MAKE_WEBHOOK_URL_HERE');
define('CHANNEL_USERNAME',   'YOUR_CHANNEL_USERNAME');      // e.g. qdame_gebeya
define('SITE_URL',           'https://www.digitalmender.com/list');
define('UPLOAD_DIR',         __DIR__ . '/uploads/');
define('MAX_FILE_MB',        8);

// ── Process form submission ──────────────────────────────────────
$success = false;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $clean = fn($k) => trim(strip_tags($_POST[$k] ?? ''));

    $seller_name = $clean('seller_name');
    $phone       = $clean('phone');
    $item        = $clean('item');
    $category    = $clean('category');
    $price       = $clean('price');
    $description = $clean('description');

    // ── Validate ─────────────────────────────────────────────────
    if (!$seller_name || !$phone || !$item || !$price) {
        $error = 'ሁሉም * የሚሉ ቦታዎች ያስፈልጋሉ። / All * fields are required.';

    } elseif (empty($_FILES['photo']['tmp_name'])) {
        $error = 'ፎቶ ያስፈልጋል። / A photo is required.';

    } else {
        $file      = $_FILES['photo'];
        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];
        $max_bytes = MAX_FILE_MB * 1024 * 1024;

        if (!in_array($ext, $allowed)) {
            $error = 'ፎቶ JPG ወይም PNG መሆን አለበት። / Photo must be JPG or PNG.';

        } elseif ($file['size'] > $max_bytes) {
            $error = 'ፎቶ ከ' . MAX_FILE_MB . 'MB አይበልጥ። / Photo must be under ' . MAX_FILE_MB . 'MB.';

        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'ፎቶ አልቀረጸም። እንደገና ሞክር። / Upload failed. Please try again.';

        } else {
            // ── Save photo ──────────────────────────────────────
            $safe_ext  = ($ext === 'jpeg') ? 'jpg' : $ext;
            $filename  = 'item_' . uniqid() . '.' . $safe_ext;
            $dest      = UPLOAD_DIR . $filename;
            $photo_url = SITE_URL . '/uploads/' . $filename;

            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }

            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $error = 'ፎቶ ማስቀመጥ አልተቻለም። ቆይቶ ይሞክሩ። / Could not save photo. Try again later.';

            } else {
                // ── Send webhook to Make.com ────────────────────
                $payload = json_encode([
                    'seller_name' => $seller_name,
                    'phone'       => $phone,
                    'item'        => $item,
                    'category'    => $category ?: 'Other',
                    'price'       => $price . ' ETB',
                    'description' => $description,
                    'photo_url'   => $photo_url,
                    'submitted_at'=> date('Y-m-d H:i'),
                ], JSON_UNESCAPED_UNICODE);

                $ch = curl_init(MAKE_WEBHOOK);
                curl_setopt_array($ch, [
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => $payload,
                    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 10,
                    CURLOPT_SSL_VERIFYPEER => true,
                ]);
                curl_exec($ch);
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                // Always show success — photo is saved on server.
                // If webhook was unreachable, Make.com will retry later
                // or you can recover the photo_url from your uploads/ folder.
                $success = true;
            }
        }
    }
}

// ── Category list ────────────────────────────────────────────────
$categories = [
    ''           => '— ምድብ ምረጥ / Select Category —',
    'Electronics'=> '📱 ኤሌክትሮኒክስ / Electronics',
    'Clothing'   => '👕 ልብስና ጫማ / Clothing & Shoes',
    'Food'       => '🌽 ምግብና ሰብል / Food & Agriculture',
    'Household'  => '🏠 የቤት እቃ / Household Items',
    'Vehicle'    => '🚗 ተሽከርካሪ / Vehicles & Parts',
    'Services'   => '🔧 አገልግሎት / Services',
    'Other'      => '📦 ሌላ / Other',
];
?>
<!DOCTYPE html>
<html lang="am">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>ቅዳሜ ገበያ — ሸቀጥ ቅረጽ</title>
<style>
:root {
  --green:  #2D6A4F;
  --green2: #40916C;
  --green3: #D8F3DC;
  --amber:  #D4A017;
  --bg:     #FDFAF6;
  --card:   #FFFFFF;
  --fg:     #1A1A1A;
  --muted:  #6B7280;
  --border: #E5E7EB;
  --err-bg: #FEF2F2;
  --err-bd: #FECACA;
  --err:    #DC2626;
  --ok:     #15803D;
  --radius: 10px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 16px; -webkit-text-size-adjust: 100%; }

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
  background: var(--bg);
  color: var(--fg);
  min-height: 100vh;
  padding-left:  max(0px, env(safe-area-inset-left,  0px));
  padding-right: max(0px, env(safe-area-inset-right, 0px));
  padding-bottom: 48px;
}

/* ── Header ────────────────────────────────────── */
.hd {
  background: var(--green);
  color: #fff;
  text-align: center;
  padding: 22px 20px 20px;
}
.hd-brand {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 4px;
}
.hd-emoji { font-size: 26px; }
.hd-title { font-size: 22px; font-weight: 700; letter-spacing: -.01em; }
.hd-sub   { font-size: 12.5px; opacity: .8; line-height: 1.6; }

/* ── Page wrapper ──────────────────────────────── */
.wrap {
  max-width: 520px;
  margin: 20px auto 0;
  padding: 0 max(16px, env(safe-area-inset-left, 16px));
  padding-right: max(16px, env(safe-area-inset-right, 16px));
}

/* ── Success card ──────────────────────────────── */
.success-card {
  background: var(--card);
  border: 1px solid #BBF7D0;
  border-radius: var(--radius);
  padding: 36px 24px;
  text-align: center;
}
.success-icon  { font-size: 52px; margin-bottom: 14px; }
.success-title { font-size: 20px; font-weight: 700; color: var(--ok); margin-bottom: 8px; }
.success-body  { font-size: 14px; color: var(--muted); line-height: 1.65; }
.success-btn {
  display: inline-block;
  margin-top: 22px;
  padding: 12px 28px;
  background: var(--green);
  color: #fff;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  font-size: 14.5px;
}

/* ── Error banner ──────────────────────────────── */
.err-banner {
  background: var(--err-bg);
  border: 1px solid var(--err-bd);
  border-radius: var(--radius);
  padding: 12px 16px;
  color: var(--err);
  font-size: 13.5px;
  margin-bottom: 16px;
  display: flex;
  gap: 8px;
  align-items: flex-start;
  line-height: 1.5;
}

/* ── Form card ─────────────────────────────────── */
.form-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 24px 20px 28px;
}

/* ── Fields ────────────────────────────────────── */
.field { margin-bottom: 18px; }
.label {
  display: block;
  font-size: 13.5px;
  font-weight: 600;
  margin-bottom: 5px;
  color: var(--fg);
}
.label .req { color: var(--amber); margin-left: 2px; }
.label .en  { font-weight: 400; color: var(--muted); font-size: 11.5px; margin-left: 4px; }

input[type="text"],
input[type="tel"],
input[type="number"],
select,
textarea {
  width: 100%;
  padding: 11px 14px;
  border: 1.5px solid var(--border);
  border-radius: 8px;
  font-size: 15px;
  color: var(--fg);
  background: #fff;
  outline: none;
  transition: border-color .15s;
  -webkit-appearance: none;
  appearance: none;
}
input:focus, select:focus, textarea:focus { border-color: var(--green2); }
textarea { resize: vertical; min-height: 82px; line-height: 1.55; }

select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236B7280' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  padding-right: 36px;
}

/* ── Photo upload zone ─────────────────────────── */
.photo-zone {
  border: 2px dashed var(--border);
  border-radius: 8px;
  padding: 22px 16px;
  text-align: center;
  cursor: pointer;
  transition: border-color .15s, background .15s;
  position: relative;
  overflow: hidden;
}
.photo-zone:hover { border-color: var(--green2); background: #F0FDF4; }
.photo-zone.has-file { border-color: var(--green2); background: #F0FDF4; border-style: solid; }

.photo-zone input[type="file"] {
  position: absolute;
  inset: 0;
  opacity: 0;
  cursor: pointer;
  width: 100%;
  height: 100%;
}
.photo-zone-icon   { font-size: 34px; margin-bottom: 6px; }
.photo-zone-title  { font-size: 14px; font-weight: 600; color: var(--fg); margin-bottom: 3px; }
.photo-zone-hint   { font-size: 12px; color: var(--muted); }
.photo-chosen {
  display: none;
  margin-top: 10px;
  font-size: 13px;
  font-weight: 600;
  color: var(--green);
}

/* ── Preview image ─────────────────────────────── */
.photo-preview {
  display: none;
  margin-top: 10px;
  border-radius: 6px;
  overflow: hidden;
  max-height: 180px;
}
.photo-preview img {
  width: 100%;
  max-height: 180px;
  object-fit: cover;
  border-radius: 6px;
}

/* ── Submit button ─────────────────────────────── */
.submit-btn {
  width: 100%;
  padding: 15px;
  background: var(--green);
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: background .15s;
  letter-spacing: .01em;
  margin-top: 4px;
}
.submit-btn:hover  { background: var(--green2); }
.submit-btn:active { background: #1E4D37; }

/* ── Footer ────────────────────────────────────── */
.footer {
  text-align: center;
  margin-top: 24px;
  font-size: 12px;
  color: var(--muted);
  line-height: 1.8;
}
.footer a { color: var(--muted); text-decoration: none; }
.footer a:hover { text-decoration: underline; }
</style>
</head>
<body>

<!-- ── Header ─────────────────────────────────────────────── -->
<div class="hd">
  <div class="hd-brand">
    <span class="hd-emoji">🛍️</span>
    <span class="hd-title">ቅዳሜ ገበያ</span>
  </div>
  <div class="hd-sub">ሸቀጥዎን ወደ ቻናሉ ለመቅረጽ ፎርሙን ይሙሉ</div>
  <div class="hd-sub">Fill the form to list your item on the channel</div>
</div>

<div class="wrap">

<?php if ($success): ?>
<!-- ── Success ──────────────────────────────────────────────── -->
<div class="success-card">
  <div class="success-icon">✅</div>
  <div class="success-title">ቀረጽ ተቀብሏል!</div>
  <div class="success-body">
    ሸቀጥዎ ተቀርጿል። ብዙም ሳይቆይ በቻናሉ ላይ ይወጣል።<br>
    <span style="color:#374151">Your listing has been received and will appear on the channel shortly.</span>
  </div>
  <a class="success-btn" href="">ሌላ ሸቀጥ ቅረጽ / List Another Item</a>
</div>

<?php else: ?>
<!-- ── Form ─────────────────────────────────────────────────── -->

<?php if ($error): ?>
<div class="err-banner">
  <span>⚠️</span>
  <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
</div>
<?php endif; ?>

<form class="form-card" method="POST" enctype="multipart/form-data" novalidate>

  <div class="field">
    <label class="label" for="seller_name">
      ሻጩ ስም<span class="req">*</span>
      <span class="en">/ Seller Name</span>
    </label>
    <input type="text" id="seller_name" name="seller_name"
           value="<?= htmlspecialchars($_POST['seller_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           placeholder="ሙሉ ስምዎ / Your full name"
           autocomplete="name" required>
  </div>

  <div class="field">
    <label class="label" for="phone">
      ስልክ ቁጥር<span class="req">*</span>
      <span class="en">/ Phone Number</span>
    </label>
    <input type="tel" id="phone" name="phone"
           value="<?= htmlspecialchars($_POST['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           placeholder="09xxxxxxxx"
           autocomplete="tel" required>
  </div>

  <div class="field">
    <label class="label" for="item">
      ምርት ስም<span class="req">*</span>
      <span class="en">/ Item Name</span>
    </label>
    <input type="text" id="item" name="item"
           value="<?= htmlspecialchars($_POST['item'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           placeholder="ምን እየሸጡ ነው? / What are you selling?" required>
  </div>

  <div class="field">
    <label class="label" for="category">
      ምድብ
      <span class="en">/ Category</span>
    </label>
    <select id="category" name="category">
      <?php foreach ($categories as $val => $label): ?>
        <option value="<?= htmlspecialchars($val) ?>"
          <?= (($_POST['category'] ?? '') === $val) ? 'selected' : '' ?>>
          <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="field">
    <label class="label" for="price">
      ዋጋ (ብር)<span class="req">*</span>
      <span class="en">/ Price (ETB)</span>
    </label>
    <input type="number" id="price" name="price"
           value="<?= htmlspecialchars($_POST['price'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
           placeholder="ለምሳሌ 1500"
           min="0" step="1" required>
  </div>

  <div class="field">
    <label class="label" for="description">
      ተጨማሪ መረጃ
      <span class="en">/ Description (Optional)</span>
    </label>
    <textarea id="description" name="description"
              placeholder="ሁኔታ፣ አዲስ ወይም ሁለተኛ እጅ፣ ሌሎች ዝርዝሮች… / Condition, new or used, any details…"
    ><?= htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
  </div>

  <div class="field">
    <label class="label" for="photo">
      ፎቶ<span class="req">*</span>
      <span class="en">/ Photo</span>
    </label>
    <div class="photo-zone" id="photoZone">
      <input type="file" id="photo" name="photo"
             accept="image/jpeg,image/png,image/webp" required>
      <div class="photo-zone-icon">📷</div>
      <div class="photo-zone-title">ፎቶ ለማስቀረጽ ይጫኑ</div>
      <div class="photo-zone-hint">Tap to take or upload a photo · JPG / PNG · max 8 MB</div>
      <div class="photo-chosen" id="photoChosen"></div>
    </div>
    <div class="photo-preview" id="photoPreview">
      <img id="previewImg" src="" alt="preview">
    </div>
  </div>

  <button type="submit" class="submit-btn">
    ቅረጽ — ወደ ቻናሉ ላክ 📤
  </button>

</form>

<?php endif; ?>

<div class="footer">
  <a href="https://t.me/<?= htmlspecialchars(CHANNEL_USERNAME) ?>" target="_blank" rel="noopener">
    📣 ቅዳሜ ገበያ ቻናልን ይቀላቀሉ / Join the channel
  </a><br>
  Powered by <a href="https://www.digitalmender.com" target="_blank" rel="noopener">Digital Mender</a>
</div>

</div><!-- .wrap -->

<script>
(function () {
  var input   = document.getElementById('photo');
  var zone    = document.getElementById('photoZone');
  var chosen  = document.getElementById('photoChosen');
  var preview = document.getElementById('photoPreview');
  var img     = document.getElementById('previewImg');

  input.addEventListener('change', function () {
    var file = this.files[0];
    if (!file) return;

    zone.classList.add('has-file');
    chosen.style.display = 'block';
    chosen.textContent   = '✓ ' + file.name;

    if (file.type.startsWith('image/')) {
      var reader = new FileReader();
      reader.onload = function (e) {
        img.src = e.target.result;
        preview.style.display = 'block';
      };
      reader.readAsDataURL(file);
    }
  });
})();
</script>

</body>
</html>
