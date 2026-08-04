import { useState } from 'react'

// ─── DATA ────────────────────────────────────────────────────────────────────

const VILLAGES = [
  { id: 'dong-ha', name: 'Đông Hà', color: '#F97316', x: 118, y: 155 },
  { id: 'tay-an', name: 'Tây An', color: '#22C55E', x: 542, y: 165 },
  { id: 'phu-loc', name: 'Phú Lộc', color: '#EAB308', x: 308, y: 96 },
  { id: 'nam-thinh', name: 'Nam Thịnh', color: '#3B82F6', x: 316, y: 290 },
  { id: 'bac-huong', name: 'Bắc Hướng', color: '#A855F7', x: 558, y: 312 },
  { id: 'trung-nghia', name: 'Trung Nghĩa', color: '#EC4899', x: 298, y: 430 },
  { id: 'lam-son', name: 'Lâm Sơn', color: '#14B8A6', x: 110, y: 412 },
]

const ROUTES = [
  {
    id: 'route-1',
    name: 'Đông Hà – Tây An',
    length: '8.2km',
    color: '#22C55E',
    stroke: '#16A34A',
    villages: ['dong-ha', 'tay-an', 'phu-loc'],
    path: 'M 118,155 C 188,110 250,92 308,96 C 372,100 462,136 542,165',
    speed: 'route-animated',
  },
  {
    id: 'route-2',
    name: 'Tuyến Trung tâm',
    length: '6.5km',
    color: '#F97316',
    stroke: '#EA580C',
    villages: ['phu-loc', 'nam-thinh', 'trung-nghia'],
    path: 'M 308,96 C 312,168 315,230 316,290 C 317,348 308,388 298,430',
    speed: 'route-animated-slow',
  },
  {
    id: 'route-3',
    name: 'Tuyến Vành đai',
    length: '12.1km',
    color: '#A855F7',
    stroke: '#9333EA',
    villages: ['dong-ha', 'lam-son', 'trung-nghia', 'bac-huong', 'tay-an'],
    path: 'M 118,155 C 86,232 90,322 110,412 C 134,462 216,466 298,430 C 362,450 458,462 558,312 C 590,256 596,206 542,165',
    speed: 'route-animated-fast',
  },
]

interface Loc {
  id: number
  name: string
  village: string
  villageName: string
  type: 'quan-an' | 'nha-hang'
  rating: number
  address: string
  tags: string[]
  cashless: boolean
  open: boolean
  hours: string
  menu: string[]
  payment: string[]
  bankAccount: string
  bank: string
  image: string
  x: number
  y: number
  count: number
}

