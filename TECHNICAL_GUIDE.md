# 💻 الدليل التقني والبرمجي المفصل لمشروع التخرج
## نظام منصة الخدمات المحلية (دبرها - Dabberha)
### التقرير البرمجي والتحليلي للجنة المناقشة والتقييم

---

## 1. جدول التقنيات المستخدمة وأدوارها (Technologies Used)

تم بناء وتطوير منصة **دبرها** باستخدام حزمة من أحدث التقنيات البرمجية مفتوحة المصدر، مقسمة إلى الطبقات التالية:

| الطبقة التقنية (Layer) | الأداة / المكتبة | الدور البرمجي والوظيفي |
| :--- | :--- | :--- |
| **لغة البرمجة الخلفية (Backend)** | **PHP 8.0+** | تنفيذ منطق العمل (Business Logic)، إدارة الجلسات، التحقق من الصلاحيات، ومعالجة الطلبات. |
| **محرك قاعدة البيانات (Database)** | **MySQL / MariaDB** | تخزين البيانات العلائقية (Relational Data) وربط الجداول بالمفاتيح الأساسية والأجنبية. |
| **واجهة الاتصال بالبيانات (Data Access)** | **PHP Data Objects (PDO)** | الربط الآمن بقاعدة البيانات واستخدام الاستعلامات المجهزة (Prepared Statements) لمنع الاختراق. |
| **إطار التصميم والواجهات (CSS Framework)** | **Tailwind CSS (v3)** | بناء واجهات عصرية متجاوبة وسريعة عبر فئات التنسيق المباشرة (Utility Classes) ونظام Grid و Flexbox. |
| **نظام التصميم والألوان (Design System)** | **Material Design 3 (MD3)** | تطبيق سمة الألوان الطينية الدافئة (Terracotta Palette: `#95442b`, `#fff8f6`) وتنسيق المسافات. |
| **الأيقونات والرموز (Icons)** | **FontAwesome 6 + Google Material** | عرض أيقونات الخدمات الديناميكية، النجوم الذهبية للتقييم، وشارات الحالات. |
| **الخطوط والطباعة (Typography)** | **Google Fonts (خط تجوال Tajawal)** | خط عربي حديث ومقروء بوضوح في جميع الشاشات والأجهزة. |
| **لغة التفاعل الأمامية (Frontend JS)** | **Vanilla JavaScript (ES6+)** | التحقق التفاعلي الفوري من كلمة المرور (6 خانات)، تبديل بطاقات الحسابات، وتحسين تفاعل النماذج. |

---

## 2. هيكل قاعدة البيانات والعلاقات بين الجداول (Database Schema & Foreign Keys)

تحتوي قاعدة البيانات `local_services_db` على **6 جداول رئيسية مترابطة علائقياً**:

```
                       ┌────────────────┐
                       │     users      │ (المستخدمين)
                       └───┬────────┬───┘
          ┌────────────────┘        └────────────────┐
          │ (1 : N)                                  │ (1 : N)
          ▼                                          ▼
   ┌──────────────┐                           ┌──────────────┐
   │   services   │ (الخدمات)                 │   bookings   │ (الحجوزات)
   └───┬──────────┘                           └───┬──────────┘
       │                                          │
       │ (N : 1)                                  │ (1 : 1)
       ▼                                          ▼
┌──────────────┐                              ┌──────────────┐
│  categories  │ (التصنيفات)                  │   reviews    │ (التقييمات)
└──────────────┘                              └──────────────┘
```

---

### شرح تفصيلي لحقول الجداول والمفاتيح الأجنبية (Foreign Keys):

#### 1. جدول المستخدمين (`users`):
* `id` (**Primary Key**): المعرف الرقمي الفريد لكل مستخدم (تزايد تلقائي Auto Increment).
* `name`: اسم المستخدم الكامل.
* `email` (**Unique Key**): البريد الإلكتروني الفريد لمنع تكرار الحسابات.
* `password`: كلمة المرور المشفرة بتشفير `Bcrypt` الآمن (سلسلة نصية تبدأ بـ `$2y$...`).
* `role`: نوع الحساب كقيمة محددة (`enum: 'admin', 'provider', 'customer'`).
* `phone`: رقم الهاتف بصيغة ليبيا الدولية (`+218 91 000 0000`).
* `address`: العنوان والمدينة (طرابلس، بنغازي، مصراتة، إلخ).
* `status`: حالة الحساب (`'active'`, `'suspended'`).
* `created_at`: تاريخ ووقت التسجيل.

#### 2. جدول التصنيفات (`categories`):
* `id` (**Primary Key**): المعرف الفريد للتصنيف.
* `name`: اسم التصنيف (مثل: تنظيف منازل، سباكة، ميكانيكا سيارات).
* `description`: وصف موجز لطبيعة التصنيف.
* `icon`: كلاس أيقونة `FontAwesome` المختار من الإدارة (مثل: `fa-solid fa-broom`).

