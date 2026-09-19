@extends('layouts.app')

@section('title', 'Chính Sách Bảo Mật & Điều Khoản Sử Dụng (EULA) - Đông Anh Social')
@section('meta_description', 'Quy định tiêu chuẩn cộng đồng, chính sách kiểm duyệt 24h, bảo vệ dữ liệu cá nhân và quyền xóa tài khoản của người dùng trên nền tảng Đông Anh Social.')

@section('content')
<div class="legal-page-container" x-data="{ activeTab: 'eula' }">
    <div class="legal-hero">
        <div class="legal-hero-inner">
            <span class="legal-badge">🛡️ Tiêu Chuẩn Cộng Đồng & Bảo Mật Dữ Liệu</span>
            <h1 class="legal-title">Chính Sách Bảo Mật & Điều Khoản Sử Dụng</h1>
            <p class="legal-subtitle">
                Cam kết môi trường mạng văn minh, an toàn, minh bạch theo tiêu chuẩn quốc tế (Apple App Store Guideline 1.2 & 5.1.1, Google Play Policy).
            </p>
            <div class="legal-meta">
                <span>📅 Phiên bản hiệu lực: 2026.1</span>
                <span>•</span>
                <span>🏛️ Cơ quan chủ quản: UBND Xã Đông Anh</span>
                <span>•</span>
                <span>⚡ Thời hạn xử lý vi phạm: Dưới 24 giờ</span>
            </div>
        </div>
    </div>

    <div class="legal-tabs-wrapper">
        <div class="legal-tabs-bar">
            <button type="button" 
                    class="legal-tab-btn" 
                    :class="{ 'active': activeTab === 'eula' }" 
                    @click="activeTab = 'eula'">
                📜 1. Thỏa Thuận Người Dùng & Kiểm Duyệt (EULA)
            </button>
            <button type="button" 
                    class="legal-tab-btn" 
                    :class="{ 'active': activeTab === 'privacy' }" 
                    @click="activeTab === 'privacy' ? null : activeTab = 'privacy'">
                🔒 2. Chính Sách Bảo Mật & Dữ Liệu Cá Nhân
            </button>
        </div>
    </div>

    <div class="legal-content-card">
        <!-- TAB 1: EULA & CONTENT MODERATION -->
        <div x-show="activeTab === 'eula'" x-transition:enter="transition ease-out duration-200">
            <div class="legal-callout-warning">
                <div class="callout-icon">⚠️</div>
                <div>
                    <h3 style="margin: 0 0 6px 0; font-size: 1.05rem; font-weight: 800; color: #b91c1c;">Chính Sách Không Dung Thứ (Zero Tolerance Policy)</h3>
                    <p style="margin: 0; font-size: 0.9rem; color: #7f1d1d; line-height: 1.5;">
                        Đông Anh Social nghiêm cấm tuyệt đối mọi hành vi đăng tải nội dung độc hại, xúc phạm danh dự, kích động bạo lực, chống phá, khiêu dâm, lừa đảo hoặc vi phạm bản quyền. Mọi vi phạm được phát hiện hoặc bị người dùng báo cáo sẽ được đội ngũ kiểm duyệt xử lý và gỡ bỏ trong vòng <strong>24 giờ</strong>.
                    </p>
                </div>
            </div>

            <section class="legal-section">
                <h2>1. Phạm vi áp dụng & Chấp thuận điều khoản</h2>
                <p>
                    Bằng việc truy cập, tải ứng dụng hoặc tạo tài khoản trên Đông Anh Social (bao gồm cả phiên bản Ứng dụng di động iOS/Android và Cổng thông tin Web), bạn xác nhận đã đọc, hiểu rõ và đồng ý vô điều kiện với tất cả các điều khoản được quy định dưới đây.
                </p>
            </section>

            <section class="legal-section">
                <h2>2. Tiêu chuẩn nội dung do người dùng tạo (UGC)</h2>
                <p>Người dùng chịu hoàn toàn trách nhiệm pháp lý đối với mọi nội dung mình đăng tải (bài viết, hình ảnh, video reels, nhận xét, phát sóng trực tiếp). Tuyệt đối nghiêm cấm các hành vi sau:</p>
                <ul class="legal-list">
                    <li><strong>Nội dung thù địch, xúc phạm:</strong> Tuyên truyền thông tin sai sự thật, vu khống, quấy rối, đe dọa, xúc phạm danh dự của bất kỳ cá nhân, tổ chức nào.</li>
                    <li><strong>Nội dung đồi trụy, khiêu dâm:</strong> Nghiêm cấm chia sẻ hình ảnh, video mang tính chất khiêu dâm, dung tục 18+, hoặc có liên quan đến lạm dụng trẻ vị thành niên.</li>
                    <li><strong>Bạo lực & Vi phạm pháp luật:</strong> Kích động bạo lực, buôn bán vũ khí, chất cấm, cờ bạc trực tuyến, cá độ bóng đá, thông tin chống phá trật tự an toàn xã hội.</li>
                    <li><strong>Bản quyền & Giả mạo:</strong> Sử dụng trái phép hình ảnh thương hiệu, sao chép tác phẩm sở hữu trí tuệ mà không có sự đồng ý của tác giả, mạo danh cơ quan nhà nước hoặc tiểu thương khác.</li>
                </ul>
            </section>

            <section class="legal-section">
                <h2>3. Cơ chế Kiểm duyệt & Cam kết xử lý vi phạm trong 24 giờ</h2>
                <p>
                    Để bảo vệ cộng đồng người dùng và đáp ứng chuẩn mực phân phối ứng dụng của Apple (Guideline 1.2) và Google Play:
                </p>
                <div class="legal-grid-features">
                    <div class="legal-feature-box">
                        <div class="feat-icon">🚩</div>
                        <h4>Công Cụ Báo Cáo (Report)</h4>
                        <p>Mỗi bài viết, bình luận hoặc hồ sơ người dùng đều có nút menu 3 chấm với tùy chọn "Báo cáo vi phạm". Báo cáo được tự động chuyển ngay vào Hàng đợi kiểm duyệt (Moderation Queue).</p>
                    </div>
                    <div class="legal-feature-box">
                        <div class="feat-icon">🚫</div>
                        <h4>Công Cụ Chặn Tức Thì (Block)</h4>
                        <p>Người dùng có toàn quyền chặn hoặc ẩn bất kỳ tác giả/nội dung nào. Sau khi chặn, toàn bộ bài viết của người đó sẽ biến mất ngay lập tức khỏi nguồn cấp dữ liệu của bạn.</p>
                    </div>
                    <div class="legal-feature-box">
                        <div class="feat-icon">⏱️</div>
                        <h4>Cam Kết SLA Dưới 24 Giờ</h4>
                        <p>Đội ngũ kiểm duyệt trực 24/7 có nghĩa vụ rà soát, đánh giá và thực hiện chế tài (Gỡ bài viết vi phạm, Khóa tài khoản tác giả có thời hạn hoặc vĩnh viễn) trong vòng không quá 24 giờ kể từ khi nhận vé.</p>
                    </div>
                </div>
            </section>

            <section class="legal-section">
                <h2>4. Các hình thức xử lý tài khoản vi phạm</h2>
                <p>Tùy theo mức độ và tần suất vi phạm, Ban Quản trị hệ thống áp dụng các chế tài sau:</p>
                <ul class="legal-list">
                    <li><strong>Gỡ bỏ nội dung:</strong> Xóa vĩnh viễn bài viết, hình ảnh, video vi phạm khỏi hệ thống.</li>
                    <li><strong>Tạm khóa 24 giờ hoặc 3 ngày:</strong> Đối với các lỗi vi phạm lần đầu, bình luận rác hoặc gây mất trật tự nhẹ.</li>
                    <li><strong>Tạm khóa 7 ngày đến 30 ngày:</strong> Tái phạm hoặc có hành vi quấy rối, phát ngôn xúc phạm nghiêm trọng.</li>
                    <li><strong>Khóa vĩnh viễn & Chuyển cơ quan chức năng:</strong> Đối với các hành vi phát tán văn hóa phẩm đồi trụy, lừa đảo tài chính, chống phá nhà nước hoặc vi phạm hình sự.</li>
                </ul>
            </section>
        </div>

        <!-- TAB 2: PRIVACY POLICY -->
        <div x-show="activeTab === 'privacy'" x-transition:enter="transition ease-out duration-200" style="display: none;">
            <div class="legal-callout-info">
                <div class="callout-icon">🔒</div>
                <div>
                    <h3 style="margin: 0 0 6px 0; font-size: 1.05rem; font-weight: 800; color: #0369a1;">Quyền Riêng Tư Của Bạn Là Ưu Tiên Hàng Đầu</h3>
                    <p style="margin: 0; font-size: 0.9rem; color: #0c4a6e; line-height: 1.5;">
                        Đông Anh Social tuân thủ nghiêm ngặt các quy định về bảo vệ dữ liệu cá nhân theo Nghị định 13/2023/NĐ-CP của Chính phủ Việt Nam và Apple App Store Review Guideline 5.1.1. Chúng tôi chỉ thu thập các dữ liệu thực sự cần thiết để vận hành dịch vụ du lịch & bản đồ số.
                    </p>
                </div>
            </div>

            <section class="legal-section">
                <h2>1. Dữ liệu chúng tôi thu thập & Mục đích sử dụng</h2>
                <div class="legal-table-wrapper">
                    <table class="legal-data-table">
                        <thead>
                            <tr>
                                <th>Loại Dữ Liệu</th>
                                <th>Mục Đích Sử Dụng</th>
                                <th>Tính Chất Bắt Buộc</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Thông tin đăng ký</strong> (Họ tên, SĐT, Email, Tên đăng nhập)</td>
                                <td>Tạo lập tài khoản thành viên, xác minh danh tính người bán hàng/tiểu thương, liên lạc hỗ trợ đơn hàng.</td>
                                <td>Bắt buộc khi đăng ký tài khoản</td>
                            </tr>
                            <tr>
                                <td><strong>Vị trí địa lý (GPS)</strong></td>
                                <td>Hiển thị các quán ăn, trường học, trạm y tế lân cận; định tuyến chỉ đường trải nghiệm Food Tour tại Đông Anh.</td>
                                <td>Tùy chọn (Người dùng có thể tắt trong cài đặt thiết bị)</td>
                            </tr>
                            <tr>
                                <td><strong>Hình ảnh & Video tải lên</strong></td>
                                <td>Đăng bài viết đánh giá ẩm thực, cập nhật ảnh đại diện, ảnh gian hàng sản phẩm OCOP hoặc góc trải nghiệm văn hóa.</td>
                                <td>Tùy chọn khi người dùng chủ động tải lên</td>
                            </tr>
                            <tr>
                                <td><strong>Nhật ký phiên & Cookie</strong></td>
                                <td>Lưu trữ trạng thái đăng nhập, giỏ hàng trực tuyến và cài đặt giao diện người dùng.</td>
                                <td>Cần thiết cho vận hành kỹ thuật</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="legal-section">
                <h2>2. Cam kết không chia sẻ & Bán dữ liệu</h2>
                <p>
                    Đông Anh Social cam kết <strong>không bao giờ bán, cho thuê hoặc chia sẻ</strong> thông tin cá nhân của người dùng cho bất kỳ bên thứ ba nào vì mục đích thương mại hoặc quảng cáo theo dõi. Dữ liệu chỉ được cung cấp cho cơ quan có thẩm quyền khi có văn bản yêu cầu chính thức theo quy định của pháp luật Việt Nam.
                </p>
            </section>

            <section class="legal-section">
                <h2>3. Quyền Xóa Tài Khoản & Hủy Dữ Liệu Cá Nhân (Apple Guideline 5.1.1)</h2>
                <p>
                    Người dùng có toàn quyền kiểm soát dữ liệu cá nhân của mình bất kỳ lúc nào. Bạn có thể tự thực hiện việc xóa tài khoản và xóa vĩnh viễn toàn bộ dữ liệu liên quan:
                </p>
                <div class="legal-callout-danger">
                    <div class="callout-icon">🗑️</div>
                    <div>
                        <h4 style="margin: 0 0 4px 0; font-size: 1rem; font-weight: 800; color: #991b1b;">Tự xóa tài khoản trực tiếp trên ứng dụng & website:</h4>
                        <p style="margin: 0; font-size: 0.88rem; color: #7f1d1d;">
                            Đăng nhập tài khoản > Truy cập <strong>Trang cá nhân (Profile)</strong> > Chọn <strong>"Xóa tài khoản & Dữ liệu"</strong> > Xác nhận. Hệ thống sẽ lập tức gỡ bỏ toàn bộ bài viết, ảnh, check-in, lịch sử trò chuyện và hủy bỏ mã định danh tài khoản của bạn khỏi cơ sở dữ liệu vĩnh viễn.
                        </p>
                    </div>
                </div>
            </section>

            <section class="legal-section">
                <h2>4. Kênh tiếp nhận & Thông tin liên hệ Bảo Mật Dữ Liệu (DPO)</h2>
                <p>
                    Nếu bạn có bất kỳ câu hỏi nào về chính sách này, yêu cầu trích xuất dữ liệu hoặc báo cáo sự cố an toàn thông tin, vui lòng liên hệ trực tiếp:
                </p>
                <div class="legal-contact-card">
                    <p><strong>Cơ quan chủ quản:</strong> Uỷ ban nhân dân xã Đông Anh - Hà Nội</p>
                    <p><strong>Bộ phận phụ trách:</strong> Ban Biên Tập & Quản Trị Hệ Thống Số Đông Anh Social</p>
                    <p>📍 <strong>Địa chỉ:</strong> Số 66 đường Cao Lỗ, xã Đông Anh, thành phố Hà Nội</p>
                    <p>📞 <strong>Đường dây nóng kiểm duyệt & hỗ trợ:</strong> <a href="tel:02439652973" style="color: #0284c7; font-weight: 700;">0243.965.2973</a></p>
                    <p>✉️ <strong>Email an toàn thông tin:</strong> <a href="mailto:banbientap@donganh.hanoi.gov.vn" style="color: #0284c7; font-weight: 700;">banbientap@donganh.hanoi.gov.vn</a></p>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