const LOCATIONS: Loc[] = [
  {
    id: 1,
    name: 'Quán Cơm Bà Năm',
    village: 'dong-ha',
    villageName: 'Thôn Đông Hà',
    type: 'quan-an',
    rating: 4.7,
    address: 'Số 12, Thôn Đông Hà, Xã Bình Minh',
    tags: ['Cơm tấm', 'Bún bò'],
    cashless: true,
    open: true,
    hours: '06:00 – 21:00',
    menu: ['Cơm tấm sườn', 'Bún bò Huế', 'Bánh mì thịt'],
    payment: ['Momo', 'VietQR', 'Ngân hàng'],
    bankAccount: '1234 5678 9012',
    bank: 'Vietcombank',
    image:
      'https://images.unsplash.com/photo-1711633648854-50a30a6df74d?w=420&h=230&fit=crop&auto=format',
    x: 118,
    y: 155,
    count: 3,
  },
  {
    id: 2,
    name: 'Nhà Hàng Phú Lộc Garden',
    village: 'phu-loc',
    villageName: 'Thôn Phú Lộc',
    type: 'nha-hang',
    rating: 4.5,
    address: 'Thôn Phú Lộc, Xã Bình Minh',
    tags: ['Lẩu', 'Hải sản'],
    cashless: true,
    open: true,
    hours: '10:00 – 22:00',
    menu: ['Lẩu thái', 'Cua rang muối', 'Tôm nướng'],
    payment: ['Momo', 'VietQR'],
    bankAccount: '9876 5432 1098',
    bank: 'Techcombank',
    image:
      'https://images.unsplash.com/photo-1587574293340-e0011c4e8ecf?w=420&h=230&fit=crop&auto=format',
    x: 308,
    y: 96,
    count: 4,
  },
  {
    id: 3,
    name: 'Quán Phở Tây An',
    village: 'tay-an',
    villageName: 'Thôn Tây An',
    type: 'quan-an',
    rating: 4.8,
    address: 'Thôn Tây An, Xã Bình Minh',
    tags: ['Phở', 'Bún riêu'],
    cashless: false,
    open: true,
    hours: '05:30 – 11:00',
    menu: ['Phở bò tái nạm', 'Phở gà', 'Bún riêu cua'],
    payment: ['Tiền mặt'],
    bankAccount: '',
    bank: '',
    image:
      'https://images.unsplash.com/photo-1597345637412-9fd611e758f3?w=420&h=230&fit=crop&auto=format',
    x: 542,
    y: 165,
    count: 5,
  },
  {
    id: 4,
    name: 'Nhà Hàng Nam Thịnh Palace',
    village: 'nam-thinh',
    villageName: 'Thôn Nam Thịnh',
    type: 'nha-hang',
    rating: 4.6,
    address: 'Thôn Nam Thịnh, Xã Bình Minh',
    tags: ['Tiệc cưới', 'Món Việt'],
    cashless: true,
    open: false,
    hours: '11:00 – 21:00',
    menu: ['Gà nướng mật ong', 'Cá kho tộ', 'Canh chua cá lóc'],
    payment: ['Momo', 'VietQR', 'Ngân hàng'],
    bankAccount: '5556 6677 7889',
    bank: 'BIDV',
    image:
      'https://images.unsplash.com/photo-1631709497146-a239ef373cf1?w=420&h=230&fit=crop&auto=format',
    x: 316,
    y: 290,
    count: 4,
  },
  {
    id: 5,
    name: 'Quán Bún Lâm Sơn',
    village: 'lam-son',
    villageName: 'Thôn Lâm Sơn',
    type: 'quan-an',
    rating: 4.4,
    address: 'Thôn Lâm Sơn, Xã Bình Minh',
    tags: ['Bún thịt nướng', 'Mì quảng'],
    cashless: true,
    open: true,
    hours: '06:00 – 14:00',
    menu: ['Bún thịt nướng', 'Mì quảng tôm thịt', 'Cao lầu'],
    payment: ['VietQR', 'Ngân hàng'],
    bankAccount: '1112 2233 3445',
    bank: 'Agribank',
    image:
      'https://images.unsplash.com/photo-1611854064186-d8dccbccb031?w=420&h=230&fit=crop&auto=format',
    x: 110,
    y: 412,
    count: 3,
  },
  {
    id: 6,
    name: 'Nhà Hàng Bắc Hướng BBQ',
    village: 'bac-huong',
    villageName: 'Thôn Bắc Hướng',
    type: 'nha-hang',
    rating: 4.3,
    address: 'Thôn Bắc Hướng, Xã Bình Minh',
    tags: ['BBQ', 'Bia hơi'],
    cashless: true,
    open: true,
    hours: '16:00 – 23:00',
    menu: ['Thịt nướng than hoa', 'Hải sản tươi', 'Bia hơi Hà Nội'],
    payment: ['Momo', 'VietQR'],
    bankAccount: '4445 5566 6778',
    bank: 'MB Bank',
    image:
      'https://images.unsplash.com/photo-1628324716243-0c9c29971a58?w=420&h=230&fit=crop&auto=format',
    x: 558,
    y: 312,
    count: 4,
  },
]

// ─── HELPERS ─────────────────────────────────────────────────────────────────

function QRCode() {
  const mods: [number, number][] = [
    [22, 22], [30, 22], [38, 22], [46, 22],
    [26, 26], [34, 26], [42, 26],
    [22, 30], [38, 30], [46, 30],
    [26, 34], [42, 34],
    [22, 38], [26, 38], [30, 38], [46, 38],
    [34, 42], [38, 42],
    [22, 46], [30, 46], [38, 46], [46, 46], [42, 46],
  ]
  return (
    <svg viewBox="0 0 56 56" width="76" height="76" aria-label="QR code">
      <rect width="56" height="56" fill="white" rx="4" />
      <rect x="2" y="2" width="18" height="18" rx="2" fill="none" stroke="#111827" strokeWidth="2.5" />
      <rect x="6" y="6" width="10" height="10" rx="1" fill="#111827" />
      <rect x="36" y="2" width="18" height="18" rx="2" fill="none" stroke="#111827" strokeWidth="2.5" />
      <rect x="40" y="6" width="10" height="10" rx="1" fill="#111827" />
      <rect x="2" y="36" width="18" height="18" rx="2" fill="none" stroke="#111827" strokeWidth="2.5" />
      <rect x="6" y="40" width="10" height="10" rx="1" fill="#111827" />
      {mods.map(([x, y]) => (
        <rect key={`${x}-${y}`} x={x} y={y} width="4" height="4" fill="#111827" />
      ))}
    </svg>
  )
}

// ─── POPUP ───────────────────────────────────────────────────────────────────