#### 3. جدول الخدمات (`services`):
* `id` (**Primary Key**): المعرف الفريد للخدمة.
* `provider_id` (**Foreign Key** ➔ يربط مع `users.id`): معرف مزود الخدمة الذي يملك هذا العرض.
* `category_id` (**Foreign Key** ➔ يربط مع `categories.id`): معرف التصنيف التابعة له الخدمة.
* `title`: عنوان الخدمة (مثال: صيانة كهرباء المنازل).
* `description`: شرح تفصيلي للخدمة والمعدات.
* `price`: السعر بالدينار الليبي (`DECIMAL(10,2)`).
* `price_type`: نوع التسعير (`'fixed'` سعر ثابت، أو `'hourly'` بالساعة).
* `latitude` / `longitude`: الإحداثيات الجغرافية لموقع تقديم الخدمة لحساب المسافات.
* `status`: حالة الخدمة (`'active'`, `'inactive'`).

#### 4. جدول الحجوزات (`bookings`):
* `id` (**Primary Key**): المعرف الفريد لطلب الحجز.
* `customer_id` (**Foreign Key** ➔ يربط مع `users.id`): معرف الزبون صاحب الطلب.
* `service_id` (**Foreign Key** ➔ يربط مع `services.id`): معرف الخدمة المطلوبة.
* `booking_date`: تاريخ وتوقيت الحجز المطلوب.
* `status`: حالة الحجز (`'pending'` معلق، `'confirmed'` مؤكد، `'completed'` مكتمل، `'cancelled'` ملغي).
* `notes`: ملاحظات الزبون الإضافية والعنوان التفصيلي.
* `total_price`: السعر الإجمالي المسجل بالدينار الليبي وقت الحجز.

#### 5. جدول التقييمات والمراجعات (`reviews`):
* `id` (**Primary Key**): المعرف الفريد للتقييم.
* `booking_id` (**Foreign Key** ➔ يربط مع `bookings.id`): لضمان أن التقييم صادر من زبون حقيقي أتم الحجز.
* `customer_id` (**Foreign Key** ➔ يربط مع `users.id`): معرف الزبون كاتب التقييم.
* `service_id` (**Foreign Key** ➔ يربط مع `services.id`): معرف الخدمة المقيمة.
* `rating`: عدد النجوم كقيمة رقمية من 1 إلى 5.
* `comment`: نص رأي وملاحظات الزبون.

#### 6. جدول التنبيهات (`notifications`):
* `id` (**Primary Key**): معرف الإشعار.
* `user_id` (**Foreign Key** ➔ يربط مع `users.id`): معرف المستخدم المستلم.
* `title`: عنوان التنبيه.
* `message`: نص الإشعار.
* `is_read`: حالة القراءة (0 غير مقروء، 1 مقروء).

---

## 3. شرح الأكواد والآليات البرمجية الأساسية (Technical Code Logic)

### 3.1 الاتصال الآمن بقاعدة البيانات عبر PDO (`config/db.php`)
تستخدم المنصة مكتبة `PDO` بدلاً من `mysqli` القديمة لتوفير مرونة وأمان عاليين:

```php
<?php
$host = '127.0.0.1';
$port = '3307';
$db   = 'local_services_db';
$user = 'root';
$pass = '';

try {
    // إنشاء كائن الاتصال وضبط الترميز لـ utf8mb4
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    
    // تفعيل وضع إطلاق الاستثناءات عند حدوث أي خطأ في الاستعلامات
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>
```

---

### 3.2 نظام الاستعلامات المجهزة وحماية SQL Injection (Prepared Statements)
يتم تنفيذ جميع عمليات الإدراج والتعديل والبحث عبر الاستعلامات المجهزة (`prepare` و `execute`)، مما يفصل كود الـ SQL تماماً عن مدخلات المستخدم ويجعل حقن الـ SQL مستحيلاً:

```php
// مثال من includes/db/users_db.php لإدراج مستخدم جديد بأمان تام:
function createUser(array $data): int {
    global $pdo;
    
    // تجهيز الاستعلام بعلامات الاستفهام كمعاملات وسيطة (?)
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role, phone, address, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    // تمرير البيانات بشكل منفصل ومحمي
    $stmt->execute([
        trim($data['name']),
        trim($data['email']),
        $data['password'],
        $data['role'] ?? 'customer',
        trim($data['phone'] ?? ''),
        trim($data['address'] ?? ''),
        $data['status'] ?? 'active'
    ]);
    
    return (int)$pdo->lastInsertId();
}
```

---