.legal-page-container {
    max-width: 1080px;
    margin: 32px auto 80px auto;
    padding: 0 20px;
    font-family: 'Plus Jakarta Sans', 'Be Vietnam Pro', sans-serif;
    color: #1e293b;
}

.legal-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0369a1 100%);
    border-radius: 28px;
    padding: 44px 36px;
    color: #ffffff;
    box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.25);
    margin-bottom: 28px;
}

.legal-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 16px;
    background: rgba(14, 165, 233, 0.2);
    border: 1px solid rgba(56, 189, 248, 0.4);
    border-radius: 100px;
    color: #38bdf8;
    font-size: 0.8rem;
    font-weight: 800;
    margin-bottom: 16px;
}

.legal-title {
    font-size: 2.1rem;
    font-weight: 900;
    line-height: 1.25;
    margin: 0 0 12px 0;
    letter-spacing: -0.02em;
}

.legal-subtitle {
    font-size: 1.05rem;
    color: #cbd5e1;
    line-height: 1.6;
    margin: 0 0 20px 0;
    max-width: 780px;
}

.legal-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.85rem;
    color: #94a3b8;
    flex-wrap: wrap;
}

.legal-tabs-wrapper {
    margin-bottom: 24px;
}

.legal-tabs-bar {
    display: flex;
    gap: 12px;
    background: #e2e8f0;
    padding: 6px;
    border-radius: 18px;
}

