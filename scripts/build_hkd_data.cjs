/**
 * Build & Sync HKD and Enterprise Dataset for Đông Anh Food Map
 * Toàn bộ dữ liệu Hộ kinh doanh & Doanh nghiệp có số điện thoại được gom gọn trong 1 file duy nhất.
 */
const fs = require('fs');
const path = require('path');

const dataDir = path.join(__dirname, '..', 'database', 'data');
if (!fs.existsSync(dataDir)) {
    fs.mkdirSync(dataDir, { recursive: true });
}

// Đọc danh sách dữ liệu chuẩn từ database/data/hkd_with_phones.json
const targetFile = path.join(dataDir, 'hkd_with_phones.json');
let allHKD = [];

if (fs.existsSync(targetFile)) {
    try {
        allHKD = JSON.parse(fs.readFileSync(targetFile, 'utf8'));
    } catch (err) {
        console.error(`[ERROR] Không thể đọc file JSON ${targetFile}:`, err.message);
        process.exit(1);
    }
}

/**
 * Chuẩn hóa số điện thoại Việt Nam
 */
function phoneClean(p) {
    if (!p) return null;
    let s = String(p).replace(/[^\d]/g, '');
    if (s.startsWith('84') && s.length >= 11) {
        s = '0' + s.slice(2);
    } else if (!s.startsWith('0') && s.length >= 9 && s.length <= 10) {
        s = '0' + s;
    }
    return (s.length >= 9 && s.length <= 11) ? s : null;
}

// Làm sạch và lọc trùng theo MST / Tên
const map = new Map();
for (const item of allHKD) {
    const cleanP = phoneClean(item.phone);
    if (!cleanP) continue;
    
    const key = (item.mst || item.name || '').trim();
    if (key && !map.has(key)) {
        map.set(key, {
            ...item,
            phone: cleanP
        });
    }
}

const finalList = Array.from(map.values());

fs.writeFileSync(targetFile, JSON.stringify(finalList, null, 2), 'utf8');
console.log(`Đã gom gọn toàn bộ dữ liệu vào file: ${targetFile}`);
console.log(`Tổng số Hộ kinh doanh & Doanh nghiệp hợp lệ có SĐT: ${finalList.length}`);

module.exports = finalList;