### 3.3 تشفير كلمات المرور والترقية التلقائية (Password Hashing & Auto-Upgrade)
* يتم تشفير كلمات المرور باستخدام دالة `password_hash($raw, PASSWORD_DEFAULT)` التي تعتمد خوارزمية `Bcrypt` المقاومة للهجمات.
* في صفحة تسجيل الدخول [`public/login.php`](file:///d:/ps/htdocs/local-services-platform/public/login.php)، يدعم النظام فحص كلمات المرور المشفرة عبر `password_verify`، كما يدعم الترقية التلقائية الفورية لأي حسابات قديمة:

```php
// التحقق من صحة كلمة المرور والترقية التلقائية:
$user = getUserByEmail($email);
$is_password_valid = false;

if ($user && $user['status'] === 'active') {
    if (password_verify($password, $user['password'])) {
        $is_password_valid = true;
    } elseif ($password === $user['password']) {
        // إذا كان الحساب مسجلاً قديماً بكلمة مرور عادية، يتم قبوله وترقيته للتشفير تلقائياً
        $is_password_valid = true;
        updateUserPassword((int)$user['id'], $password);
    }
}
```

---

### 3.4 إدارة الجلسات والتحقق من الصلاحيات (Sessions & RBAC)
تعتمد حماية الصفحات في [`includes/auth.php`](file:///d:/ps/htdocs/local-services-platform/includes/auth.php) على جلسات الخادم `$_SESSION`:

```php
function requireRole(string $required_role): void {
    requireLogin(); // التحقق أولاً من تسجيل الدخول
    if (getUserRole() !== $required_role) {
        // إذا كان الزبون يحاول دخول لوحة المزود أو الإدارة، يتم طرده للصفحة الرئيسية
        header('Location: /local-services-platform/index.php');
        exit;
    }
}
```

---

### 3.5 خوارزمية التوصية الذكية وفرز الخدمات (Recommendation & Proximity Algorithm)
في صفحة [`public/browse-services.php`](file:///d:/ps/htdocs/local-services-platform/public/browse-services.php)، يتم ترتيب الخدمات المعروضة للزبون بناءً على **معيار مركب (Composite Score)**:
1. **وزن التقييم (60%)**: يعتمد على متوسط نجوم الخدمة من 5.
2. **وزن القرب الجغرافي (40%)**: يعتمد على حساب المسافة بين الزبون ومزود الخدمة عبر **معادلة هافرسين (Haversine Formula)**:

$$\text{Distance} = 2 R \cdot \arcsin\left(\sqrt{\sin^2\left(\frac{\Delta\text{lat}}{2}\right) + \cos(\text{lat}_1)\cos(\text{lat}_2)\sin^2\left(\frac{\Delta\text{lon}}{2}\right)}\right)$$

```php
// كود حساب التوصية المركبة:
$rating_score = ($avg_rating / 5.0) * 0.6;          // 60% لتقييم النجوم
$distance_score = max(0, (1 - ($dist / 50.0))) * 0.4; // 40% للقرب ضمن نطاق 50 كم
$total_recommendation = $rating_score + $distance_score;
```

---

### 3.6 دورة حياة الحجز وحالات الطلب (Booking State Machine)

```
  [ حجز جديد ] ──► حالة معلقة (Pending)
                         │
         ┌───────────────┴───────────────┐
         ▼                               ▼
   [ قبول المزود ]                 [ إلغاء الحجز ]
         │                               │
         ▼                               ▼
   حالة مؤكدة (Confirmed)           حالة ملغية (Cancelled)
         │
         ▼
   [ إتمام الخدمة ]
         │
         ▼
   حالة مكتملة (Completed) ──► يتاح للزبون وضع التقييم بالنجوم ⭐
```

---

## 4. تدابير الحماية والأمان المطبقة في النظام (Security Measures)

1. **الحماية من حقن قواعد البيانات (SQL Injection)**:
   - استخدام استعلامات الـ PDO المجهزة (`PDO Prepared Statements`) في كافة ملفات `includes/db/` بنسبة 100%.
2. **الحماية من هجمات حقن النصوص البرمجية (XSS - Cross-Site Scripting)**:
   - تنظيف وتمرير أي نص يتم إدخاله من المستخدم عبر دالة `htmlspecialchars($str, ENT_QUOTES, 'UTF-8')` قبل طباعته في الـ HTML.
3. **التحقق المزدوج من كلمات المرور (Frontend & Backend Validation)**:
   - في الواجهة الأمامية: التحقق الفوري بأن كلمة المرور 6 خانات على الأقل قبل الإرسال.
   - في الواجهة الخلفية: فحص طول السلسلة النصية `strlen($password) < 6` قبل الإدراج في قاعدة البيانات.
4. **حماية التوجيه والأذونات (Role-Based Access Control)**:
   - فحص دور المستخدم في الجلسة ومنع وصول أي مستخدم لصفحات لا يملك تصريحاً لها.
5. **توحيد العملة بالدينار الليبي (`د.ل`) وتوافق الاتجاه العربي (RTL)**:
   - استبدال رمز الدولار بالدينار الليبي في كافة الشاشات والتقارير.
   - تعديل اتجاه أسهم القوائم المنسدلة لتكون في جهة اليسار تلقائياً في وضع RTL دون تغطية النصوص.