.legal-tab-btn {
    flex: 1;
    padding: 14px 20px;
    border: none;
    border-radius: 14px;
    font-size: 0.95rem;
    font-weight: 700;
    color: #475569;
    background: transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: center;
}

.legal-tab-btn:hover {
    color: #0f172a;
}

.legal-tab-btn.active {
    background: #ffffff;
    color: #0284c7;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
}

.legal-content-card {
    background: #ffffff;
    border-radius: 28px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
    padding: 44px;
}

.legal-callout-warning {
    background: #fef2f2;
    border: 1.5px solid #fecaca;
    border-radius: 20px;
    padding: 20px;
    display: flex;
    gap: 16px;
    align-items: flex-start;
    margin-bottom: 32px;
}

.legal-callout-info {
    background: #f0f9ff;
    border: 1.5px solid #bae6fd;
    border-radius: 20px;
    padding: 20px;
    display: flex;
    gap: 16px;
    align-items: flex-start;
    margin-bottom: 32px;
}

.legal-callout-danger {
    background: #fff1f2;
    border: 1.5px solid #fecdd3;
    border-radius: 18px;
    padding: 18px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
    margin-top: 14px;
}

.callout-icon {
    font-size: 1.8rem;
    line-height: 1;
}

.legal-section {
    margin-bottom: 36px;
}

