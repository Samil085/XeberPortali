# 📰 Xəbər Portalı

## 🌟 Layihə Haqqında
Bu layihə müasir və dinamik xəbər portalıdır. Portal istifadəçilərə ən son xəbərləri, son dəqiqə məlumatlarını və müxtəlif kateqoriyalarda məqalələri təqdim edir.

## 🚀 Əsas Xüsusiyyətlər
- 📱 Tam responsiv dizayn
- ⚡ Son dəqiqə xəbərləri
- 🎯 Kateqoriyalara görə xəbərlər
- 📊 Ən çox oxunan xəbərlər bölməsi
- 🌤️ Hava proqnozu inteqrasiyası
- 💱 Valyuta məzənnələri
- 🔍 Xəbər axtarışı
- 📸 Şəkil qalereyası

## 🛠️ Texnologiyalar
- PHP 7.4+
- MySQL 5.7+
- HTML5
- CSS3
- JavaScript/jQuery
- Bootstrap 5
- Swiper.js
- AOS (Animate On Scroll)

## ⚙️ Quraşdırma
1. Layihəni klonlayın:
```bash
git clone https://github.com/Samil085/XeberPortali.git
```

2. Verilənlər bazasını yaradın:
- MySQL-də yeni baza yaradın
- `database.sql` faylını import edin

3. Konfiqurasiya:
- `config/db.php` faylında verilənlər bazası məlumatlarını düzəldin:
```php
$db_host = 'localhost';
$db_name = 'your_database';
$db_user = 'your_username';
$db_pass = 'your_password';
```

4. Veb serveri konfiqurasiya edin:
- Layihə qovluğunu veb server root qovluğuna yerləşdirin
- Virtual host konfiqurasiyasını edin (lazım olarsa)

## 📝 İstifadə
### Admin Panel
- `/admin` ünvanına daxil olun
- Standart giriş məlumatları:
  - İstifadəçi adı: `admin`
  - Şifrə: `admin123`

### Xəbər Əlavə Etmə
1. Admin panelə daxil olun
2. "Xəbər Əlavə Et" bölməsinə keçin
3. Xəbər məlumatlarını daxil edin:
   - Başlıq
   - Məzmun
   - Kateqoriya
   - Şəkil
   - Son dəqiqə (əgər varsa)

## 🔐 Təhlükəsizlik
- SQL injection qorunması
- XSS qorunması
- CSRF token istifadəsi
- Şifrələnmiş sessiyalar
- Fayl yükləmə təhlükəsizliyi

## 🤝 Töhfə Vermə
1. Fork edin
2. Feature branch yaradın (`git checkout -b feature/YeniXususiyyet`)
3. Dəyişiklikləri commit edin (`git commit -am 'Yeni xüsusiyyət əlavə edildi'`)
4. Branch-i push edin (`git push origin feature/YeniXususiyyet`)
5. Pull Request yaradın

## 📄 Lisenziya
Bu layihə MIT lisenziyası altında yayımlanıb. Ətraflı məlumat üçün [LICENSE](LICENSE) faylına baxın.
