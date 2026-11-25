SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS sachviet
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE sachviet;

DROP TABLE IF EXISTS yeu_thich, gio_hang, danh_gia, chi_tiet_don_hang,
                     don_hang, nguoi_dung, sach, kho, the_loai;

CREATE TABLE the_loai (
  id          INT          NOT NULL AUTO_INCREMENT COMMENT 'Mã thể loại (tự tăng)',
  ten         VARCHAR(100) NOT NULL                COMMENT 'Tên thể loại, VD: Thơ Nôm',
  mau         CHAR(7)      NOT NULL DEFAULT '#7A6A4F' COMMENT 'Màu bìa sách, dạng #RRGGBB',
  bieu_tuong  VARCHAR(16)  NOT NULL DEFAULT '📚'    COMMENT 'Emoji ở mục Danh mục nổi bật',
  PRIMARY KEY (id),
  UNIQUE KEY uq_the_loai_ten (ten),
  CONSTRAINT chk_the_loai_mau CHECK (mau REGEXP '^#[0-9A-Fa-f]{6}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Thể loại sách';

CREATE TABLE kho (
  id   INT          NOT NULL AUTO_INCREMENT COMMENT 'Mã kho (tự tăng)',
  ten  VARCHAR(100) NOT NULL                COMMENT 'Tên kho, VD: Hà Nội',
  PRIMARY KEY (id),
  UNIQUE KEY uq_kho_ten (ten)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Kho hàng';

CREATE TABLE sach (
  id               INT          NOT NULL AUTO_INCREMENT COMMENT 'Mã sách — dùng trong link sach.php?id=...',
  ten              VARCHAR(200) NOT NULL                COMMENT 'Tên sách',
  tac_gia          VARCHAR(150) NOT NULL DEFAULT 'Khuyết danh' COMMENT 'Tác giả',
  nam_sang_tac     VARCHAR(50)  NULL                    COMMENT 'Năm/thế kỷ sáng tác, VD: 1820 hoặc Thế kỷ XVIII',
  the_loai_id      INT          NOT NULL                COMMENT 'Thể loại → the_loai.id',
  kho_id           INT          NOT NULL                COMMENT 'Kho chứa sách → kho.id',
  gia              INT          NOT NULL                COMMENT 'Giá bán hiện tại (đồng)',
  gia_cu           INT          NULL                    COMMENT 'Giá bìa gốc (đồng); NULL = không giảm giá',
  so_luong         INT          NOT NULL DEFAULT 0      COMMENT 'Số cuốn còn trong kho (0 = hết hàng)',
  mo_ta            TEXT         NULL                    COMMENT 'Mô tả ngắn ở trang chi tiết',
  trich_doan       TEXT         NULL                    COMMENT 'Đoạn đọc thử; các đoạn cách nhau 1 dòng trống',
  link_wikisource  VARCHAR(255) NULL                    COMMENT 'Link đọc toàn văn trên Wikisource',
  hien_thi         TINYINT(1)   NOT NULL DEFAULT 1      COMMENT '1 = đang bán, 0 = ẩn khỏi cửa hàng',
  ngay_them        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày đưa lên cửa hàng',
  PRIMARY KEY (id),
  KEY idx_sach_the_loai (the_loai_id),
  KEY idx_sach_kho (kho_id),
  CONSTRAINT fk_sach_the_loai FOREIGN KEY (the_loai_id) REFERENCES the_loai (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_sach_kho FOREIGN KEY (kho_id) REFERENCES kho (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_sach_gia      CHECK (gia >= 0),
  CONSTRAINT chk_sach_gia_cu   CHECK (gia_cu IS NULL OR gia_cu >= gia),
  CONSTRAINT chk_sach_so_luong CHECK (so_luong >= 0),
  CONSTRAINT chk_sach_hien_thi CHECK (hien_thi IN (0, 1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Sách đang bán';

CREATE TABLE nguoi_dung (
  id             INT          NOT NULL AUTO_INCREMENT COMMENT 'Mã người dùng (tự tăng)',
  ho_ten         VARCHAR(100) NOT NULL                COMMENT 'Họ và tên',
  email          VARCHAR(150) NOT NULL                COMMENT 'Email đăng nhập — không được trùng',
  mat_khau       VARCHAR(255) NOT NULL                COMMENT 'Mật khẩu ĐÃ BĂM bằng password_hash(), không lưu mật khẩu thật',
  so_dien_thoai  VARCHAR(20)  NULL                    COMMENT 'Số điện thoại',
  ngay_sinh      DATE         NULL                    COMMENT 'Ngày sinh (YYYY-MM-DD)',
  dia_chi        VARCHAR(255) NULL                    COMMENT 'Địa chỉ nhận hàng mặc định',
  vai_tro        ENUM('admin','staff','user') NOT NULL DEFAULT 'user' COMMENT 'Quyền: admin / staff / user',
  trang_thai     ENUM('hoat_dong','tam_khoa','cho_duyet') NOT NULL DEFAULT 'hoat_dong' COMMENT 'Trạng thái tài khoản',
  ngay_tao       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày đăng ký',
  PRIMARY KEY (id),
  UNIQUE KEY uq_nguoi_dung_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tài khoản người dùng';

CREATE TABLE don_hang (
  id              INT          NOT NULL AUTO_INCREMENT COMMENT 'Mã đơn (hiển thị #DH001, #DH002...)',
  nguoi_dung_id   INT          NOT NULL                COMMENT 'Người đặt → nguoi_dung.id',
  ho_ten_nhan     VARCHAR(100) NOT NULL                COMMENT 'Tên người nhận hàng',
  so_dien_thoai   VARCHAR(20)  NOT NULL                COMMENT 'SĐT người nhận',
  dia_chi_giao    VARCHAR(255) NOT NULL                COMMENT 'Địa chỉ giao hàng',
  ghi_chu         VARCHAR(255) NULL                    COMMENT 'Ghi chú cho người giao hàng',
  phuong_thuc_tt  ENUM('cod','banking','ewallet') NOT NULL DEFAULT 'cod' COMMENT 'Thanh toán: COD / chuyển khoản / ví điện tử',
  phi_van_chuyen  INT          NOT NULL DEFAULT 30000  COMMENT 'Phí ship (đồng); 0 nếu tiền hàng từ 300.000₫',
  trang_thai      ENUM('cho_xac_nhan','dang_giao','da_giao','da_huy') NOT NULL DEFAULT 'cho_xac_nhan' COMMENT 'Trạng thái đơn',
  ngay_dat        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm đặt hàng',
  PRIMARY KEY (id),
  KEY idx_don_hang_nguoi_dung (nguoi_dung_id),
  CONSTRAINT fk_don_hang_nguoi_dung FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT chk_don_hang_phi CHECK (phi_van_chuyen >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Đơn hàng';

CREATE TABLE chi_tiet_don_hang (
  don_hang_id  INT NOT NULL           COMMENT 'Thuộc đơn nào → don_hang.id',
  sach_id      INT NOT NULL           COMMENT 'Sách nào → sach.id',
  so_luong     INT NOT NULL DEFAULT 1 COMMENT 'Số cuốn',
  don_gia      INT NOT NULL           COMMENT 'Giá 1 cuốn LÚC ĐẶT (không đổi khi sau này sửa giá sách)',
  PRIMARY KEY (don_hang_id, sach_id),
  KEY idx_ctdh_sach (sach_id),
  CONSTRAINT fk_ctdh_don_hang FOREIGN KEY (don_hang_id) REFERENCES don_hang (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_ctdh_sach FOREIGN KEY (sach_id) REFERENCES sach (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT chk_ctdh_so_luong CHECK (so_luong > 0),
  CONSTRAINT chk_ctdh_don_gia  CHECK (don_gia >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Các cuốn sách trong từng đơn hàng';

CREATE TABLE danh_gia (
  id             INT      NOT NULL AUTO_INCREMENT COMMENT 'Mã đánh giá (tự tăng)',
  sach_id        INT      NOT NULL                COMMENT 'Đánh giá sách nào → sach.id',
  nguoi_dung_id  INT      NOT NULL                COMMENT 'Ai viết → nguoi_dung.id',
  so_sao         TINYINT  NOT NULL                COMMENT 'Từ 1 đến 5',
  noi_dung       TEXT     NULL                    COMMENT 'Lời nhận xét',
  ngay_danh_gia  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm viết',
  PRIMARY KEY (id),
  UNIQUE KEY uq_danh_gia_sach_nguoi (sach_id, nguoi_dung_id),
  KEY idx_danh_gia_nguoi_dung (nguoi_dung_id),
  CONSTRAINT fk_danh_gia_sach FOREIGN KEY (sach_id) REFERENCES sach (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_danh_gia_nguoi_dung FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT chk_danh_gia_so_sao CHECK (so_sao BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Đánh giá sách của khách hàng';

CREATE TABLE gio_hang (
  nguoi_dung_id  INT      NOT NULL           COMMENT 'Giỏ của ai → nguoi_dung.id',
  sach_id        INT      NOT NULL           COMMENT 'Sách nào → sach.id',
  so_luong       INT      NOT NULL DEFAULT 1 COMMENT 'Số cuốn',
  ngay_them      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Lúc thêm vào giỏ',
  PRIMARY KEY (nguoi_dung_id, sach_id),
  KEY idx_gio_hang_sach (sach_id),
  CONSTRAINT fk_gio_hang_nguoi_dung FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_gio_hang_sach FOREIGN KEY (sach_id) REFERENCES sach (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT chk_gio_hang_so_luong CHECK (so_luong > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Giỏ hàng của từng người dùng';

CREATE TABLE yeu_thich (
  nguoi_dung_id  INT      NOT NULL COMMENT 'Ai thích → nguoi_dung.id',
  sach_id        INT      NOT NULL COMMENT 'Thích sách nào → sach.id',
  ngay_them      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Lúc bấm yêu thích',
  PRIMARY KEY (nguoi_dung_id, sach_id),
  KEY idx_yeu_thich_sach (sach_id),
  CONSTRAINT fk_yeu_thich_nguoi_dung FOREIGN KEY (nguoi_dung_id) REFERENCES nguoi_dung (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_yeu_thich_sach FOREIGN KEY (sach_id) REFERENCES sach (id)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Sách yêu thích của từng người dùng';

INSERT INTO the_loai (id, ten, mau, bieu_tuong) VALUES
  (1, 'Thơ Nôm', '#A8551E', '🪶'),
  (2, 'Truyện ngắn', '#3F7D58', '📄'),
  (3, 'Tiểu thuyết', '#8B5E9E', '📚'),
  (4, 'Văn nghị luận', '#B3453A', '⚔️'),
  (5, 'Thơ', '#C9A227', '🌿'),
  (6, 'Truyện truyền kỳ', '#4A7FA5', '🏮'),
  (7, 'Lịch sử', '#7A6A4F', '🏛️');

INSERT INTO kho (id, ten) VALUES
  (1, 'Hà Nội'),
  (2, 'TP. Hồ Chí Minh'),
  (3, 'Đà Nẵng');

INSERT INTO sach (id, ten, tac_gia, nam_sang_tac, the_loai_id, kho_id, gia, gia_cu, so_luong,
                  mo_ta, trich_doan, link_wikisource, hien_thi, ngay_them) VALUES
  (1, 'Truyện Kiều', 'Nguyễn Du', '1820', 1, 1, 125000, 156000, 27,
   'Kiệt tác gồm 3254 câu thơ lục bát, kể mười lăm năm lưu lạc của Thúy Kiều. Được xem là đỉnh cao của văn học trung đại Việt Nam và là tác phẩm đưa Nguyễn Du trở thành Danh nhân văn hóa thế giới.',
   'Trăm năm trong cõi người ta,
Chữ tài chữ mệnh khéo là ghét nhau.

Trải qua một cuộc bể dâu,
Những điều trông thấy mà đau đớn lòng.

Lạ gì bỉ sắc tư phong,
Trời xanh quen thói má hồng đánh ghen.

Cảo thơm lần giở trước đèn,
Phong tình cổ lục còn truyền sử xanh.',
   'https://vi.wikisource.org/wiki/Truyện_Kiều', 1, '2026-01-05 08:00:00'),
  (2, 'Chí Phèo', 'Nam Cao', '1941', 2, 1, 68000, 85000, 34,
   'Truyện ngắn về bi kịch của người nông dân bị xã hội đẩy vào con đường tha hoá, rồi bị chặn mất lối quay về làm người lương thiện.',
   'Hắn vừa đi vừa chửi. Bao giờ cũng thế, cứ rượu xong là hắn chửi. Bắt đầu chửi trời. Có hề gì? Trời có của riêng nhà nào?

Rồi hắn chửi đời. Thế cũng chẳng sao: đời là tất cả nhưng chẳng là ai. Tức mình hắn chửi ngay tất cả làng Vũ Đại.

Nhưng cả làng Vũ Đại ai cũng nhủ: "Chắc nó trừ mình ra!" Không ai lên tiếng cả.',
   'https://vi.wikisource.org/wiki/Chí_Phèo', 1, '2026-01-05 08:05:00'),
  (3, 'Lão Hạc', 'Nam Cao', '1943', 2, 1, 62000, 77000, 41,
   'Câu chuyện về một lão nông nghèo bán con chó Vàng rồi tự kết liễu đời mình để giữ trọn mảnh vườn cho con trai.',
   NULL,
   'https://vi.wikisource.org/wiki/Lão_Hạc', 1, '2026-01-05 08:10:00'),
  (4, 'Tắt đèn', 'Ngô Tất Tố', '1937', 3, 1, 88000, 110000, 48,
   'Tiểu thuyết về chị Dậu và cảnh sưu thuế ngột ngạt ở nông thôn Bắc Bộ trước Cách mạng tháng Tám.',
   NULL,
   'https://vi.wikisource.org/wiki/Tắt_đèn', 1, '2026-01-05 08:15:00'),
  (5, 'Số đỏ', 'Vũ Trọng Phụng', '1936', 3, 2, 95000, 118000, 55,
   'Tiểu thuyết trào phúng về Xuân Tóc Đỏ — kẻ nhờ may mắn và thói học đòi của xã hội thượng lưu mà leo lên đỉnh danh vọng.',
   NULL,
   'https://vi.wikisource.org/wiki/Số_đỏ', 1, '2026-01-05 08:20:00'),
  (6, 'Giông tố', 'Vũ Trọng Phụng', '1936', 3, 2, 98000, 122000, 62,
   'Bức tranh xã hội thành thị những năm 1930 với Nghị Hách — điển hình cho thế lực tiền bạc chà đạp lên số phận con người.',
   NULL,
   'https://vi.wikisource.org/wiki/Giông_tố', 1, '2026-01-05 08:25:00'),
  (7, 'Tố Tâm', 'Hoàng Ngọc Phách', '1925', 3, 3, 78000, 97000, 69,
   'Một trong những tiểu thuyết hiện đại đầu tiên của Việt Nam, kể mối tình dang dở giữa Tố Tâm và Đạm Thủy.',
   NULL,
   'https://vi.wikisource.org/wiki/Tố_Tâm', 1, '2026-01-05 08:30:00'),
  (8, 'Hoàng Lê nhất thống chí', 'Ngô gia văn phái', 'Thế kỷ XVIII', 3, 1, 145000, 181000, 76,
   'Tiểu thuyết chương hồi viết bằng chữ Hán, ghi lại giai đoạn cuối Lê — đầu Nguyễn với nhiều biến động lịch sử lớn.',
   NULL,
   'https://vi.wikisource.org/wiki/Hoàng_Lê_nhất_thống_chí', 1, '2026-01-05 08:35:00'),
  (9, 'Bình Ngô đại cáo', 'Nguyễn Trãi', '1428', 4, 1, 55000, 68000, 83,
   'Áng "thiên cổ hùng văn" tuyên bố chấm dứt hai mươi năm đô hộ của nhà Minh, khẳng định nền độc lập và chủ quyền của Đại Việt.',
   'Việc nhân nghĩa cốt ở yên dân,
Quân điếu phạt trước lo trừ bạo.

Như nước Đại Việt ta từ trước,
Vốn xưng nền văn hiến đã lâu.

Núi sông bờ cõi đã chia,
Phong tục Bắc Nam cũng khác.',
   'https://vi.wikisource.org/wiki/Bình_Ngô_đại_cáo', 1, '2026-01-05 08:40:00'),
  (10, 'Hịch tướng sĩ', 'Trần Quốc Tuấn', '1284', 4, 1, 52000, 65000, 90,
   'Bài hịch viết trước cuộc kháng chiến chống Nguyên Mông lần thứ hai, khích lệ tướng sĩ rèn luyện võ nghệ và nêu cao lòng yêu nước.',
   'Ta đây, ngày thì quên ăn, đêm thì quên ngủ, ruột đau như cắt, nước mắt đầm đìa, chỉ căm tức rằng chưa được xả thịt lột da của quân giặc, dẫu thân này phơi ngoài nội cỏ, xác này gói trong da ngựa, thì cũng đành lòng.',
   'https://vi.wikisource.org/wiki/Hịch_tướng_sĩ', 1, '2026-01-05 08:45:00'),
  (11, 'Chiếu dời đô', 'Lý Công Uẩn', '1010', 4, 1, 48000, 60000, 97,
   'Bài chiếu công bố việc dời kinh đô từ Hoa Lư ra thành Đại La, mở đầu cho nghìn năm Thăng Long — Hà Nội.',
   NULL,
   'https://vi.wikisource.org/wiki/Chiếu_dời_đô', 1, '2026-01-05 08:50:00'),
  (12, 'Lục Vân Tiên', 'Nguyễn Đình Chiểu', '1864', 1, 2, 110000, 137000, 104,
   'Truyện thơ Nôm đề cao đạo lý làm người, nghĩa khí và lòng trung hiếu — tác phẩm được truyền tụng rộng rãi ở Nam Bộ.',
   NULL,
   'https://vi.wikisource.org/wiki/Lục_Vân_Tiên', 1, '2026-01-05 08:55:00'),
  (13, 'Chinh phụ ngâm', 'Đặng Trần Côn — Đoàn Thị Điểm diễn Nôm', 'Thế kỷ XVIII', 1, 1, 85000, 106000, 111,
   'Khúc ngâm về nỗi cô đơn và khát vọng đoàn tụ của người vợ có chồng ra trận, nổi tiếng với bản diễn Nôm song thất lục bát.',
   NULL,
   'https://vi.wikisource.org/wiki/Chinh_phụ_ngâm', 1, '2026-01-05 09:00:00'),
  (14, 'Cung oán ngâm khúc', 'Nguyễn Gia Thiều', 'Thế kỷ XVIII', 1, 1, 82000, 102000, 118,
   'Lời than của người cung nữ bị bỏ quên nơi lãnh cung, tiêu biểu cho cảm hứng nhân đạo của văn học trung đại.',
   NULL,
   'https://vi.wikisource.org/wiki/Cung_oán_ngâm_khúc', 1, '2026-01-05 09:05:00'),
  (15, 'Bích Câu kỳ ngộ', 'Khuyết danh', 'Thế kỷ XVIII', 1, 3, 76000, 95000, 125,
   'Truyện thơ Nôm kể cuộc gặp gỡ kỳ lạ giữa chàng Tú Uyên và nàng Giáng Kiều bên đất Bích Câu.',
   NULL,
   'https://vi.wikisource.org/wiki/Bích_Câu_kỳ_ngộ', 1, '2026-01-05 09:10:00'),
  (16, 'Nhị độ mai', 'Khuyết danh', 'Thế kỷ XIX', 1, 3, 79000, 98000, 132,
   'Truyện thơ Nôm về lòng trung nghĩa và luật nhân quả, lấy hình ảnh cây mai hai lần nở hoa làm biểu tượng.',
   NULL,
   'https://vi.wikisource.org/wiki/Nhị_độ_mai', 1, '2026-01-05 09:15:00'),
  (17, 'Quốc âm thi tập', 'Nguyễn Trãi', 'Thế kỷ XV', 5, 1, 132000, 165000, 139,
   'Tập thơ chữ Nôm cổ nhất còn lại, gồm 254 bài — cột mốc mở đầu cho dòng thơ Nôm Việt Nam.',
   NULL,
   'https://vi.wikisource.org/wiki/Quốc_âm_thi_tập', 1, '2026-01-05 09:20:00'),
  (18, 'Bạch Vân quốc ngữ thi tập', 'Nguyễn Bỉnh Khiêm', 'Thế kỷ XVI', 5, 1, 118000, 147000, 146,
   'Tập thơ Nôm của Trạng Trình, thấm đẫm triết lý sống nhàn và cái nhìn tỉnh táo trước thời cuộc.',
   NULL,
   'https://vi.wikisource.org/wiki/Bạch_Vân_quốc_ngữ_thi_tập', 1, '2026-01-05 09:25:00'),
  (19, 'Thơ Hồ Xuân Hương', 'Hồ Xuân Hương', 'Thế kỷ XVIII–XIX', 5, 2, 92000, 115000, 23,
   'Tuyển thơ Nôm của "Bà chúa thơ Nôm" — giọng thơ táo bạo, sắc sảo, giàu tinh thần phản kháng và bênh vực người phụ nữ.',
   'BÁNH TRÔI NƯỚC

Thân em vừa trắng lại vừa tròn,
Bảy nổi ba chìm với nước non.

Rắn nát mặc dầu tay kẻ nặn,
Mà em vẫn giữ tấm lòng son.',
   'https://vi.wikisource.org/wiki/Tác_gia:Hồ_Xuân_Hương', 1, '2026-01-05 09:30:00'),
  (20, 'Thơ Nguyễn Khuyến', 'Nguyễn Khuyến', 'Thế kỷ XIX', 5, 1, 89000, 111000, 30,
   'Tuyển thơ của "Tam nguyên Yên Đổ" — nổi tiếng với chùm thơ thu và giọng trào phúng kín đáo mà thâm thúy.',
   'THU ĐIẾU (Câu cá mùa thu)

Ao thu lạnh lẽo nước trong veo,
Một chiếc thuyền câu bé tẻo teo.

Sóng biếc theo làn hơi gợn tí,
Lá vàng trước gió khẽ đưa vèo.',
   'https://vi.wikisource.org/wiki/Tác_gia:Nguyễn_Khuyến', 1, '2026-01-05 09:35:00'),
  (21, 'Thơ Trần Tế Xương', 'Trần Tế Xương', 'Thế kỷ XIX', 5, 1, 86000, 107000, 37,
   'Tuyển thơ của Tú Xương — tiếng cười chua chát về xã hội giao thời, xen lẫn những bài trữ tình cảm động về gia đình.',
   'THƯƠNG VỢ

Quanh năm buôn bán ở mom sông,
Nuôi đủ năm con với một chồng.

Lặn lội thân cò khi quãng vắng,
Eo sèo mặt nước buổi đò đông.',
   'https://vi.wikisource.org/wiki/Tác_gia:Trần_Tế_Xương', 1, '2026-01-05 09:40:00'),
  (22, 'Nhật ký trong tù', 'Hồ Chí Minh', '1943', 5, 1, 75000, 93000, 44,
   'Tập thơ chữ Hán gồm hơn một trăm bài, viết trong thời gian bị giam giữ tại Quảng Tây, Trung Quốc.',
   NULL,
   'https://vi.wikisource.org/wiki/Nhật_ký_trong_tù', 1, '2026-01-05 09:45:00'),
  (23, 'Truyền kỳ mạn lục', 'Nguyễn Dữ', 'Thế kỷ XVI', 6, 2, 105000, 131000, 51,
   'Tập hai mươi truyện truyền kỳ viết bằng chữ Hán, trong đó có "Chuyện người con gái Nam Xương" quen thuộc.',
   NULL,
   'https://vi.wikisource.org/wiki/Truyền_kỳ_mạn_lục', 1, '2026-01-05 09:50:00'),
  (24, 'Lĩnh Nam chích quái', 'Trần Thế Pháp', 'Thế kỷ XIV', 6, 3, 99000, 123000, 58,
   'Sưu tập truyện dân gian và thần thoại nước Nam, lưu giữ nhiều truyền thuyết khởi nguyên của dân tộc.',
   NULL,
   'https://vi.wikisource.org/wiki/Lĩnh_Nam_chích_quái', 1, '2026-01-05 09:55:00'),
  (25, 'Việt Nam sử lược', 'Trần Trọng Kim', '1920', 7, 1, 168000, 210000, 65,
   'Bộ thông sử bằng chữ quốc ngữ đầu tiên viết theo lối hiện đại, trình bày lịch sử Việt Nam từ thời thượng cổ đến thời Pháp thuộc.',
   NULL,
   'https://vi.wikisource.org/wiki/Việt_Nam_sử_lược', 1, '2026-01-05 10:00:00');

INSERT INTO nguoi_dung (id, ho_ten, email, mat_khau, so_dien_thoai, ngay_sinh, dia_chi, vai_tro, trang_thai, ngay_tao) VALUES
  (1, 'Nguyễn Văn A', 'nguyenvana@gmail.com', '$2y$12$eokGJbRZQvMw.B9/yVeDHu73SGRB4jGx2bQyd7s1IRlZDFAcIHjiG', '0901 234 567', '2004-05-12', '123 Nguyễn Huệ, Quận 1, TP.HCM', 'user', 'hoat_dong', '2026-01-08 09:15:00'),
  (2, 'Trần Thị B', 'tranthib@gmail.com', '$2y$12$WtxGTS.NDmoytqeBbsIvZOeL.H5V0wPInrx2Vx3T3OLfPm/AcQz46', '0912 345 678', '1998-11-02', '45 Lê Lợi, Quận 1, TP.HCM', 'admin', 'hoat_dong', '2026-01-02 08:00:00'),
  (3, 'Lê Minh C', 'leminhc@gmail.com', '$2y$12$aISbTcTTa3aAnOtFboZ82eQraxQXOCtdsUCaeNQRznJ1o71r56wb.', '0923 456 789', '2003-03-21', '18 Trần Phú, Hải Châu, Đà Nẵng', 'user', 'hoat_dong', '2026-01-20 20:11:00'),
  (4, 'Phạm Thu D', 'phamthud@gmail.com', '$2y$12$.ZzFU.k8xpDLyzrCjKv85O1ZxYkfy0E4CjaD5DE0r82FX15tNFEtO', '0934 567 890', '2000-07-15', '27 Hai Bà Trưng, Hoàn Kiếm, Hà Nội', 'staff', 'tam_khoa', '2026-02-03 10:00:00'),
  (5, 'Hoàng Văn E', 'hoangvane@gmail.com', '$2y$12$j843hrSk.q0PnrZF9L79xu.M13ZC5kbiMo0riwTIr.QvJ7EC9/0pm', '0945 678 901', '1995-01-30', '9 Lý Thường Kiệt, TP. Huế', 'user', 'hoat_dong', '2026-02-14 19:40:00'),
  (6, 'Vũ Thị F', 'vuthif@gmail.com', '$2y$12$5ebLmAwTSKQyNN5ZCKdGc.83GXr1Co2gczf7F/Y3AqQ1RaRPZx.RO', '0956 789 012', NULL, '56 Nguyễn Trãi, Thanh Xuân, Hà Nội', 'user', 'cho_duyet', '2026-09-20 22:05:00'),
  (7, 'Đặng Quốc G', 'dangquocg@gmail.com', '$2y$12$sTKg0zu4ahXGKtl/3lXk6e8BasrEoPIOjn0Kpg6lonCpbYiBROr8m', '0967 890 123', '1999-09-09', '102 Võ Văn Tần, Quận 3, TP.HCM', 'staff', 'hoat_dong', '2026-02-25 09:30:00'),
  (8, 'Bùi Thanh H', 'buithanhh@gmail.com', '$2y$12$IDPhPbkwXOsMUSz0VM89yu90gIFi9xSq/fxJpH1JqWuFoLbVwRi0S', '0978 901 234', '2002-12-01', '14 Hùng Vương, Ninh Kiều, Cần Thơ', 'user', 'tam_khoa', '2026-03-06 18:22:00'),
  (9, 'Nguyễn Minh Hà', 'ha.nguyen@example.com', '$2y$12$pA2usww5qD1at3GeKm7RmOoIb24/knEeTGMyQg.RHNToAjh64rLcS', '0981 112 233', '1990-04-18', '33 Tràng Thi, Hoàn Kiếm, Hà Nội', 'user', 'hoat_dong', '2026-03-15 21:03:00'),
  (10, 'Trần Thanh Long', 'long.tran@example.com', '$2y$12$S9o9NIp95vyByFlzRnZMwOoproV1Lyo2XhSXMufdb4lgKKhB6O2ay', '0982 223 344', '1987-08-25', '71 Bạch Đằng, Hải Châu, Đà Nẵng', 'user', 'hoat_dong', '2026-03-28 07:45:00'),
  (11, 'Phạm Ngọc Anh', 'anh.pham@example.com', '$2y$12$OxiAr.CmIdtPanJqSp1Fc.qACJtVBnsSvl6yMeKDQCr6hPNYaVD0S', '0983 334 455', '1983-06-10', '5 Lạch Tray, Ngô Quyền, Hải Phòng', 'user', 'hoat_dong', '2026-04-09 12:30:00'),
  (12, 'Đỗ Gia Huy', 'huy.do@example.com', '$2y$12$mgLqhFSdeAoo8SE.E0LMS.g6fXA3KFhNCkaRJVH22FU1vNEKPhlMW', '0984 445 566', '2005-02-14', '88 Mậu Thân, Ninh Kiều, Cần Thơ', 'user', 'hoat_dong', '2026-05-02 16:10:00'),
  (13, 'Lương Thị Mai', 'mai.luong@example.com', '$2y$12$mi.tiFf2ij8qHp2boU856eJ7XqAtIllHlRNitdFGd3vhSEZglOYre', '0985 556 677', '1996-10-05', '21 Lê Lợi, TP. Huế', 'user', 'hoat_dong', '2026-06-11 20:20:00'),
  (14, 'Võ Hoàng Nam', 'nam.vo@example.com', '$2y$12$Z6X0D0BIkKqtmSB4LBS0IeqGXsF7UIHqsjGtFAfbyvK8tLs8THKRa', '0986 667 788', '2001-12-24', '40 Phạm Văn Thuận, Biên Hòa, Đồng Nai', 'user', 'hoat_dong', '2026-09-03 11:11:00'),
  (15, 'Trịnh Khánh Linh', 'linh.trinh@example.com', '$2y$12$16DanMPDMIwz0Sy2fipZleRgFkWLwVPXLOKCfljS9pGnh7nS8BI9S', '0987 778 899', '2004-08-08', '12 Trần Phú, Nha Trang, Khánh Hòa', 'user', 'hoat_dong', '2026-09-12 15:35:00'),
  (16, 'Quản trị viên', 'admin@sachviet.vn', '$2y$12$T//SWAuE1vS/5fP4D2/ohOxCY99NRm6ZPSGNv7azZzLUnM8ss4CKm', '0900 123 456', NULL, '123 Nguyễn Huệ, Quận 1, TP.HCM', 'admin', 'hoat_dong', '2026-01-01 00:00:00');

INSERT INTO don_hang (id, nguoi_dung_id, ho_ten_nhan, so_dien_thoai, dia_chi_giao, ghi_chu, phuong_thuc_tt, phi_van_chuyen, trang_thai, ngay_dat) VALUES
  (1, 3, 'Lê Minh C', '0923 456 789', '18 Trần Phú, Hải Châu, Đà Nẵng', NULL, 'cod', 30000, 'da_giao', '2026-04-05 21:06:00'),
  (2, 3, 'Lê Minh C', '0923 456 789', '18 Trần Phú, Hải Châu, Đà Nẵng', NULL, 'ewallet', 0, 'da_giao', '2026-04-13 21:18:00'),
  (3, 1, 'Nguyễn Văn A', '0901 234 567', '123 Nguyễn Huệ, Quận 1, TP.HCM', NULL, 'cod', 30000, 'da_giao', '2026-04-18 10:24:00'),
  (4, 10, 'Trần Thanh Long', '0982 223 344', '71 Bạch Đằng, Hải Châu, Đà Nẵng', NULL, 'ewallet', 0, 'da_giao', '2026-04-19 19:40:00'),
  (5, 3, 'Lê Minh C', '0923 456 789', '18 Trần Phú, Hải Châu, Đà Nẵng', NULL, 'ewallet', 30000, 'da_giao', '2026-04-24 21:00:00'),
  (6, 5, 'Hoàng Văn E', '0945 678 901', '9 Lý Thường Kiệt, TP. Huế', NULL, 'cod', 30000, 'da_giao', '2026-05-02 17:59:00'),
  (7, 9, 'Nguyễn Minh Hà', '0981 112 233', '33 Tràng Thi, Hoàn Kiếm, Hà Nội', NULL, 'cod', 30000, 'da_giao', '2026-05-04 14:42:00'),
  (8, 3, 'Lê Minh C', '0923 456 789', '18 Trần Phú, Hải Châu, Đà Nẵng', NULL, 'cod', 30000, 'da_giao', '2026-05-17 16:53:00'),
  (9, 9, 'Nguyễn Minh Hà', '0981 112 233', '33 Tràng Thi, Hoàn Kiếm, Hà Nội', NULL, 'banking', 30000, 'da_giao', '2026-05-18 13:39:00'),
  (10, 11, 'Phạm Ngọc Anh', '0983 334 455', '5 Lạch Tray, Ngô Quyền, Hải Phòng', NULL, 'cod', 30000, 'da_giao', '2026-05-27 11:27:00'),
  (11, 12, 'Đỗ Gia Huy', '0984 445 566', '88 Mậu Thân, Ninh Kiều, Cần Thơ', NULL, 'cod', 30000, 'da_giao', '2026-05-29 18:23:00'),
  (12, 1, 'Nguyễn Văn A', '0901 234 567', '123 Nguyễn Huệ, Quận 1, TP.HCM', NULL, 'banking', 30000, 'da_giao', '2026-06-05 19:12:00'),
  (13, 5, 'Hoàng Văn E', '0945 678 901', '9 Lý Thường Kiệt, TP. Huế', NULL, 'ewallet', 30000, 'da_huy', '2026-06-08 19:17:00'),
  (14, 10, 'Trần Thanh Long', '0982 223 344', '71 Bạch Đằng, Hải Châu, Đà Nẵng', NULL, 'banking', 30000, 'da_giao', '2026-06-09 15:16:00'),
  (15, 11, 'Phạm Ngọc Anh', '0983 334 455', '5 Lạch Tray, Ngô Quyền, Hải Phòng', NULL, 'banking', 30000, 'da_giao', '2026-06-11 18:45:00'),
  (16, 3, 'Lê Minh C', '0923 456 789', '18 Trần Phú, Hải Châu, Đà Nẵng', NULL, 'cod', 30000, 'da_giao', '2026-06-16 18:56:00'),
  (17, 3, 'Lê Minh C', '0923 456 789', '18 Trần Phú, Hải Châu, Đà Nẵng', NULL, 'cod', 30000, 'da_huy', '2026-06-27 17:44:00'),
  (18, 3, 'Lê Minh C', '0923 456 789', '18 Trần Phú, Hải Châu, Đà Nẵng', 'Giao giờ hành chính', 'banking', 30000, 'da_giao', '2026-06-29 11:17:00'),
  (19, 13, 'Lương Thị Mai', '0985 556 677', '21 Lê Lợi, TP. Huế', NULL, 'cod', 30000, 'da_giao', '2026-07-06 08:11:00'),
  (20, 12, 'Đỗ Gia Huy', '0984 445 566', '88 Mậu Thân, Ninh Kiều, Cần Thơ', NULL, 'cod', 30000, 'da_giao', '2026-07-15 12:53:00'),
  (21, 5, 'Hoàng Văn E', '0945 678 901', '9 Lý Thường Kiệt, TP. Huế', NULL, 'cod', 30000, 'da_giao', '2026-07-19 18:20:00'),
  (22, 1, 'Nguyễn Văn A', '0901 234 567', '123 Nguyễn Huệ, Quận 1, TP.HCM', 'Giao giờ hành chính', 'cod', 30000, 'da_giao', '2026-07-22 20:41:00'),
  (23, 5, 'Hoàng Văn E', '0945 678 901', '9 Lý Thường Kiệt, TP. Huế', 'Để hàng ở phòng bảo vệ', 'banking', 0, 'da_giao', '2026-07-25 13:42:00'),
  (24, 12, 'Đỗ Gia Huy', '0984 445 566', '88 Mậu Thân, Ninh Kiều, Cần Thơ', 'Để hàng ở phòng bảo vệ', 'ewallet', 30000, 'da_giao', '2026-07-29 11:51:00'),
  (25, 11, 'Phạm Ngọc Anh', '0983 334 455', '5 Lạch Tray, Ngô Quyền, Hải Phòng', 'Giao sau 18h', 'cod', 30000, 'da_giao', '2026-07-29 12:23:00'),
  (26, 11, 'Phạm Ngọc Anh', '0983 334 455', '5 Lạch Tray, Ngô Quyền, Hải Phòng', NULL, 'banking', 30000, 'da_giao', '2026-07-29 20:33:00'),
  (27, 12, 'Đỗ Gia Huy', '0984 445 566', '88 Mậu Thân, Ninh Kiều, Cần Thơ', 'Gọi trước khi giao', 'cod', 30000, 'da_giao', '2026-08-01 20:46:00'),
  (28, 9, 'Nguyễn Minh Hà', '0981 112 233', '33 Tràng Thi, Hoàn Kiếm, Hà Nội', NULL, 'cod', 30000, 'da_giao', '2026-08-04 08:48:00'),
  (29, 10, 'Trần Thanh Long', '0982 223 344', '71 Bạch Đằng, Hải Châu, Đà Nẵng', 'Giao sau 18h', 'cod', 30000, 'da_giao', '2026-08-04 09:50:00'),
  (30, 9, 'Nguyễn Minh Hà', '0981 112 233', '33 Tràng Thi, Hoàn Kiếm, Hà Nội', NULL, 'banking', 30000, 'da_giao', '2026-08-04 21:33:00'),
  (31, 3, 'Lê Minh C', '0923 456 789', '18 Trần Phú, Hải Châu, Đà Nẵng', 'Giao sau 18h', 'ewallet', 30000, 'da_giao', '2026-08-11 11:11:00'),
  (32, 1, 'Nguyễn Văn A', '0901 234 567', '123 Nguyễn Huệ, Quận 1, TP.HCM', NULL, 'cod', 0, 'da_huy', '2026-08-14 09:05:00'),
  (33, 11, 'Phạm Ngọc Anh', '0983 334 455', '5 Lạch Tray, Ngô Quyền, Hải Phòng', 'Giao giờ hành chính', 'banking', 30000, 'da_giao', '2026-08-19 17:17:00'),
  (34, 3, 'Lê Minh C', '0923 456 789', '18 Trần Phú, Hải Châu, Đà Nẵng', NULL, 'cod', 30000, 'da_giao', '2026-08-28 17:30:00'),
  (35, 13, 'Lương Thị Mai', '0985 556 677', '21 Lê Lợi, TP. Huế', NULL, 'cod', 30000, 'da_giao', '2026-08-31 19:49:00'),
  (36, 13, 'Lương Thị Mai', '0985 556 677', '21 Lê Lợi, TP. Huế', 'Giao giờ hành chính', 'ewallet', 30000, 'da_giao', '2026-09-03 12:59:00'),
  (37, 2, 'Trần Thị B', '0912 345 678', '45 Lê Lợi, Quận 1, TP.HCM', 'Để hàng ở phòng bảo vệ', 'banking', 30000, 'da_giao', '2026-09-07 13:51:00'),
  (38, 1, 'Nguyễn Văn A', '0901 234 567', '123 Nguyễn Huệ, Quận 1, TP.HCM', NULL, 'cod', 0, 'da_giao', '2026-09-10 14:30:00'),
  (39, 10, 'Trần Thanh Long', '0982 223 344', '71 Bạch Đằng, Hải Châu, Đà Nẵng', NULL, 'cod', 30000, 'da_giao', '2026-09-12 10:44:00'),
  (40, 13, 'Lương Thị Mai', '0985 556 677', '21 Lê Lợi, TP. Huế', NULL, 'cod', 0, 'da_giao', '2026-09-16 12:28:00'),
  (41, 3, 'Lê Minh C', '0923 456 789', '18 Trần Phú, Hải Châu, Đà Nẵng', NULL, 'cod', 30000, 'dang_giao', '2026-09-21 08:38:00'),
  (42, 12, 'Đỗ Gia Huy', '0984 445 566', '88 Mậu Thân, Ninh Kiều, Cần Thơ', NULL, 'banking', 30000, 'dang_giao', '2026-09-23 08:38:00'),
  (43, 1, 'Nguyễn Văn A', '0901 234 567', '123 Nguyễn Huệ, Quận 1, TP.HCM', NULL, 'ewallet', 30000, 'dang_giao', '2026-09-24 08:50:00'),
  (44, 9, 'Nguyễn Minh Hà', '0981 112 233', '33 Tràng Thi, Hoàn Kiếm, Hà Nội', NULL, 'cod', 0, 'cho_xac_nhan', '2026-09-26 18:50:00'),
  (45, 1, 'Nguyễn Văn A', '0901 234 567', '123 Nguyễn Huệ, Quận 1, TP.HCM', 'Gọi trước khi giao', 'cod', 30000, 'cho_xac_nhan', '2026-09-27 21:15:00'),
  (46, 7, 'Đặng Quốc G', '0967 890 123', '102 Võ Văn Tần, Quận 3, TP.HCM', NULL, 'cod', 30000, 'cho_xac_nhan', '2026-09-28 09:14:00');

INSERT INTO chi_tiet_don_hang (don_hang_id, sach_id, so_luong, don_gia) VALUES
  (1, 4, 1, 88000),
  (1, 20, 2, 89000),
  (2, 1, 1, 135000),
  (2, 6, 1, 98000),
  (2, 8, 1, 145000),
  (3, 1, 1, 135000),
  (3, 4, 1, 88000),
  (4, 1, 1, 135000),
  (4, 23, 1, 105000),
  (4, 25, 1, 168000),
  (5, 19, 2, 92000),
  (6, 2, 1, 68000),
  (6, 7, 1, 78000),
  (7, 1, 2, 135000),
  (8, 6, 1, 98000),
  (8, 13, 1, 85000),
  (9, 4, 1, 88000),
  (10, 4, 2, 88000),
  (11, 2, 1, 68000),
  (12, 19, 1, 92000),
  (12, 6, 1, 98000),
  (12, 3, 1, 62000),
  (13, 1, 2, 125000),
  (14, 3, 2, 62000),
  (15, 2, 1, 68000),
  (16, 2, 1, 68000),
  (16, 13, 1, 85000),
  (17, 2, 1, 68000),
  (17, 9, 3, 55000),
  (18, 3, 1, 62000),
  (19, 22, 1, 75000),
  (20, 4, 1, 88000),
  (20, 20, 2, 89000),
  (21, 10, 1, 52000),
  (21, 11, 1, 48000),
  (22, 17, 1, 132000),
  (22, 22, 2, 75000),
  (23, 1, 1, 125000),
  (23, 11, 2, 48000),
  (23, 25, 1, 168000),
  (24, 3, 1, 62000),
  (24, 15, 1, 76000),
  (25, 2, 1, 68000),
  (26, 19, 1, 92000),
  (27, 1, 1, 125000),
  (27, 2, 2, 68000),
  (28, 2, 1, 68000),
  (28, 3, 1, 62000),
  (28, 25, 1, 168000),
  (29, 1, 1, 125000),
  (29, 2, 1, 68000),
  (30, 4, 1, 88000),
  (30, 5, 1, 95000),
  (31, 4, 1, 88000),
  (31, 9, 1, 55000),
  (31, 14, 1, 82000),
  (32, 5, 3, 95000),
  (32, 8, 2, 145000),
  (33, 7, 1, 78000),
  (33, 24, 1, 99000),
  (34, 16, 1, 79000),
  (34, 19, 1, 92000),
  (35, 1, 1, 125000),
  (36, 7, 1, 78000),
  (36, 10, 1, 52000),
  (37, 12, 1, 110000),
  (38, 1, 2, 125000),
  (38, 9, 1, 55000),
  (39, 11, 1, 48000),
  (40, 1, 2, 125000),
  (40, 18, 1, 118000),
  (40, 21, 1, 86000),
  (41, 9, 1, 55000),
  (41, 18, 1, 118000),
  (41, 23, 1, 105000),
  (42, 14, 1, 82000),
  (43, 2, 1, 68000),
  (43, 3, 1, 62000),
  (44, 23, 1, 105000),
  (44, 25, 2, 168000),
  (45, 25, 1, 168000),
  (46, 22, 1, 75000);

INSERT INTO danh_gia (sach_id, nguoi_dung_id, so_sao, noi_dung, ngay_danh_gia) VALUES
  (22, 9, 5, 'Giao hàng chỉ 2 ngày, sách mới tinh, còn được tặng kèm bookmark.', '2026-04-15 09:52:00'),
  (12, 7, 5, 'Bìa đẹp, gáy chắc, để trên kệ nhìn rất sang.', '2026-04-15 20:51:00'),
  (19, 9, 4, 'Giá hơi cao so với độ dày của sách, nhưng bản in thì đẹp.', '2026-04-17 22:31:00'),
  (18, 5, 5, 'Giấy màu ngà, chữ to vừa phải, đọc lâu không mỏi mắt.', '2026-04-22 14:50:00'),
  (6, 7, 4, 'Phần chú giải dễ hiểu, có vài lỗi chính tả nhỏ ở phần phụ lục.', '2026-04-26 23:56:00'),
  (7, 11, 3, 'Giao hàng chậm gần một tuần, sách thì bình thường.', '2026-04-28 22:50:00'),
  (5, 8, 5, 'Đọc lại sau nhiều năm vẫn thấy hay. Giá hợp lý so với chất lượng in.', '2026-04-30 10:45:00'),
  (3, 8, 5, 'Phần giới thiệu tác giả và hoàn cảnh sáng tác ở đầu sách viết rất hay.', '2026-04-30 19:32:00'),
  (20, 4, 5, 'Bản in đẹp nhất trong số các bản mình từng mua. Rất đáng tiền.', '2026-05-04 04:53:00'),
  (2, 5, 5, 'Đóng gói cẩn thận, bọc màng chống ẩm, sách không bị móp góc nào.', '2026-05-07 09:39:00'),
  (14, 12, 3, 'Tạm được, phần chú thích còn khá sơ sài.', '2026-05-08 09:29:00'),
  (13, 10, 4, 'Sách đẹp, chỉ tiếc là giao chậm hơn dự kiến một ngày.', '2026-05-18 15:18:00'),
  (3, 10, 5, 'Giao hàng chỉ 2 ngày, sách mới tinh, còn được tặng kèm bookmark.', '2026-05-19 10:43:00'),
  (6, 5, 5, 'Bìa đẹp, gáy chắc, để trên kệ nhìn rất sang.', '2026-05-19 18:04:00'),
  (1, 3, 5, 'Chú thích từ cổ rất chi tiết, giúp mình hiểu thêm nhiều điển tích.', '2026-05-19 22:47:00'),
  (21, 11, 4, 'Chú thích đầy đủ nhưng cỡ chữ hơi nhỏ với người lớn tuổi.', '2026-05-26 08:17:00'),
  (17, 10, 5, 'Đọc lại sau nhiều năm vẫn thấy hay. Giá hợp lý so với chất lượng in.', '2026-05-29 18:00:00'),
  (15, 12, 4, 'Sách ổn, nhưng hộp giao đến bị móp nhẹ ở một góc.', '2026-06-02 10:38:00'),
  (9, 3, 4, 'Sách đẹp, chỉ tiếc là giao chậm hơn dự kiến một ngày.', '2026-06-03 18:31:00'),
  (8, 4, 4, 'Giá hơi cao so với độ dày của sách, nhưng bản in thì đẹp.', '2026-06-05 18:41:00'),
  (9, 9, 5, 'Giấy màu ngà, chữ to vừa phải, đọc lâu không mỏi mắt.', '2026-06-06 09:21:00'),
  (23, 12, 4, 'Sách ổn, nhưng hộp giao đến bị móp nhẹ ở một góc.', '2026-06-07 08:40:00'),
  (10, 11, 5, 'Mua tặng bố nhân dịp sinh nhật, bố rất thích.', '2026-06-16 23:59:00'),
  (10, 2, 5, 'Giao hàng chỉ 2 ngày, sách mới tinh, còn được tặng kèm bookmark.', '2026-06-18 04:16:00'),
  (7, 7, 5, 'Đóng gói cẩn thận, bọc màng chống ẩm, sách không bị móp góc nào.', '2026-06-19 19:01:00'),
  (2, 10, 5, 'Giấy màu ngà, chữ to vừa phải, đọc lâu không mỏi mắt.', '2026-06-20 00:19:00'),
  (2, 1, 5, 'Đọc lại sau nhiều năm vẫn thấy hay. Giá hợp lý so với chất lượng in.', '2026-06-21 18:16:00'),
  (10, 7, 4, 'Sách ổn, nhưng hộp giao đến bị móp nhẹ ở một góc.', '2026-06-24 01:57:00'),
  (22, 3, 4, 'Nội dung hay, mong nhà sách có thêm bản bìa cứng.', '2026-06-24 16:14:00'),
  (16, 9, 4, 'Phần chú giải dễ hiểu, có vài lỗi chính tả nhỏ ở phần phụ lục.', '2026-06-26 20:38:00'),
  (14, 9, 4, 'Nội dung hay, mong nhà sách có thêm bản bìa cứng.', '2026-06-30 02:47:00'),
  (1, 13, 5, 'Bản in đẹp nhất trong số các bản mình từng mua. Rất đáng tiền.', '2026-07-02 10:18:00'),
  (3, 7, 4, 'Chú thích đầy đủ nhưng cỡ chữ hơi nhỏ với người lớn tuổi.', '2026-07-08 18:55:00'),
  (17, 5, 5, 'Đóng gói cẩn thận, bọc màng chống ẩm, sách không bị móp góc nào.', '2026-07-11 11:23:00'),
  (4, 3, 4, 'Chất lượng in tốt, gáy sách hơi cứng lúc mới mở.', '2026-07-12 03:32:00'),
  (5, 13, 4, 'Nội dung hay, mong nhà sách có thêm bản bìa cứng.', '2026-07-12 04:23:00'),
  (11, 12, 4, 'Chú thích đầy đủ nhưng cỡ chữ hơi nhỏ với người lớn tuổi.', '2026-07-12 22:14:00'),
  (15, 3, 4, 'Chất lượng in tốt, gáy sách hơi cứng lúc mới mở.', '2026-07-13 17:32:00'),
  (23, 5, 4, 'Chú thích đầy đủ nhưng cỡ chữ hơi nhỏ với người lớn tuổi.', '2026-07-14 07:00:00'),
  (4, 2, 5, 'Đọc thử trên web thấy thích nên mua bản in để giữ. Không thất vọng.', '2026-07-14 18:29:00'),
  (11, 10, 4, 'Chất lượng in tốt, gáy sách hơi cứng lúc mới mở.', '2026-07-21 08:17:00'),
  (10, 9, 5, 'Đọc thử trên web thấy thích nên mua bản in để giữ. Không thất vọng.', '2026-07-23 12:38:00'),
  (5, 1, 5, 'Bản in đẹp nhất trong số các bản mình từng mua. Rất đáng tiền.', '2026-07-25 20:24:00'),
  (4, 9, 5, 'Mua để ôn thi, cô giáo cũng khen bản này chú giải kỹ.', '2026-07-26 09:11:00'),
  (24, 3, 5, 'Bìa đẹp, gáy chắc, để trên kệ nhìn rất sang.', '2026-07-27 15:41:00'),
  (1, 11, 5, 'Mua cho con học lớp 11. Phần chú thích rất kỹ, cháu đọc hiểu được ngay.', '2026-07-28 21:05:00'),
  (6, 9, 4, 'Sách ổn, nhưng hộp giao đến bị móp nhẹ ở một góc.', '2026-07-29 03:45:00'),
  (19, 2, 5, 'Đọc thử trên web thấy thích nên mua bản in để giữ. Không thất vọng.', '2026-07-30 15:02:00'),
  (21, 1, 4, 'Chất lượng in tốt, gáy sách hơi cứng lúc mới mở.', '2026-08-01 14:26:00'),
  (1, 10, 4, 'Nội dung thì khỏi bàn. Bìa hơi mỏng một chút so với giá tiền, nhưng nhìn chung vẫn hài lòng.', '2026-08-03 09:32:00'),
  (12, 10, 4, 'Phần chú giải dễ hiểu, có vài lỗi chính tả nhỏ ở phần phụ lục.', '2026-08-04 07:55:00'),
  (11, 3, 5, 'Mua để ôn thi, cô giáo cũng khen bản này chú giải kỹ.', '2026-08-05 14:38:00'),
  (8, 5, 4, 'Chú thích đầy đủ nhưng cỡ chữ hơi nhỏ với người lớn tuổi.', '2026-08-06 06:57:00'),
  (3, 4, 5, 'Bản in trang nhã, chú thích dễ hiểu. Đáng để giữ trong tủ sách gia đình.', '2026-08-09 07:19:00'),
  (13, 2, 4, 'Giá hơi cao so với độ dày của sách, nhưng bản in thì đẹp.', '2026-08-10 07:55:00'),
  (19, 11, 5, 'Giao hàng chỉ 2 ngày, sách mới tinh, còn được tặng kèm bookmark.', '2026-08-10 21:49:00'),
  (1, 9, 5, 'Sách in đẹp, chữ rõ, giấy dày. Mình đọc thử trên web trước rồi mới đặt, rất yên tâm. Giao hàng nhanh hơn dự kiến.', '2026-08-12 20:14:00'),
  (20, 1, 5, 'Chú thích từ cổ rất chi tiết, giúp mình hiểu thêm nhiều điển tích.', '2026-08-20 10:38:00'),
  (4, 11, 5, 'Bản in đẹp nhất trong số các bản mình từng mua. Rất đáng tiền.', '2026-08-21 14:16:00'),
  (25, 4, 5, 'Bản in trang nhã, chú thích dễ hiểu. Đáng để giữ trong tủ sách gia đình.', '2026-08-22 16:50:00'),
  (25, 12, 4, 'Phần chú giải dễ hiểu, có vài lỗi chính tả nhỏ ở phần phụ lục.', '2026-08-24 10:58:00'),
  (6, 3, 3, 'Nội dung hay nhưng giấy hơi mỏng, nhìn thấu chữ ở trang sau.', '2026-08-27 03:43:00'),
  (16, 11, 3, 'Nội dung hay nhưng giấy hơi mỏng, nhìn thấu chữ ở trang sau.', '2026-08-27 05:52:00'),
  (19, 1, 5, 'Phần giới thiệu tác giả và hoàn cảnh sáng tác ở đầu sách viết rất hay.', '2026-08-27 06:47:00'),
  (22, 8, 5, 'Mua để ôn thi, cô giáo cũng khen bản này chú giải kỹ.', '2026-08-27 18:40:00'),
  (2, 13, 5, 'Bản in trang nhã, chú thích dễ hiểu. Đáng để giữ trong tủ sách gia đình.', '2026-08-29 15:59:00'),
  (12, 4, 4, 'Nội dung hay, mong nhà sách có thêm bản bìa cứng.', '2026-08-31 02:14:00'),
  (9, 5, 5, 'Phần giới thiệu tác giả và hoàn cảnh sáng tác ở đầu sách viết rất hay.', '2026-09-03 05:29:00'),
  (20, 10, 4, 'Sách đẹp, chỉ tiếc là giao chậm hơn dự kiến một ngày.', '2026-09-07 15:26:00'),
  (9, 11, 5, 'Mua tặng bố nhân dịp sinh nhật, bố rất thích.', '2026-09-12 07:27:00'),
  (1, 1, 5, 'Đặt lần hai để tặng bạn. Bản in rất đẹp, chú thích kỹ.', '2026-09-14 19:40:00'),
  (2, 14, 4, 'Sách đẹp, chỉ tiếc là giao chậm hơn dự kiến một ngày.', '2026-09-14 21:45:00'),
  (18, 7, 3, 'Giao hàng chậm gần một tuần, sách thì bình thường.', '2026-09-15 07:52:00'),
  (24, 4, 3, 'Tạm được, phần chú thích còn khá sơ sài.', '2026-09-16 02:50:00'),
  (16, 15, 2, 'Sách bị lỗi in mờ vài trang, đã liên hệ nhà sách để đổi.', '2026-09-17 12:34:00'),
  (4, 15, 5, 'Chú thích từ cổ rất chi tiết, giúp mình hiểu thêm nhiều điển tích.', '2026-09-19 00:25:00'),
  (14, 15, 5, 'Bản in trang nhã, chú thích dễ hiểu. Đáng để giữ trong tủ sách gia đình.', '2026-09-19 07:45:00'),
  (25, 15, 5, 'Mua để ôn thi, cô giáo cũng khen bản này chú giải kỹ.', '2026-09-19 11:34:00'),
  (5, 15, 5, 'Chú thích từ cổ rất chi tiết, giúp mình hiểu thêm nhiều điển tích.', '2026-09-23 14:40:00'),
  (3, 15, 5, 'Mua tặng bố nhân dịp sinh nhật, bố rất thích.', '2026-09-25 17:56:00');

INSERT INTO gio_hang (nguoi_dung_id, sach_id, so_luong, ngay_them) VALUES
  (1, 1, 2, '2026-09-26 21:40:00'),
  (1, 2, 1, '2026-09-26 21:42:00'),
  (1, 9, 1, '2026-09-27 08:03:00'),
  (3, 5, 1, '2026-09-25 13:20:00'),
  (12, 12, 2, '2026-09-21 18:05:00');

INSERT INTO yeu_thich (nguoi_dung_id, sach_id, ngay_them) VALUES
  (1, 3, '2026-06-06 08:00:00'),
  (1, 6, '2026-06-20 21:10:00'),
  (1, 19, '2026-07-03 19:25:00'),
  (1, 25, '2026-09-01 22:02:00'),
  (3, 1, '2026-05-11 10:00:00'),
  (9, 13, '2026-08-02 20:30:00'),
  (11, 1, '2026-07-20 07:45:00'),
  (13, 20, '2026-07-09 12:00:00');

SET FOREIGN_KEY_CHECKS = 1;