.legal-section h2 {
    font-size: 1.25rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 12px;
}

.legal-section p {
    font-size: 0.96rem;
    color: #334155;
    line-height: 1.7;
    margin-bottom: 14px;
}

.legal-list {
    padding-left: 20px;
    margin-bottom: 16px;
}

.legal-list li {
    font-size: 0.95rem;
    color: #334155;
    line-height: 1.7;
    margin-bottom: 8px;
}

.legal-grid-features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-top: 18px;
}

@media (max-width: 860px) {
    .legal-grid-features {
        grid-template-columns: 1fr;
    }
    .legal-content-card {
        padding: 24px;
    }
    .legal-hero {
        padding: 30px 20px;
    }
    .legal-title {
        font-size: 1.6rem;
    }
    .legal-tabs-bar {
        flex-direction: column;
    }
}

.legal-feature-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 22px;
}

.legal-feature-box .feat-icon {
    font-size: 1.8rem;
    margin-bottom: 10px;
}

.legal-feature-box h4 {
    font-size: 1rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 8px 0;
}

.legal-feature-box p {
    font-size: 0.86rem;
    color: #64748b;
    line-height: 1.5;
    margin: 0;
}

.legal-table-wrapper {
    overflow-x: auto;
    margin: 18px 0;
}

.legal-data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.92rem;
}

.legal-data-table th {
    background: #f1f5f9;
    padding: 12px 16px;
    text-align: left;
    font-weight: 800;
    color: #0f172a;
    border-bottom: 2px solid #cbd5e1;
}

.legal-data-table td {
    padding: 14px 16px;
    border-bottom: 1px solid #e2e8f0;
    color: #334155;
    vertical-align: top;
    line-height: 1.5;
}

.legal-contact-card {
    background: #f8fafc;
    border-left: 4px solid #0284c7;
    border-radius: 0 16px 16px 0;
    padding: 20px 24px;
    margin-top: 14px;
}

.legal-contact-card p {
    margin: 0 0 8px 0;
    font-size: 0.92rem;
    color: #334155;
}

.legal-contact-card p:last-child {
    margin-bottom: 0;
}
</style>
@endsection