function Popup({ loc, onClose }: { loc: Loc; onClose: () => void }) {
  const paymentStyle = (p: string) => {
    if (p === 'Momo') return 'bg-pink-50 text-pink-700 border-pink-100'
    if (p === 'VietQR') return 'bg-blue-50 text-blue-700 border-blue-100'
    if (p === 'Ngân hàng') return 'bg-violet-50 text-violet-700 border-violet-100'
    return 'bg-gray-50 text-gray-600 border-gray-100'
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-4" onClick={onClose}>
      <div className="overlay-enter absolute inset-0 bg-black/40 backdrop-blur-sm" />
      <div
        className="popup-enter relative bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden z-10"
        style={{ maxHeight: '92vh' }}
        onClick={(e) => e.stopPropagation()}
      >
        {/* hero image */}
        <div className="relative h-44 bg-green-100 overflow-hidden shrink-0">
          <img src={loc.image} alt={loc.name} className="w-full h-full object-cover" />
          <div className="absolute inset-0 bg-gradient-to-t from-black/35 to-transparent" />
          <button
            onClick={onClose}
            className="absolute top-3 right-3 bg-white/90 hover:bg-white rounded-full p-2 shadow-md transition-colors"
            aria-label="Đóng"
          >
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
              <path d="M 1 1 L 12 12 M 12 1 L 1 12" stroke="#374151" strokeWidth="2" strokeLinecap="round" />
            </svg>
          </button>
          <span
            className={`absolute top-3 left-3 px-2.5 py-0.5 rounded-full text-xs font-bold ${loc.open ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`}
          >
            {loc.open ? '● Đang mở' : '● Đã đóng'}
          </span>
        </div>

        {/* scrollable body */}
        <div
          className="overflow-y-auto hide-scrollbar"
          style={{ maxHeight: 'calc(92vh - 176px)' }}
        >
          <div className="p-4 space-y-4">
            {/* title + rating */}
            <div>
              <div className="flex items-start justify-between gap-2 mb-1.5">
                <h2
                  className="font-black text-gray-900 text-lg leading-snug"
                  style={{ fontFamily: 'Nunito, sans-serif' }}
                >
                  {loc.name}
                </h2>
                <div className="shrink-0 flex items-center gap-1 bg-amber-50 border border-amber-100 px-2 py-0.5 rounded-full">
                  <span className="text-amber-500 text-sm">★</span>
                  <span className="text-sm font-bold text-amber-700">{loc.rating}</span>
                </div>
              </div>
              <div className="space-y-1">
                <p className="text-xs text-gray-500 flex items-center gap-1.5">
                  <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                    <path d="M 6 1 C 3.8 1 2 2.8 2 5 C 2 8 6 11 6 11 C 6 11 10 8 10 5 C 10 2.8 8.2 1 6 1 Z" stroke="#9CA3AF" strokeWidth="1.2" />
                    <circle cx="6" cy="5" r="1.5" fill="#9CA3AF" />
                  </svg>
                  {loc.address}
                </p>
                <p className="text-xs text-gray-500 flex items-center gap-1.5">
                  <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                    <circle cx="6" cy="6" r="4.5" stroke="#9CA3AF" strokeWidth="1.2" />
                    <path d="M 6 3.5 L 6 6 L 8 7.5" stroke="#9CA3AF" strokeWidth="1.2" strokeLinecap="round" strokeLinejoin="round" />
                  </svg>
                  {loc.hours}
                </p>
              </div>
            </div>

            <hr className="border-gray-100" />

            {/* menu */}
            <div>
              <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                Thực đơn
              </p>
              <div className="flex flex-wrap gap-1.5">
                {loc.menu.map((item) => (
                  <span
                    key={item}
                    className="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg border border-emerald-100"
                  >
                    {item}
                  </span>
                ))}
              </div>
            </div>

            {/* payment */}
            <div>
              <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                Phương thức thanh toán
              </p>
              <div className="flex flex-wrap gap-1.5 mb-3">
                {loc.payment.map((p) => (
                  <span
                    key={p}
                    className={`px-2.5 py-1 text-xs font-semibold rounded-lg border ${paymentStyle(p)}`}
                  >
                    {p}
                  </span>
                ))}
              </div>

              {loc.bankAccount && (
                <div className="bg-gray-50 border border-gray-100 rounded-xl p-3 flex items-center justify-between gap-3">
                  <div>
                    <p className="text-xs text-gray-400 mb-0.5">Số tài khoản</p>
                    <p className="font-mono font-bold text-gray-800 text-sm">{loc.bankAccount}</p>
                    <p className="text-xs text-gray-500 mt-0.5">{loc.bank}</p>
                  </div>
                  <div className="bg-white rounded-xl p-1.5 shadow-sm border border-gray-100 shrink-0">
                    <QRCode />
                  </div>
                </div>
              )}
            </div>

            {/* CTA buttons */}
            <div className="flex gap-2 pt-1 pb-1">
              <button
                className="flex-1 flex items-center justify-center gap-2 bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white font-bold py-2.5 rounded-xl text-sm transition-all"
                style={{ fontFamily: 'Nunito, sans-serif' }}
              >
                <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                  <path d="M 7.5 1 L 14 7.5 L 7.5 14 M 1 7.5 L 14 7.5" stroke="white" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                </svg>
                Chỉ đường
              </button>
              <button
                className="flex-1 flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 active:scale-95 text-white font-bold py-2.5 rounded-xl text-sm transition-all"
                style={{ fontFamily: 'Nunito, sans-serif' }}
              >
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                  <path d="M 3 1 C 2.5 1 2 1.5 1.5 2 C 1 3 1.5 5 3 6.5 C 4.5 8.5 6.5 10.5 8.5 12 C 10 13 12 13 13 12 C 13.5 11 13 10 13 10 L 11.5 9.5 C 11 9 10.5 9 10 9.5 C 9.5 10 9 10.5 9 10.5 C 9 10.5 7.5 9.5 6 8 C 4.5 6.5 4 5 4 5 C 4 5 4.5 4.5 5 4 C 5.5 3.5 5.5 2.5 5 2 L 4 1 C 3.5 0.5 3 1 3 1 Z" fill="white" />
                </svg>
                Gọi ngay
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

// ─── MAP SVG ─────────────────────────────────────────────────────────────────

function MapView({
  activeVillage,
  activeCategory,
  onMarkerClick,
  selectedId,
}: {
  activeVillage: string
  activeCategory: string
  onMarkerClick: (loc: Loc) => void
  selectedId: number | null
}) {
  const visibleLocs = LOCATIONS.filter((loc) => {
    if (activeCategory === 'nha-hang') return loc.type === 'nha-hang'
    if (activeCategory === 'quan-an') return loc.type === 'quan-an'
    if (activeCategory === 'khong-tien-mat') return loc.cashless
    return true
  })

  const routeOpacity = (route: (typeof ROUTES)[0]) => {
    if (activeVillage === 'all') return 1
    return route.villages.includes(activeVillage) ? 1 : 0.07
  }

  const markerOpacity = (loc: Loc) => {
    const catOk = visibleLocs.find((fl) => fl.id === loc.id)
    const villageOk = activeVillage === 'all' || loc.village === activeVillage
    return catOk && villageOk ? 1 : 0.18
  }

  const pinColor = (loc: Loc) => (loc.type === 'quan-an' ? '#22C55E' : '#F97316')

  // scattered tree positions (kept away from routes and markers)
  const trees = [
    [58, 185], [64, 218], [78, 200], [52, 245], [240, 175], [252, 192],
    [455, 178], [492, 208], [582, 190], [58, 358], [68, 385], [148, 330],
    [562, 388], [598, 358], [184, 462], [428, 488], [58, 468], [625, 270],
    [420, 60], [145, 62], [590, 460],
  ]

  return (
    <div className="relative w-full h-full overflow-hidden">
      <svg
        viewBox="0 0 660 500"
        className="w-full h-full"
        style={{ fontFamily: "'Nunito', 'Inter', sans-serif" }}
      >
        <defs>
          <pattern id="mapgrid" width="32" height="32" patternUnits="userSpaceOnUse">
            <path d="M 32 0 L 0 0 0 32" fill="none" stroke="#BBF7D0" strokeWidth="0.6" />
          </pattern>
          <filter id="pin-shadow">
            <feDropShadow dx="0" dy="3" stdDeviation="4" floodColor="#000" floodOpacity="0.18" />
          </filter>
          <filter id="route-glow">
            <feGaussianBlur stdDeviation="5" result="blur" />
            <feComposite in="SourceGraphic" in2="blur" operator="over" />
          </filter>
        </defs>

        {/* base */}
        <rect width="660" height="500" fill="#F0FDF6" />
        <rect width="660" height="500" fill="url(#mapgrid)" opacity="0.85" />

        {/* terrain areas */}
        <ellipse cx="205" cy="305" rx="92" ry="56" fill="#D1FAE5" opacity="0.45" />
        <ellipse cx="438" cy="232" rx="78" ry="50" fill="#FEF9C3" opacity="0.52" />
        <ellipse cx="388" cy="390" rx="68" ry="44" fill="#FCE7F3" opacity="0.38" />
        <ellipse cx="152" cy="72" rx="58" ry="36" fill="#DBEAFE" opacity="0.42" />
        <ellipse cx="585" cy="88" rx="48" ry="32" fill="#EDE9FE" opacity="0.42" />
        <ellipse cx="500" cy="430" rx="55" ry="32" fill="#ECFDF5" opacity="0.5" />

        {/* river */}
        <path
          d="M 0,243 C 72,222 114,262 192,252 C 272,242 308,264 394,250 C 472,238 548,264 660,246"
          fill="none"
          stroke="#BAE6FD"
          strokeWidth="5"
          opacity="0.5"
          strokeLinecap="round"
        />
        <path
          d="M 0,248 C 72,227 114,267 192,257 C 272,247 308,269 394,255 C 472,243 548,269 660,251"
          fill="none"
          stroke="#E0F2FE"
          strokeWidth="2.5"
          opacity="0.38"
          strokeLinecap="round"
        />

        {/* trees */}
        {trees.map(([tx, ty], i) => (
          <g key={i} transform={`translate(${tx}, ${ty})`} opacity="0.52">
            <rect x="-2" y="0" width="4" height="7" rx="1" fill="#6EE7B7" />
            <circle cy="-6" r="6" fill="#34D399" />
          </g>
        ))}

        {/* village halos */}
        {VILLAGES.map((v) => (
          <circle
            key={v.id}
            cx={v.x}
            cy={v.y}
            r={activeVillage === v.id ? 52 : 40}
            fill={v.color}
            opacity={
              activeVillage === 'all' ? 0.07 : activeVillage === v.id ? 0.16 : 0.025
            }
            style={{ transition: 'opacity 0.3s, r 0.3s' }}
          />
        ))}

        {/* route glow (active) */}
        {ROUTES.map((route) =>
          activeVillage === 'all' || route.villages.includes(activeVillage) ? (
            <path
              key={route.id + '-glow'}
              d={route.path}
              fill="none"
              stroke={route.color}
              strokeWidth="10"
              strokeLinecap="round"
              opacity={0.1}
              style={{ transition: 'opacity 0.3s' }}
            />
          ) : null,
        )}

        {/* routes */}
        {ROUTES.map((route) => (
          <path
            key={route.id}
            d={route.path}
            fill="none"
            stroke={route.stroke}
            strokeWidth="3.5"
            strokeLinecap="round"
            opacity={routeOpacity(route)}
            className={route.speed}
            style={{ transition: 'opacity 0.3s' }}
          />
        ))}

        {/* markers */}
        {LOCATIONS.map((loc) => {
          const color = pinColor(loc)
          const op = markerOpacity(loc)
          const cx = loc.x
          const cy = loc.y
          const isSelected = selectedId === loc.id

          return (
            <g
              key={loc.id}
              className="map-marker"
              onClick={() => onMarkerClick(loc)}
              opacity={op}
              style={{ transition: 'opacity 0.3s' }}
            >
              {/* ground shadow */}
              <ellipse cx={cx} cy={cy + 5} rx="13" ry="5" fill="rgba(0,0,0,0.1)" />
              {/* pin body: circle + downward triangle */}
              <circle
                cx={cx}
                cy={cy - 22}
                r="20"
                fill={color}
                filter={isSelected ? 'url(#pin-shadow)' : undefined}
              />
              <polygon
                points={`${cx},${cy + 4} ${cx - 10},${cy - 17} ${cx + 10},${cy - 17}`}
                fill={color}
              />
              {/* white inner circle */}
              <circle cx={cx} cy={cy - 22} r="13" fill="white" opacity="0.95" />
              {/* type label */}
              <text
                x={cx}
                y={cy - 22}
                textAnchor="middle"
                dominantBaseline="central"
                fontSize="7.5"
                fontWeight="800"
                fill={color}
              >
                {loc.type === 'quan-an' ? 'QA' : 'NH'}
              </text>
              {/* count badge (top-right) */}
              <circle cx={cx + 18} cy={cy - 40} r="9.5" fill={color} stroke="white" strokeWidth="2.5" />
              <text
                x={cx + 18}
                y={cy - 40}
                textAnchor="middle"
                dominantBaseline="central"
                fontSize="8"
                fontWeight="900"
                fill="white"
              >
                {loc.count}
              </text>
              {/* cashless badge (top-left) */}
              {loc.cashless && (
                <>
                  <circle cx={cx - 18} cy={cy - 40} r="8" fill="#3B82F6" stroke="white" strokeWidth="2" />
                  <text
                    x={cx - 18}
                    y={cy - 40}
                    textAnchor="middle"
                    dominantBaseline="central"
                    fontSize="8"
                    fontWeight="800"
                    fill="white"
                  >
                    $
                  </text>
                </>
              )}
              {/* village name label */}
              <text
                x={cx}
                y={cy + 20}
                textAnchor="middle"
                dominantBaseline="central"
                fontSize="9.5"
                fontWeight="700"
                fill="#1f2937"
              >
                {loc.villageName.replace('Thôn ', '')}
              </text>
            </g>
          )
        })}

        {/* ── legend: types ── */}
        <g transform="translate(16, 16)">
          <rect width="196" height="44" rx="10" fill="white" opacity="0.92" />
          <circle cx="16" cy="14" r="7" fill="#22C55E" />
          <text x="28" y="18" fontSize="8.5" fontWeight="600" fill="#374151">
            Quán ăn (QA)
          </text>
          <circle cx="16" cy="30" r="7" fill="#F97316" />
          <text x="28" y="34" fontSize="8.5" fontWeight="600" fill="#374151">
            Nhà hàng (NH)
          </text>
          <circle cx="110" cy="14" r="7" fill="#3B82F6" />
          <text x="122" y="18" fontSize="8.5" fontWeight="600" fill="#374151">
            $ Không TM
          </text>
          <text x="122" y="34" fontSize="7.5" fill="#9CA3AF">
            (badge xanh dương)
          </text>
        </g>

        {/* ── legend: routes ── */}
        <g transform="translate(16, 444)">
          <rect width="244" height="50" rx="10" fill="white" opacity="0.92" />
          <text x="10" y="14" fontSize="7.5" fontWeight="800" fill="#6B7280">
            TUYẾN ĐƯỜNG
          </text>
          {ROUTES.map((r, i) => (
            <g key={r.id} transform={`translate(10, ${24 + i * 10})`}>
              <line x1="0" y1="0" x2="22" y2="0" stroke={r.color} strokeWidth="2.5" strokeDasharray="4 3" />
              <text x="28" y="4" fontSize="7.5" fill="#374151" fontWeight="600">
                {r.name} ({r.length})
              </text>
            </g>
          ))}
        </g>

        {/* compass */}
        <g transform="translate(628, 30)">
          <circle cx="0" cy="0" r="18" fill="white" opacity="0.92" />
          <text x="0" y="-4" textAnchor="middle" fontSize="7.5" fontWeight="800" fill="#374151">N</text>
          <path d="M 0,-12 L -4,4 L 0,2 L 4,4 Z" fill="#EF4444" opacity="0.85" />
          <path d="M 0,12 L -4,-4 L 0,-2 L 4,-4 Z" fill="#374151" opacity="0.4" />
        </g>
      </svg>
    </div>
  )
}

// ─── LOCATION CARD ───────────────────────────────────────────────────────────

function LocationCard({ loc, onClick }: { loc: Loc; onClick: () => void }) {
  const village = VILLAGES.find((v) => v.id === loc.village)!
  return (
    <button
      onClick={onClick}
      className="w-full text-left bg-white rounded-2xl overflow-hidden border border-gray-100 hover:border-green-200 hover:shadow-lg transition-all duration-200 group"
    >
      <div className="relative h-32 bg-green-50 overflow-hidden">
        <img
          src={loc.image}
          alt={loc.name}
          className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent" />
        <span
          className={`absolute top-2 left-2 px-2 py-0.5 rounded-full text-xs font-bold ${loc.open ? 'bg-green-500 text-white' : 'bg-gray-500 text-white'}`}
        >
          {loc.open ? 'Đang mở' : 'Đã đóng'}
        </span>
        {loc.cashless && (
          <span className="absolute top-2 right-2 bg-blue-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
            Không TM
          </span>
        )}
      </div>
      <div className="p-3">
        <div className="flex items-start justify-between gap-1 mb-1">
          <h3
            className="font-black text-gray-900 text-sm leading-snug"
            style={{ fontFamily: 'Nunito, sans-serif' }}
          >
            {loc.name}
          </h3>
          <div className="flex items-center gap-0.5 shrink-0">
            <span className="text-amber-400 text-xs">★</span>
            <span className="text-xs font-bold text-amber-700">{loc.rating}</span>
          </div>
        </div>
        <p className="text-xs text-gray-400 mb-2 truncate">{loc.address}</p>
        <div className="flex flex-wrap gap-1 mb-2">
          {loc.tags.map((tag) => (
            <span key={tag} className="px-2 py-0.5 bg-gray-50 text-gray-600 text-xs rounded-md border border-gray-100">
              {tag}
            </span>
          ))}
          <span
            className={`px-2 py-0.5 text-xs rounded-md font-semibold ${loc.type === 'quan-an' ? 'bg-green-50 text-green-700' : 'bg-orange-50 text-orange-700'}`}
          >
            {loc.type === 'quan-an' ? 'Quán ăn' : 'Nhà hàng'}
          </span>
        </div>
        <div
          className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
          style={{ background: village.color + '18', color: village.color }}
        >
          <div className="w-1.5 h-1.5 rounded-full" style={{ background: village.color }} />
          {loc.villageName}
        </div>
      </div>
    </button>
  )
}

// ─── SIDEBAR ─────────────────────────────────────────────────────────────────

const CATEGORIES = [
  { id: 'all', label: 'Tất cả' },
  { id: 'nha-hang', label: 'Nhà hàng' },
  { id: 'quan-an', label: 'Quán ăn' },
  { id: 'khong-tien-mat', label: 'Không tiền mặt' },
]

function Sidebar({
  activeVillage,
  setActiveVillage,
  activeCategory,
  setActiveCategory,
  onCardClick,
}: {
  activeVillage: string
  setActiveVillage: (v: string) => void
  activeCategory: string
  setActiveCategory: (c: string) => void
  onCardClick: (loc: Loc) => void
}) {
  const [search, setSearch] = useState('')

  const filtered = LOCATIONS.filter((loc) => {
    const q = search.toLowerCase()
    const matchSearch =
      !q ||
      loc.name.toLowerCase().includes(q) ||
      loc.tags.some((t) => t.toLowerCase().includes(q)) ||
      loc.villageName.toLowerCase().includes(q)
    const matchCat =
      activeCategory === 'all' ||
      (activeCategory === 'nha-hang' && loc.type === 'nha-hang') ||
      (activeCategory === 'quan-an' && loc.type === 'quan-an') ||
      (activeCategory === 'khong-tien-mat' && loc.cashless)
    const matchVillage = activeVillage === 'all' || loc.village === activeVillage
    return matchSearch && matchCat && matchVillage
  })

  return (
    <aside className="w-80 flex flex-col bg-white border-r border-gray-100 h-full overflow-hidden shrink-0">
      {/* header */}
      <div className="p-4 pb-3 border-b border-gray-100 shrink-0">
        <div className="flex items-center gap-2.5 mb-3">
          <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center shadow-sm shrink-0">
            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
              <circle cx="9" cy="9" r="7" stroke="white" strokeWidth="1.5" />
              <path
                d="M 2 9 L 16 9 M 9 2 C 9 2 6.5 5.5 6.5 9 C 6.5 12.5 9 16 9 16 C 9 16 11.5 12.5 11.5 9 C 11.5 5.5 9 2 9 2 Z"
                stroke="white"
                strokeWidth="1.3"
              />
            </svg>
          </div>
          <div>
            <p className="text-xs text-gray-400 font-medium">Bản đồ số thông minh</p>
            <h1
              className="text-sm font-black text-gray-900 leading-tight"
              style={{ fontFamily: 'Nunito, sans-serif' }}
            >
              Tuyến đường 4.0 – Xã Đông Anh
            </h1>
          </div>
        </div>

        {/* search */}
        <div className="relative">
          <svg
            className="absolute left-3 top-1/2 -translate-y-1/2"
            width="14"
            height="14"
            viewBox="0 0 14 14"
            fill="none"
          >
            <circle cx="6" cy="6" r="5" stroke="#9CA3AF" strokeWidth="1.5" />
            <path d="M 10 10 L 13.5 13.5" stroke="#9CA3AF" strokeWidth="1.5" strokeLinecap="round" />
          </svg>
          <input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Tìm quán ăn, đặc sản…"
            className="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-100 rounded-xl text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-green-300 focus:ring-2 focus:ring-green-100 transition-all"
          />
        </div>
      </div>

      {/* filters */}
      <div className="px-4 py-3 border-b border-gray-100 shrink-0">
        {/* category chips */}
        <div className="flex gap-1.5 flex-wrap mb-3">
          {CATEGORIES.map((cat) => (
            <button
              key={cat.id}
              onClick={() => setActiveCategory(cat.id)}
              className={`px-3 py-1 rounded-full text-xs font-semibold transition-all ${
                activeCategory === cat.id
                  ? 'bg-emerald-500 text-white shadow-sm'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
              }`}
            >
              {cat.label}
            </button>
          ))}
        </div>

        {/* village filter */}
        <p className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Theo thôn</p>
        <div className="flex flex-wrap gap-1.5">
          <button
            onClick={() => setActiveVillage('all')}
            className={`px-3 py-1 rounded-full text-xs font-semibold transition-all ${
              activeVillage === 'all'
                ? 'bg-gray-800 text-white shadow-sm'
                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
            }`}
          >
            Tất cả
          </button>
          {VILLAGES.map((v) => (
            <button
              key={v.id}
              onClick={() => setActiveVillage(activeVillage === v.id ? 'all' : v.id)}
              className="px-3 py-1 rounded-full text-xs font-semibold transition-all border"
              style={{
                background: activeVillage === v.id ? v.color : v.color + '15',
                color: activeVillage === v.id ? 'white' : v.color,
                borderColor: v.color + '45',
              }}
            >
              {v.name}
            </button>
          ))}
        </div>
      </div>

      {/* result count */}
      <div className="px-4 py-2 shrink-0">
        <p className="text-xs text-gray-400">
          Hiển thị <span className="font-bold text-gray-700">{filtered.length}</span> địa điểm
        </p>
      </div>

      {/* cards list */}
      <div className="flex-1 overflow-y-auto hide-scrollbar px-4 pb-4 space-y-3">
        {filtered.length === 0 ? (
          <div className="text-center py-12 text-gray-400">
            <div className="text-4xl mb-3">🗺️</div>
            <p className="text-sm font-medium">Không tìm thấy địa điểm</p>
            <p className="text-xs mt-1">Thử thay đổi bộ lọc</p>
          </div>
        ) : (
          filtered.map((loc) => (
            <LocationCard key={loc.id} loc={loc} onClick={() => onCardClick(loc)} />
          ))
        )}
      </div>
    </aside>
  )
}

// ─── BOTTOM BAR ──────────────────────────────────────────────────────────────

function BottomBar() {
  const stats = [
    { label: 'Tổng tuyến', value: 3, color: '#A855F7' },
    { label: 'Số thôn', value: 7, color: '#14B8A6' },
    { label: 'Nhà hàng', value: 3, color: '#F97316' },
    { label: 'Quán ăn', value: 3, color: '#22C55E' },
    { label: 'Không tiền mặt', value: 8, color: '#3B82F6' },
    { label: 'Đang mở', value: 5, color: '#10B981' },
  ]

  const icons = [
    // road
    <svg key="road" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M 3 15 L 9 3 L 15 15" stroke="#A855F7" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"/><path d="M 5.5 10 L 12.5 10" stroke="#A855F7" strokeWidth="1.4" strokeLinecap="round"/></svg>,
    // village
    <svg key="village" width="18" height="18" viewBox="0 0 18 18" fill="none"><rect x="2" y="8" width="14" height="8" rx="1.5" stroke="#14B8A6" strokeWidth="1.5"/><path d="M 5 8 L 5 5 L 13 5 L 13 8" stroke="#14B8A6" strokeWidth="1.5" strokeLinejoin="round"/><path d="M 5 5 L 9 2 L 13 5" stroke="#14B8A6" strokeWidth="1.5" strokeLinejoin="round"/><rect x="7" y="11" width="4" height="5" rx="0.5" fill="#14B8A6" opacity="0.4"/></svg>,
    // restaurant
    <svg key="restaurant" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M 5 2 L 5 8 M 5 8 C 5 8 3 9 3 11 C 3 12 4 12 5 12 L 5 16" stroke="#F97316" strokeWidth="1.5" strokeLinecap="round"/><path d="M 9 2 L 9 16" stroke="#F97316" strokeWidth="1.5" strokeLinecap="round"/><path d="M 13 2 C 13 2 15 4 15 7 C 15 9 13 10 13 10 L 13 16" stroke="#F97316" strokeWidth="1.5" strokeLinecap="round"/></svg>,
    // bowl
    <svg key="bowl" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M 2 8 C 2 13 5 16 9 16 C 13 16 16 13 16 8 Z" stroke="#22C55E" strokeWidth="1.5" strokeLinejoin="round"/><path d="M 2 8 L 16 8" stroke="#22C55E" strokeWidth="1.5" strokeLinecap="round"/><path d="M 6 5 C 6 5 7 3 9 5 C 11 7 12 4 12 4" stroke="#22C55E" strokeWidth="1.3" strokeLinecap="round"/></svg>,
    // card
    <svg key="card" width="18" height="18" viewBox="0 0 18 18" fill="none"><rect x="2" y="4" width="14" height="10" rx="2" stroke="#3B82F6" strokeWidth="1.5"/><path d="M 2 7 L 16 7" stroke="#3B82F6" strokeWidth="1.5"/><path d="M 5 11 L 8 11" stroke="#3B82F6" strokeWidth="1.5" strokeLinecap="round"/></svg>,
    // open dot
    <svg key="open" width="18" height="18" viewBox="0 0 18 18" fill="none"><circle cx="9" cy="9" r="6.5" stroke="#10B981" strokeWidth="1.5"/><path d="M 6 9 L 8 11 L 12 7" stroke="#10B981" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round"/></svg>,
  ]

  return (
    <div className="h-16 bg-white border-t border-gray-100 flex items-stretch px-2 shrink-0">
      {stats.map((s, i) => (
        <div key={s.label} className="flex-1 flex items-center justify-center gap-2.5 px-2">
          <div className="shrink-0">{icons[i]}</div>
          <div>
            <p
              className="text-xl font-black leading-none"
              style={{ color: s.color, fontFamily: 'Nunito, sans-serif' }}
            >
              {s.value}
            </p>
            <p className="text-xs text-gray-400 leading-none mt-0.5 whitespace-nowrap">{s.label}</p>
          </div>
          {i < stats.length - 1 && <div className="w-px h-8 bg-gray-100 ml-auto" />}
        </div>
      ))}
    </div>
  )
}

// ─── APP ─────────────────────────────────────────────────────────────────────

export default function App() {
  const [activeVillage, setActiveVillage] = useState('all')
  const [activeCategory, setActiveCategory] = useState('all')
  const [selectedLoc, setSelectedLoc] = useState<Loc | null>(null)

  return (
    <div
      className="flex flex-col h-screen bg-gray-50 overflow-hidden"
      style={{ fontFamily: 'Inter, sans-serif' }}
    >
      {/* top header */}
      <header className="h-12 bg-white border-b border-gray-100 flex items-center px-5 gap-4 shrink-0 z-10">
        <div className="flex items-center gap-2">
          <div className="w-7 h-7 rounded-lg bg-gradient-to-br from-green-400 to-emerald-600 flex items-center justify-center shadow-sm">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
              <circle cx="7" cy="7" r="5.5" stroke="white" strokeWidth="1.4" />
              <path
                d="M 1.5 7 L 12.5 7 M 7 1.5 C 7 1.5 5 4 5 7 C 5 10 7 12.5 7 12.5"
                stroke="white"
                strokeWidth="1.2"
              />
            </svg>
          </div>
          <span
            className="font-black text-gray-900 text-sm"
            style={{ fontFamily: 'Nunito, sans-serif' }}
          >
            Xã Đông Anh &nbsp;•&nbsp; Tuyến đường 4.0
          </span>
        </div>

        {/* route pills */}
        <div className="flex items-center gap-2 ml-auto">
          {ROUTES.map((r) => (
            <div
              key={r.id}
              className="flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 border border-gray-100"
            >
              <svg width="22" height="4" viewBox="0 0 22 4">
                <line
                  x1="0"
                  y1="2"
                  x2="22"
                  y2="2"
                  stroke={r.color}
                  strokeWidth="2.5"
                  strokeDasharray="4 3"
                  strokeLinecap="round"
                />
              </svg>
              <span className="text-xs text-gray-600 font-medium">
                {r.name} ({r.length})
              </span>
            </div>
          ))}
          <div className="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 ml-1">
            <div className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse" />
            <span className="text-xs text-emerald-700 font-semibold">Live</span>
          </div>
        </div>
      </header>

      {/* main */}
      <div className="flex flex-1 overflow-hidden">
        <Sidebar
          activeVillage={activeVillage}
          setActiveVillage={setActiveVillage}
          activeCategory={activeCategory}
          setActiveCategory={setActiveCategory}
          onCardClick={setSelectedLoc}
        />
        <main className="flex-1 overflow-hidden">
          <MapView
            activeVillage={activeVillage}
            activeCategory={activeCategory}
            onMarkerClick={setSelectedLoc}
            selectedId={selectedLoc?.id ?? null}
          />
        </main>
      </div>

      <BottomBar />

      {selectedLoc && <Popup loc={selectedLoc} onClose={() => setSelectedLoc(null)} />}
    </div>
  )
}
