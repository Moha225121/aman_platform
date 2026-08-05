# نشر منصة أمان على DigitalOcean App Platform

المشروع مجهز للنشر من GitHub باستخدام الملف `.do/app.yaml` وDockerfile. يستخدم PostgreSQL في الإنتاج، ويبني واجهة Vite داخل صورة Docker، ويشغّل migrations وبيانات البداية وفهرس RAG قبل كل نشر.

## قبل إنشاء التطبيق

1. أنشئ مفتاح Laravel محليًا:

   ```bash
   php artisan key:generate --show
   ```

2. افتح `.do/app.yaml` واستبدل القيمتين التاليتين قبل استيراد الملف، أو أدخلهما كمتغيرات مشفرة من لوحة DigitalOcean:

   - `REPLACE_WITH_GENERATED_APP_KEY`
   - `REPLACE_WITH_OPENAI_API_KEY`

لا ترفع القيم الحقيقية إلى GitHub.

## النشر من لوحة DigitalOcean

1. اختر **Create > Apps** واربط حساب GitHub.
2. اختر المستودع `Moha225121/aman_platform` والفرع `main`.
3. استخدم App Spec الموجود في `.do/app.yaml`.
4. اجعل `APP_KEY` و`OPENAI_API_KEY` من نوع **Encrypted/Secret**.
5. راجع تكلفة خدمة الويب وقاعدة PostgreSQL قبل تأكيد الإنشاء.

## النشر بواسطة doctl

بعد تسجيل الدخول في `doctl` وتعديل نسخة محلية من الملف بالقيم المطلوبة:

```bash
doctl apps create --spec .do/app.yaml
```

للتحديث لاحقًا:

```bash
doctl apps update YOUR_APP_ID --spec .do/app.yaml
```

كل push إلى `main` يشغّل نشرًا تلقائيًا. مهمة `database-setup` تنفذ قبل تشغيل النسخة الجديدة.

## ملاحظات RAG

بيانات المعرفة الأساسية تُزرع وتُفهرس تلقائيًا. ملفات PDF المحلية غير مرفوعة إلى GitHub؛ لإدخالها في بيئة الإنتاج، ارفع الملفات إلى مساحة خاصة ومصرح بها ثم شغّل:

```bash
php artisan rag:ingest-pdf /path/to/file.pdf
php artisan rag:index
```

لا تضع مراجع مرخصة أو بيانات مرضى في مستودع عام.
