/**
 * DATASET FOR DONG ANH PUBLIC SCHOOL MERGER & REORGANIZATION (PA3)
 * Exact 18 schools & original component schools parsed from 20.7. 20h03. PHƯƠNG ÁN SẮP XẾP (PA3).xlsx
 */

window.STORYTELLING_SCHOOLS = {
    // 1. MN Phúc Lộc
    'mn-phuc-loc': {
        mergedSchool: {
            name: 'Mầm non Phúc Lộc',
            address: 'Thôn Phúc Lộc, Xã Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Đỗ Thị Hậu',
            board: [
                { role: 'HT MN Phúc Lộc:', name: 'Cô Đỗ Thị Hậu' },
                { role: 'Phó HT MN Phúc Lộc:', name: 'Cô Vương Thị Huyền' },
                { role: 'Phó HT MN Sao Mai:', name: 'Cô Lê Thị Thúy Hà' }
            ],
            classes: 30,
            students: 759,
            ratio: '25.3 HS/lớp (Quy mô chuẩn)',
            photo: 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/mn-phuc-loc',
            lat: 21.1685,
            lng: 105.8920
        },
        components: [
            {
                name: 'Trường Mầm non Phúc Lộc (Cũ)',
                address: 'Khu A, Thôn Phúc Lộc, Xã Đông Anh',
                principal: 'Cô Nguyễn Thị Hoa',
                phone: '',
                classes: 16,
                students: 405,
                lat: 21.1662,
                lng: 105.8895,
                photo: 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường Mầm non Sao Mai',
                address: 'Khu B, Thôn Phúc Lộc, Xã Đông Anh',
                principal: 'Cô Trần Thị Mai',
                phone: '',
                classes: 14,
                students: 354,
                lat: 21.1710,
                lng: 105.8950,
                photo: 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '2.3 km',
        durationText: '6 phút'
    },

    // 2. MN Cổ Loa
    'mn-co-loa': {
        mergedSchool: {
            name: 'Mầm non Cổ Loa',
            address: 'Thôn Chùa, Xã Cổ Loa, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Nguyễn Thị Nhàn',
            board: [
                { role: 'HT MN Cổ Loa:', name: 'Cô Nguyễn Thị Nhàn' },
                { role: 'HT MN Thành Loa:', name: 'Cô Nguyễn Thị Thu Trang' },
                { role: 'Phó HT MN Thành Loa:', name: 'Cô Đào Thị Kim Yến' }
            ],
            classes: 36,
            students: 947,
            ratio: '26.3 HS/lớp',
            photo: 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/mn-co-loa',
            lat: 21.1398,
            lng: 105.8655
        },
        components: [
            {
                name: 'Trường Mầm non Cổ Loa (Đơn vị 1)',
                address: 'Xóm Chùa, Xã Cổ Loa, Đông Anh',
                principal: 'Cô Lê Thị Nga',
                phone: '',
                classes: 18,
                students: 564,
                lat: 21.1380,
                lng: 105.8620,
                photo: 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường Mầm non Thành Loa',
                address: 'Xóm Chợ, Xã Cổ Loa, Đông Anh',
                principal: 'Cô Phạm Thị Yến',
                phone: '',
                classes: 18,
                students: 383,
                lat: 21.1415,
                lng: 105.8690,
                photo: 'https://images.unsplash.com/photo-1576495199011-87b3f6c21dbb?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '1.8 km',
        durationText: '5 phút'
    },

    // 3. MN Mai Lâm
    'mn-mai-lam': {
        mergedSchool: {
            name: 'Mầm non Mai Lâm',
            address: 'Xã Mai Lâm, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Nguyễn Thị Vân Anh',
            board: [
                { role: 'HT MN Thái Bình:', name: 'Cô Nguyễn Thị Vân Anh' },
                { role: 'HT MN Mai Lâm:', name: 'Cô Phạm Thị Bích Liên' },
                { role: 'Phó HT MN Thái Bình:', name: 'Cô Lương Thị Hương' }
            ],
            classes: 32,
            students: 866,
            ratio: '27.0 HS/lớp',
            photo: 'https://images.unsplash.com/photo-1576495199011-87b3f6c21dbb?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/mn-mai-lam',
            lat: 21.1085,
            lng: 105.8820
        },
        components: [
            {
                name: 'Trường Mầm non Thái Bình',
                address: 'Thôn Thái Bình, Xã Mai Lâm, Đông Anh',
                principal: 'Cô Đỗ Thị Kim',
                phone: '',
                classes: 18,
                students: 521,
                lat: 21.1060,
                lng: 105.8790,
                photo: 'https://images.unsplash.com/photo-1576495199011-87b3f6c21dbb?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường Mầm non Mai Lâm (Cũ)',
                address: 'Thôn Mai Hiên, Xã Mai Lâm, Đông Anh',
                principal: 'Cô Vũ Thị Thu',
                phone: '',
                classes: 14,
                students: 345,
                lat: 21.1110,
                lng: 105.8850,
                photo: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '2.1 km',
        durationText: '6 phút'
    },

    // 4. MN Việt Hùng
    'mn-viet-hung': {
        mergedSchool: {
            name: 'Mầm non Việt Hùng',
            address: 'Thôn Gia Lương, Xã Việt Hùng, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Hoàng Thị Quỳnh Hoa',
            board: [
                { role: 'HT MN Dục Tú:', name: 'Cô Hoàng Thị Quỳnh Hoa' },
                { role: 'HT MN Đông Hội:', name: 'Cô Trương Thị Thúy Hòa' },
                { role: 'HT MN Sao Mai:', name: 'Cô Nguyễn Thị Kim Quế' }
            ],
            classes: 55,
            students: 1440,
            ratio: '26.2 HS/lớp',
            photo: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/mn-viet-hung',
            lat: 21.1480,
            lng: 105.8750
        },
        components: [
            {
                name: 'Trường Mầm non Dục Nội',
                address: 'Thôn Dục Nội, Xã Việt Hùng, Đông Anh',
                principal: 'Cô Hoàng Thị Lan',
                phone: '',
                classes: 17,
                students: 525,
                lat: 21.1460,
                lng: 105.8710,
                photo: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường Mầm non Việt Hùng (Cũ)',
                address: 'Thôn Gia Lương, Xã Việt Hùng, Đông Anh',
                principal: 'Cô Bùi Thị Tâm',
                phone: '',
                classes: 12,
                students: 268,
                lat: 21.1500,
                lng: 105.8780,
                photo: 'https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường Mầm non Dục Tú ',
                address: 'Giáp Ranh Dục Tú, Đông Anh',
                principal: 'Cô Ngô Thị Liên',
                phone: '',
                classes: 26,
                students: 647,
                lat: 21.1440,
                lng: 105.8820,
                photo: 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '3.4 km',
        durationText: '9 phút'
    },

    // 5. MN Uy Nỗ
    'mn-uy-no': {
        mergedSchool: {
            name: 'Mầm non Uy Nỗ',
            address: 'Xã Uy Nỗ, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Trần Thị Yên Giang',
            board: [
                { role: 'HT MN Uy Nỗ:', name: 'Cô Trần Thị Yên Giang' },
                { role: 'HT MN Uy Nỗ:', name: 'Cô Ngô Thị Hạnh' },
                { role: 'Phó HT MN Uy Nỗ A:', name: 'Cô Nguyễn Thị Minh Toan' }
            ],
            classes: 33,
            students: 910,
            ratio: '27.5 HS/lớp',
            photo: 'https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/mn-uy-no',
            lat: 21.1390,
            lng: 105.8500
        },
        components: [
            {
                name: 'Trường Mầm non Uy Nỗ A',
                address: 'Khu A, Xã Uy Nỗ, Đông Anh',
                principal: 'Cô Trịnh Thị Oanh',
                phone: '',
                classes: 15,
                students: 420,
                lat: 21.1375,
                lng: 105.8470,
                photo: 'https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường Mầm non Uy Nỗ (Cũ)',
                address: 'Khu B, Xã Uy Nỗ, Đông Anh',
                principal: 'Cô Đinh Thị Hồng',
                phone: '',
                classes: 18,
                students: 490,
                lat: 21.1405,
                lng: 105.8530,
                photo: 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '1.5 km',
        durationText: '4 phút'
    },

    // 6. MN Đông Hội
    'mn-dong-hoi': {
        mergedSchool: {
            name: 'Mầm non Đông Hội',
            address: 'Thôn Đông Hói, Xã Đông Hội, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Trương Thị Nga',
            board: [
                { role: 'HT MN Việt Hùng:', name: 'Cô Trương Thị Nga' },
                { role: 'HT MN Xuân Canh:', name: 'Cô Nguyễn Thị Hà' },
                { role: 'Phó HT MN Đông Hội:', name: 'Cô Quản Thị Thu Hòa' }
            ],
            classes: 48,
            students: 1464,
            ratio: '30.5 HS/lớp',
            photo: 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/mn-dong-hoi',
            lat: 21.0950,
            lng: 105.8710
        },
        components: [
            {
                name: 'Trường Mầm non Đông Hội (Cũ)',
                address: 'Thôn Lại Đà, Xã Đông Hội, Đông Anh',
                principal: 'Cô Nguyễn Thị Tuyết',
                phone: '',
                classes: 28,
                students: 875,
                lat: 21.0930,
                lng: 105.8680,
                photo: 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường Mầm non Xuân Canh',
                address: 'Thôn Xuân Canh, Xã Xuân Canh, Đông Anh',
                principal: 'Cô Lý Thị Phượng',
                phone: '',
                classes: 20,
                students: 589,
                lat: 21.0970,
                lng: 105.8740,
                photo: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '2.5 km',
        durationText: '7 phút'
    },

    // 7. TH An Dương Vương
    'th-an-duong-vuong': {
        mergedSchool: {
            name: 'Tiểu học An Dương Vương',
            address: 'Thôn Gia Lương, Xã Việt Hùng, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Dương Thị Lan Phương',
            board: [
                { role: 'HT TH An Dương Vương:', name: 'Cô Dương Thị Lan Phương' },
                { role: 'Phó HT TH An Dương Vương:', name: 'Cô Lê Hồng Vân' },
                { role: 'Phó HT TH An Dương Vương:', name: 'Cô Ngô Thị Mai' }
            ],
            classes: 28,
            students: 1009,
            ratio: '36.0 HS/lớp (Trường Chất lượng cao)',
            photo: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/th-an-duong-vuong',
            lat: 21.1420,
            lng: 105.8690
        },
        components: [
            {
                name: 'Trường Tiểu học An Dương Vương (Giữ nguyên quy mô)',
                address: 'Thôn Gia Lương, Xã Việt Hùng, Đông Anh',
                principal: 'Thầy Phạm Văn Hùng',
                phone: '',
                classes: 28,
                students: 1009,
                lat: 21.1420,
                lng: 105.8690,
                photo: 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '0 km',
        durationText: 'Giữ nguyên vị trí'
    },

    // 8. TH Đông Hội
    'th-dong-hoi': {
        mergedSchool: {
            name: 'Tiểu học Đông Hội',
            address: 'Xã Đông Hội, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Phạm Thị Tân Trang',
            board: [
                { role: 'HT TH Ngô Tất Tố:', name: 'Cô Phạm Thị Tân Trang' },
                { role: 'Phó HT TH Đông Hội:', name: 'Cô Nguyễn Thị Hạnh' },
                { role: 'HT TH Xuân Canh:', name: 'Cô Lưu Thị Thu Hồng' }
            ],
            classes: 55,
            students: 2426,
            ratio: '44.1 HS/lớp',
            photo: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/th-dong-hoi',
            lat: 21.0930,
            lng: 105.8680
        },
        components: [
            {
                name: 'Trường Tiểu học Đông Hội (Cũ)',
                address: 'Thôn Lại Đà, Xã Đông Hội, Đông Anh',
                principal: 'Thầy Lê Minh Tuấn',
                phone: '',
                classes: 38,
                students: 1887,
                lat: 21.0910,
                lng: 105.8650,
                photo: 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường Tiểu học Xuân Canh',
                address: 'Thôn Xuân Canh, Xã Xuân Canh, Đông Anh',
                principal: 'Cô Đặng Thị Hà',
                phone: '',
                classes: 17,
                students: 539,
                lat: 21.0955,
                lng: 105.8710,
                photo: 'https://images.unsplash.com/photo-1564981797816-1043664bf78d?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '2.0 km',
        durationText: '5 phút'
    },

    // 9. TH Việt Hùng
    'th-viet-hung': {
        mergedSchool: {
            name: 'Tiểu học Việt Hùng',
            address: 'Thôn Việt Hùng, Xã Việt Hùng, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Đỗ Thị Kim Loan',
            board: [
                { role: 'HT TH Uy Nỗ:', name: 'Cô Đỗ Thị Kim Loan' },
                { role: 'HT TH Việt Hùng 2:', name: 'Cô Hữu Thị Như Quỳnh' },
                { role: 'Phó HT TH Việt Hùng:', name: 'Cô Đào Mỹ Lệ Hằng' }
            ],
            classes: 40,
            students: 1237,
            ratio: '30.9 HS/lớp',
            photo: 'https://images.unsplash.com/photo-1564981797816-1043664bf78d?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/th-viet-hung',
            lat: 21.1450,
            lng: 105.8780
        },
        components: [
            {
                name: 'Trường Tiểu học Việt Hùng ',
                address: 'Khu 1, Xã Việt Hùng, Đông Anh',
                principal: 'Thầy Trịnh Văn Nam',
                phone: '',
                classes: 21,
                students: 667,
                lat: 21.1435,
                lng: 105.8755,
                photo: 'https://images.unsplash.com/photo-1564981797816-1043664bf78d?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường Tiểu học Việt Hùng 2',
                address: 'Khu 2, Xã Việt Hùng, Đông Anh',
                principal: 'Cô Dương Thị Nga',
                phone: '',
                classes: 19,
                students: 570,
                lat: 21.1468,
                lng: 105.8805,
                photo: 'https://images.unsplash.com/photo-1541829070764-84a7d30dd3f3?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '1.4 km',
        durationText: '4 phút'
    },

    // 10. THCS Nguyễn Huy Tưởng
    'thcs-nguyen-huy-tuong': {
        mergedSchool: {
            name: 'THCS Nguyễn Huy Tưởng',
            address: 'Tổ 4, Thị trấn Đông Anh, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Nguyễn Thị Thu Hà',
            board: [
                { role: 'HT THCS N.Huy Tưởng:', name: 'Cô Nguyễn Thị Thu Hà' },
                { role: 'Phó HT THCS N.Huy Tưởng:', name: 'Cô Nguyễn Thị Mai Lan' },
                { role: 'Phó HT THCS N.Huy Tưởng:', name: 'Cô Nguyễn Thị Kim Hoa' }
            ],
            classes: 30,
            students: 1294,
            ratio: '43.1 HS/lớp (Trường Chất lượng cao)',
            photo: 'https://images.unsplash.com/photo-1541829070764-84a7d30dd3f3?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/thcs-nguyen-huy-tuong',
            lat: 21.1360,
            lng: 105.8450
        },
        components: [
            {
                name: 'Trường THCS Nguyễn Huy Tưởng (Giữ nguyên quy mô)',
                address: 'Tổ 4, Thị trấn Đông Anh, Đông Anh',
                principal: 'Cô Nguyễn Thị Thanh',
                phone: '',
                classes: 30,
                students: 1294,
                lat: 21.1360,
                lng: 105.8450,
                photo: 'https://images.unsplash.com/photo-1541829070764-84a7d30dd3f3?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '0 km',
        durationText: 'Giữ nguyên vị trí'
    },

    // 11. THCS Ngô Quyền
    'thcs-ngo-quyen': {
        mergedSchool: {
            name: 'THCS Ngô Quyền',
            address: 'Thị trấn Đông Anh, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Chử Thị Hồng Yến',
            board: [
                { role: 'HT THCS Ngô Quyền:', name: 'Cô Chử Thị Hồng Yến' },
                { role: 'Phó HT THCS Ngô Quyền:', name: 'Cô Trần Thị Quyên' },
                { role: 'Phó HT THCS Ngô Quyền:', name: 'Cô Đỗ Thị Kim Hòa' }
            ],
            classes: 25,
            students: 996,
            ratio: '39.8 HS/lớp',
            photo: 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/thcs-ngo-quyen',
            lat: 21.1380,
            lng: 105.8420
        },
        components: [
            {
                name: 'Trường THCS Ngô Quyền (Giữ nguyên quy mô)',
                address: 'Thị trấn Đông Anh, Đông Anh',
                principal: 'Thầy Vũ Văn Sang',
                phone: '',
                classes: 25,
                students: 996,
                lat: 21.1380,
                lng: 105.8420,
                photo: 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '0 km',
        durationText: 'Giữ nguyên vị trí'
    },

    // 12. THCS An Dương Vương
    'thcs-an-duong-vuong': {
        mergedSchool: {
            name: 'THCS An Dương Vương',
            address: 'Xã Vân Hà, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Đỗ Thị Thanh Thủy',
            board: [
                { role: 'HT THCS Việt Hùng:', name: 'Cô Đỗ Thị Thanh Thủy' },
                { role: 'HT THCS A.Dương Vương:', name: 'Thầy Ngô Văn Thắng' },
                { role: 'Phó HT THCS Đông Hội:', name: 'Cô Đỗ Thu Phương' }
            ],
            classes: 50,
            students: 2020,
            ratio: '40.4 HS/lớp',
            photo: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/thcs-an-duong-vuong',
            lat: 21.1650,
            lng: 105.8890
        },
        components: [
            {
                name: 'Trường THCS An Dương Vương (Cũ)',
                address: 'Thôn Thiết Bình, Xã Vân Hà, Đông Anh',
                principal: 'Thầy Đỗ Văn Thắng',
                phone: '',
                classes: 19,
                students: 739,
                lat: 21.1630,
                lng: 105.8860,
                photo: 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường THCS Việt Hùng',
                address: 'Thôn Gia Lương, Xã Việt Hùng, Đông Anh',
                principal: 'Cô Hoàng Thi Lý',
                phone: '',
                classes: 31,
                students: 1281,
                lat: 21.1670,
                lng: 105.8920,
                photo: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '2.8 km',
        durationText: '7 phút'
    },

    // 13. THCS Xuân Canh
    'thcs-xuan-canh': {
        mergedSchool: {
            name: 'THCS Xuân Canh',
            address: 'Xã Xuân Canh, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Thầy Nguyễn Đình Diềm',
            board: [
                { role: 'HT THCS Đông Hội:', name: 'Thầy Nguyễn Đình Diềm' },
                { role: 'HT THCS Xuân Canh:', name: 'Thầy Nguyễn Hữu Sính' },
                { role: 'HT THCS Uy Nỗ:', name: 'Thầy Lê Quang Hoa' }
            ],
            classes: 45,
            students: 1609,
            ratio: '35.8 HS/lớp',
            photo: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/thcs-xuan-canh',
            lat: 21.0890,
            lng: 105.8560
        },
        components: [
            {
                name: 'Trường THCS Đông Hội',
                address: 'Thôn Lại Đà, Xã Đông Hội, Đông Anh',
                principal: 'Cô Bùi Thị Hòa',
                phone: '',
                classes: 31,
                students: 1112,
                lat: 21.0910,
                lng: 105.8590,
                photo: 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường THCS Xuân Canh (Cũ)',
                address: 'Thôn Xuân Canh, Xã Xuân Canh, Đông Anh',
                principal: 'Thầy Phạm Văn Bình',
                phone: '',
                classes: 14,
                students: 497,
                lat: 21.0870,
                lng: 105.8530,
                photo: 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '1.9 km',
        durationText: '5 phút'
    },

    // 14. Trường liên cấp Mai Lâm
    'truong-lien-cap-mai-lam': {
        mergedSchool: {
            name: 'Trường liên cấp Mai Lâm',
            address: 'Xã Mai Lâm, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Thầy Hoàng Ngọc Thắng',
            board: [
                { role: 'HT THCS Mai Lâm:', name: 'Thầy Hoàng Ngọc Thắng' },
                { role: 'HT TH Đông Hội:', name: 'Cô Lê Thị Hạnh' },
                { role: 'Phó HT THCS Xuân Canh:', name: 'Cô Hoàng Phương Anh' }
            ],
            classes: 50,
            students: 1640,
            ratio: '32.8 HS/lớp (Tiểu học & THCS)',
            photo: 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/truong-lien-cap-mai-lam',
            lat: 21.1050,
            lng: 105.8800
        },
        components: [
            {
                name: 'Trường Tiểu học Ngô Tất Tố',
                address: 'Thôn Thái Bình, Xã Mai Lâm, Đông Anh',
                principal: 'Thầy Ngô Văn Hùng',
                phone: '',
                classes: 29,
                students: 928,
                lat: 21.1030,
                lng: 105.8770,
                photo: 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường THCS Mai Lâm',
                address: 'Thôn Mai Hiên, Xã Mai Lâm, Đông Anh',
                principal: 'Cô Trần Thị Cúc',
                phone: '',
                classes: 21,
                students: 712,
                lat: 21.1070,
                lng: 105.8830,
                photo: 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '2.2 km',
        durationText: '6 phút'
    },

    // 15. Trường liên cấp Cổ Loa
    'truong-lien-cap-co-loa': {
        mergedSchool: {
            name: 'Trường liên cấp Cổ Loa',
            address: 'Thôn Cổ Loa, Xã Cổ Loa, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Nguyễn Thị Huệ',
            board: [
                { role: 'HT TH Đào Duy Tùng:', name: 'Cô Nguyễn Thị Huệ' },
                { role: 'Phó HT THCS Cổ Loa:', name: 'Cô Đỗ Thị Như Hoa' },
                { role: 'HT TH Cổ Loa:', name: 'Cô Đào Thị Hòa' }
            ],
            classes: 52,
            students: 2094,
            ratio: '40.3 HS/lớp (Tiểu học & THCS)',
            photo: 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/truong-lien-cap-co-loa',
            lat: 21.1380,
            lng: 105.8620
        },
        components: [
            {
                name: 'Trường Tiểu học Cổ Loa',
                address: 'Xóm Chùa, Xã Cổ Loa, Đông Anh',
                principal: 'Thầy Nguyễn Văn Tùng',
                phone: '',
                classes: 30,
                students: 1166,
                lat: 21.1360,
                lng: 105.8590,
                photo: 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường THCS Cổ Loa',
                address: 'Xóm Mới, Xã Cổ Loa, Đông Anh',
                principal: 'Cô Lê Thị Mai',
                phone: '',
                classes: 22,
                students: 928,
                lat: 21.1400,
                lng: 105.8650,
                photo: 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '1.7 km',
        durationText: '5 phút'
    },

    // 16. Trường liên cấp Đào Duy Tùng
    'truong-lien-cap-dao-duy-tung': {
        mergedSchool: {
            name: 'Trường liên cấp Đào Duy Tùng',
            address: 'Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Hồ Thị Ánh',
            board: [
                { role: 'HT THCS Đ.Duy Tùng:', name: 'Cô Hồ Thị Ánh' },
                { role: 'Phó HT THCS Đ.Duy Tùng:', name: 'Cô Nguyễn Thị Thanh Thủy' },
                { role: 'Phó HT TH Đào Duy Tùng:', name: 'Cô Bùi Thị Thúy' }
            ],
            classes: 50,
            students: 1717,
            ratio: '34.3 HS/lớp (Tiểu học & THCS)',
            photo: 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/truong-lien-cap-dao-duy-tung',
            lat: 21.1410,
            lng: 105.8550
        },
        components: [
            {
                name: 'Trường Tiểu học Đào Duy Tùng',
                address: 'Khu A, Đông Anh, Hà Nội',
                principal: 'Cô Nguyễn Thị Loan',
                phone: '',
                classes: 31,
                students: 1016,
                lat: 21.1390,
                lng: 105.8520,
                photo: 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường THCS Đào Duy Tùng',
                address: 'Khu B, Đông Anh, Hà Nội',
                principal: 'Thầy Trịnh Xuân Bách',
                phone: '',
                classes: 19,
                students: 701,
                lat: 21.1430,
                lng: 105.8580,
                photo: 'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '2.1 km',
        durationText: '6 phút'
    },

    // 17. Trường liên cấp Dục Tú
    'truong-lien-cap-duc-tu': {
        mergedSchool: {
            name: 'Trường liên cấp Dục Tú',
            address: 'Thôn Dục Tú, Xã Dục Tú, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Cô Nguyễn Thị Thu Hằng',
            board: [
                { role: 'HT TH Dục Tú:', name: 'Cô Nguyễn Thị Thu Hằng' },
                { role: 'HT THCS Dục Tú:', name: 'Cô Trần Thị Giáng Hương' },
                { role: 'Phó HT TH Cổ Loa:', name: 'Cô Hoàng Thúy Hòa' }
            ],
            classes: 60,
            students: 2298,
            ratio: '38.3 HS/lớp (Tiểu học & THCS)',
            photo: 'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/truong-lien-cap-duc-tu',
            lat: 21.1250,
            lng: 105.8950
        },
        components: [
            {
                name: 'Trường Tiểu học Dục Tú',
                address: 'Thôn Dục Tú 1, Xã Dục Tú, Đông Anh',
                principal: 'Thầy Bùi Văn Thắng',
                phone: '',
                classes: 29,
                students: 1109,
                lat: 21.1230,
                lng: 105.8920,
                photo: 'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường THCS Dục Tú',
                address: 'Thôn Dục Tú 2, Xã Dục Tú, Đông Anh',
                principal: 'Cô Nguyễn Thị Hoa',
                phone: '',
                classes: 31,
                students: 1189,
                lat: 21.1270,
                lng: 105.8980,
                photo: 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '2.4 km',
        durationText: '7 phút'
    },

    // 18. Trường liên cấp Uy Nỗ
    'truong-lien-cap-uy-no': {
        mergedSchool: {
            name: 'Trường liên cấp Uy Nỗ',
            address: 'Thôn Uy Nỗ, Xã Uy Nỗ, Đông Anh, Hà Nội',
            phone: '',
            principal: 'Thầy Nguyễn Quang Anh',
            board: [
                { role: 'HT THCS Cổ Loa:', name: 'Thầy Nguyễn Quang Anh' },
                { role: 'Phó HT THCS Mai Lâm:', name: 'Cô Nguyễn Thiên Hương' },
                { role: 'Phó HT TH Uy Nỗ:', name: 'Cô Phan Thị Thanh Nhàn' }
            ],
            classes: 50,
            students: 1493,
            ratio: '29.9 HS/lớp (Tiểu học & THCS)',
            photo: 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=800&q=80',
            qrCode: 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/truong-lien-cap-uy-no',
            lat: 21.1390,
            lng: 105.8500
        },
        components: [
            {
                name: 'Trường Tiểu học Uy Nỗ',
                address: 'Thôn Uy Nỗ A, Xã Uy Nỗ, Đông Anh',
                principal: 'Cô Đỗ Thị Thanh',
                phone: '',
                classes: 29,
                students: 892,
                lat: 21.1370,
                lng: 105.8475,
                photo: 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=600&q=80'
            },
            {
                name: 'Trường THCS Uy Nỗ',
                address: 'Thôn Uy Nỗ B, Xã Uy Nỗ, Đông Anh',
                principal: 'Thầy Lê Văn Thành',
                phone: '',
                classes: 21,
                students: 601,
                lat: 21.1410,
                lng: 105.8525,
                photo: 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=600&q=80'
            }
        ],
        distanceText: '1.6 km',
        durationText: '5 phút'
    }
};

/**
 * Fallback & Fuzzy Resolver for school story datasets
 */
window.getSchoolStoryData = function(slug) {
    if (!slug) return window.STORYTELLING_SCHOOLS['mn-phuc-loc'];

    if (window.STORYTELLING_SCHOOLS[slug]) {
        return window.STORYTELLING_SCHOOLS[slug];
    }

    // Clean slug for fuzzy matching
    const cleanSlug = slug.toString().toLowerCase()
        .replace(/^(truong-|co-so-|mam-non-|tieu-hoc-|thcs-|thpt-)+/, '')
        .replace(/-pa3.*$/, '')
        .trim();

    for (const key in window.STORYTELLING_SCHOOLS) {
        const cleanKey = key.replace(/^(truong-|co-so-|mam-non-|tieu-hoc-|thcs-|thpt-)+/, '');
        if (cleanSlug === cleanKey || cleanSlug.includes(cleanKey) || cleanKey.includes(cleanSlug)) {
            return window.STORYTELLING_SCHOOLS[key];
        }
    }

    // Default fallback to MN Phúc Lộc
    return window.STORYTELLING_SCHOOLS['mn-phuc-loc'];
};
