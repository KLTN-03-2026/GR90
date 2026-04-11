export const adminMenu = [
  {
    title: 'Dashboard',
    route: '/admin/dashboard',
    icon: 'HomeFilled',
  },
  {
    title: 'T\u00e0i kho\u1ea3n',
    icon: 'UserFilled',
    children: [
      { title: 'Qu\u1ea3n tr\u1ecb vi\u00ean', route: '/admin/quan-tri-viens', icon: 'Avatar' },
      { title: 'Kh\u00e1ch h\u00e0ng', route: '/admin/khach-hangs', icon: 'User' },
      { title: 'Qu\u1ea3n l\u00fd quy\u1ec1n', route: '/admin/phan-quyens', icon: 'Lock', masterOnly: true },
    ],
  },
  {
    title: 'Danh m\u1ee5c',
    icon: 'CollectionTag',
    children: [
      { title: 'Lo\u1ea1i tuy\u1ebfn', route: '/admin/loai-tuyens', icon: 'Files' },
      { title: 'Tr\u1ea1ng th\u00e1i tuy\u1ebfn', route: '/admin/trang-thai-tuyens', icon: 'InfoFilled' },
      { title: '\u0110\u01a1n v\u1ecb v\u1eadn h\u00e0nh', route: '/admin/don-vi-van-hanhs', icon: 'OfficeBuilding' },
      { title: 'Tr\u1ea1m xe', route: '/admin/tram-xes', icon: 'LocationFilled' },
    ],
  },
  {
    title: 'V\u1eadn h\u00e0nh',
    icon: 'Operation',
    children: [
      { title: 'Tuy\u1ebfn xe', route: '/admin/tuyen-xes', icon: 'Van' },
      { title: 'Xe buýt', route: '/admin/xe-buyts', icon: 'Van' },
      { title: 'L\u1ed9 tr\u00ecnh tuy\u1ebfn', route: '/admin/lo-trinh-tuyens', icon: 'Connection' },
      { title: 'Gi\u00e1 v\u00e9 tuy\u1ebfn', route: '/admin/gia-ve-tuyens', icon: 'Tickets' },
    ],
  },
];

function formatDateOnly(value) {
  if (!value) {
    return '';
  }

  const date = new Date(value);
  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return date.toLocaleDateString('vi-VN');
}

function formatPermissionList(value) {
  if (!Array.isArray(value) || value.length === 0) {
    return '';
  }

  return value.join(', ');
}

export const adminModules = {
  quanTriViens: {
    title: 'Qu\u1ea3n tr\u1ecb vi\u00ean',
    endpoint: '/admin/tai-khoan/quan-tri-viens',
    columns: [
      { prop: 'id', label: 'ID', width: 80 },
      { prop: 'ma_quan_tri_vien', label: 'M\u00e3 QTV', width: 140, align: 'center' },
      { prop: 'ten_dang_nhap', label: 'T\u00ean \u0111\u0103ng nh\u1eadp', width: 180 },
      { prop: 'ho_ten', label: 'H\u1ecd t\u00ean', minWidth: 180 },
      { prop: 'email', label: 'Email', minWidth: 220 },
      {
        prop: 'trang_thai',
        label: 'Tr\u1ea1ng th\u00e1i',
        width: 130,
        valueMap: {
          1: 'Ho\u1ea1t \u0110\u1ed9ng',
          0: 'B\u1ecb Kh\u00f3a',
        },
      },
      {
        prop: 'is_master',
        label: 'Quy\u1ec1n',
        width: 120,
        align: 'center',
        valueMap: {
          1: 'Master',
          0: 'Th\u01b0\u1eddng',
        },
      },
      {
        prop: 'ten_quyens',
        label: 'Nh\u00f3m quy\u1ec1n',
        minWidth: 260,
        format: (value) => formatPermissionList(value),
      },
      {
        prop: 'created_at',
        label: 'T\u1ea1o l\u00fac',
        minWidth: 180,
        align: 'center',
        format: (value) => formatDateOnly(value),
      },
    ],
    formFields: [
      { prop: 'ma_quan_tri_vien', label: 'M\u00e3 QTV', type: 'text', required: true },
      { prop: 'ten_dang_nhap', label: 'T\u00ean \u0111\u0103ng nh\u1eadp', type: 'text', required: true },
      { prop: 'password', label: 'M\u1eadt kh\u1ea9u', type: 'password', requiredOnCreate: true },
      { prop: 'ho_ten', label: 'H\u1ecd t\u00ean', type: 'text', required: true },
      { prop: 'email', label: 'Email', type: 'text', required: true },
      { prop: 'so_dien_thoai', label: 'S\u1ed1 \u0111i\u1ec7n tho\u1ea1i', type: 'text' },
      {
        prop: 'trang_thai',
        label: 'Tr\u1ea1ng th\u00e1i',
        type: 'select',
        options: [
          { label: 'Ho\u1ea1t \u0110\u1ed9ng', value: 1 },
          { label: 'B\u1ecb Kh\u00f3a', value: 0 },
        ],
      },
      {
        prop: 'is_master',
        label: 'Quy\u1ec1n master',
        type: 'select',
        options: [
          { label: 'Kh\u00f4ng', value: 0 },
          { label: 'Master', value: 1 },
        ],
        defaultValue: 0,
      },
      {
        prop: 'quyen_ids',
        label: 'Nh\u00f3m quy\u1ec1n',
        type: 'select',
        multiple: true,
        showOnCreate: false,
        defaultValue: [],
        optionsEndpoint: '/admin/tai-khoan/quan-tri-viens/permission-options',
        optionLabel: 'ten_quyen',
        optionValue: 'id',
        placeholder: 'Ch\u1ecdn nh\u00f3m quy\u1ec1n cho qu\u1ea3n tr\u1ecb vi\u00ean',
      },
    ],
  },
  khachHangs: {
    title: 'Kh\u00e1ch h\u00e0ng',
    endpoint: '/admin/tai-khoan/khach-hangs',
    columns: [
      { prop: 'id', label: 'ID', width: 80 },
      { prop: 'ma_khach_hang', label: 'M\u00e3 KH', width: 140, align: 'center' },
      { prop: 'ten_dang_nhap', label: 'T\u00ean \u0111\u0103ng nh\u1eadp', width: 180 },
      { prop: 'ho_ten', label: 'H\u1ecd t\u00ean', minWidth: 180 },
      { prop: 'email', label: 'Email', minWidth: 220 },
      {
        prop: 'trang_thai',
        label: 'Tr\u1ea1ng th\u00e1i',
        width: 120,
        valueMap: {
          1: 'Ho\u1ea1t \u0110\u1ed9ng',
          0: 'B\u1ecb Kh\u00f3a',
        },
      },
    ],
    formFields: [
      { prop: 'ma_khach_hang', label: 'M\u00e3 kh\u00e1ch h\u00e0ng', type: 'text', required: true },
      { prop: 'ten_dang_nhap', label: 'T\u00ean \u0111\u0103ng nh\u1eadp', type: 'text' },
      { prop: 'password', label: 'M\u1eadt kh\u1ea9u', type: 'password' },
      { prop: 'ho_ten', label: 'H\u1ecd t\u00ean', type: 'text', required: true },
      { prop: 'email', label: 'Email', type: 'text', required: true },
      { prop: 'so_dien_thoai', label: 'S\u1ed1 \u0111i\u1ec7n tho\u1ea1i', type: 'text' },
      { prop: 'anh_dai_dien', label: '\u1ea2nh \u0111\u1ea1i di\u1ec7n URL', type: 'text' },
      {
        prop: 'trang_thai',
        label: 'Tr\u1ea1ng th\u00e1i',
        type: 'select',
        options: [
          { label: 'Ho\u1ea1t \u0110\u1ed9ng', value: 1 },
          { label: 'B\u1ecb Kh\u00f3a', value: 0 },
        ],
      },
    ],
  },
  loaiTuyens: {
    title: 'Lo\u1ea1i tuy\u1ebfn',
    endpoint: '/admin/danh-muc/loai-tuyens',
    columns: [
      { prop: 'id', label: 'ID', width: 80 },
      { prop: 'ma_loai_tuyen', label: 'M\u00e3 lo\u1ea1i', width: 140, align: 'center' },
      { prop: 'ten_loai_tuyen', label: 'T\u00ean lo\u1ea1i', minWidth: 220 },
      { prop: 'mo_ta', label: 'M\u00f4 t\u1ea3', minWidth: 280 },
      { prop: 'tuyen_xes_count', label: 'S\u1ed1 tuy\u1ebfn', width: 120 },
    ],
    formFields: [
      { prop: 'ma_loai_tuyen', label: 'M\u00e3 lo\u1ea1i tuy\u1ebfn', type: 'text', required: true },
      { prop: 'ten_loai_tuyen', label: 'T\u00ean lo\u1ea1i tuy\u1ebfn', type: 'text', required: true },
      { prop: 'mo_ta', label: 'M\u00f4 t\u1ea3', type: 'textarea' },
    ],
  },
  donViVanHanhs: {
    title: '\u0110\u01a1n v\u1ecb v\u1eadn h\u00e0nh',
    endpoint: '/admin/danh-muc/don-vi-van-hanhs',
    columns: [
      { prop: 'id', label: 'ID', width: 80 },
      { prop: 'ma_don_vi', label: 'M\u00e3 \u0111\u01a1n v\u1ecb', width: 140, align: 'center' },
      { prop: 'ten_don_vi', label: 'T\u00ean \u0111\u01a1n v\u1ecb', minWidth: 260 },
      { prop: 'so_dien_thoai', label: 'S\u1ed1 \u0111i\u1ec7n tho\u1ea1i', width: 140 },
      { prop: 'email', label: 'Email', minWidth: 200 },
      { prop: 'tuyen_xes_count', label: 'S\u1ed1 tuy\u1ebfn', width: 110 },
    ],
    formFields: [
      { prop: 'ma_don_vi', label: 'M\u00e3 \u0111\u01a1n v\u1ecb', type: 'text', required: true },
      { prop: 'ten_don_vi', label: 'T\u00ean \u0111\u01a1n v\u1ecb', type: 'text', required: true },
      { prop: 'dia_chi', label: '\u0110\u1ecba ch\u1ec9', type: 'text' },
      { prop: 'so_dien_thoai', label: 'S\u1ed1 \u0111i\u1ec7n tho\u1ea1i', type: 'text' },
      { prop: 'email', label: 'Email', type: 'text' },
    ],
  },
  trangThaiTuyens: {
    title: 'Tr\u1ea1ng th\u00e1i tuy\u1ebfn',
    endpoint: '/admin/danh-muc/trang-thai-tuyens',
    columns: [
      { prop: 'id', label: 'ID', width: 80 },
      { prop: 'ma_trang_thai', label: 'M\u00e3 tr\u1ea1ng th\u00e1i', width: 150, align: 'center' },
      {
        prop: 'ten_trang_thai',
        label: 'T\u00ean tr\u1ea1ng th\u00e1i',
        minWidth: 240,
        align: 'center',
        cellType: 'button',
        buttonWidth: 'min(320px, 100%)',
        buttonTypeMap: {
          '\u0110ang ho\u1ea1t \u0111\u1ed9ng': 'success',
          'Ho\u1ea1t \u0110\u1ed9ng': 'success',
          'T\u1ea1m d\u1eebng': 'warning',
          '\u0110ang l\u00e0m th\u1ee7 t\u1ee5c v\u1eadn h\u00e0nh': 'primary',
          'Ng\u1eebng ho\u1ea1t \u0111\u1ed9ng': 'danger',
          'B\u1ecb kh\u00f3a': 'danger',
        },
      },
      { prop: 'tuyen_xes_count', label: 'S\u1ed1 tuy\u1ebfn', width: 120 },
    ],
    formFields: [
      { prop: 'ma_trang_thai', label: 'M\u00e3 tr\u1ea1ng th\u00e1i', type: 'text', required: true },
      { prop: 'ten_trang_thai', label: 'T\u00ean tr\u1ea1ng th\u00e1i', type: 'text', required: true },
    ],
  },
  trams: {
    title: 'Tr\u1ea1m xe',
    endpoint: '/admin/danh-muc/tram-xes',
    columns: [
      { prop: 'id', label: 'ID', width: 80 },
      { prop: 'ma_tram', label: 'M\u00e3 tr\u1ea1m', width: 130, align: 'center' },
      { prop: 'ten_tram', label: 'T\u00ean tr\u1ea1m', minWidth: 220 },
      { prop: 'dia_chi', label: '\u0110\u1ecba ch\u1ec9', minWidth: 260 },
      { prop: 'vi_do', label: 'V\u0129 \u0111\u1ed9', width: 120 },
      { prop: 'kinh_do', label: 'Kinh \u0111\u1ed9', width: 120 },
    ],
    formFields: [
      { prop: 'ma_tram', label: 'M\u00e3 tr\u1ea1m', type: 'text', required: true },
      { prop: 'ten_tram', label: 'T\u00ean tr\u1ea1m', type: 'text', required: true },
      { prop: 'dia_chi', label: '\u0110\u1ecba ch\u1ec9', type: 'text' },
      { prop: 'vi_do', label: 'V\u0129 \u0111\u1ed9', type: 'number' },
      { prop: 'kinh_do', label: 'Kinh \u0111\u1ed9', type: 'number' },
      { prop: 'khu_vuc', label: 'Khu v\u1ef1c', type: 'text' },
    ],
  },
  xeBuyts: {
    title: 'Xe bu\xfdt',
    endpoint: '/admin/van-hanh/xe-buyts',
    columns: [
      { prop: 'id', label: 'ID', width: 80 },
      { prop: 'bien_so', label: 'Bi\u1ec3n s\u1ed1', width: 150, align: 'center' },
      { prop: 'ten_xe', label: 'T\xean xe', minWidth: 220 },
      { prop: 'tuyen_xe.ten_tuyen', label: 'Tuy\u1ebfn', minWidth: 260 },
      {
        prop: 'trang_thai',
        label: 'Tr\u1ea1ng th\u00e1i',
        width: 160,
        cellType: 'button',
        buttonWidth: '130px',
        align: 'center',
        buttonTypeMap: {
          'dang_hoat_dong': 'success',
          'dang_cho': 'warning',
          'dang_sua_chua': 'danger',
          'ngung_hoat_dong': 'info',
        },
      },
      { prop: 'loai_xe', label: 'Lo\u1ea1i xe', width: 160 },
      { prop: 'so_cho', label: 'S\u1ed1 ch\u1ed7', width: 100, align: 'center' },
      { prop: 'nam_san_xuat', label: 'N\u0103m SX', width: 100, align: 'center' },
    ],
    formFields: [
      { prop: 'bien_so', label: 'Bi\u1ec3n s\u1ed1', type: 'text', required: true },
      { prop: 'ten_xe', label: 'T\xean xe', type: 'text', required: true },
      {
        prop: 'tuyen_xe_id',
        label: 'Tuy\u1ebfn xe',
        type: 'select',
        required: true,
        optionsEndpoint: '/admin/van-hanh/tuyen-xes',
        optionLabel: 'ten_tuyen',
        optionValue: 'id',
        optionFormatter: (item) => {
          const ten = item?.ten_tuyen || `#${item?.id || ''}`;
          const ma = item?.ma_tuyen;
          return ma ? `${ten} (${ma})` : ten;
        },
      },
      {
        prop: 'trang_thai',
        label: 'Tr\u1ea1ng th\u00e1i',
        type: 'select',
        defaultValue: 'dang_hoat_dong',
        options: [
          { label: '\u0110ang ho\u1ea1t \u0111\u1ed9ng', value: 'dang_hoat_dong' },
          { label: '\u0110ang ch\u1edd', value: 'dang_cho' },
          { label: '\u0110ang s\u1eeda ch\u1eefa', value: 'dang_sua_chua' },
          { label: 'Ng\u1eebng ho\u1ea1t \u0111\u1ed9ng', value: 'ngung_hoat_dong' },
        ],
      },
      { prop: 'loai_xe', label: 'Lo\u1ea1i xe (xe bu\xfdt 40 ch\u1ed7,...)', type: 'text' },
      { prop: 'so_cho', label: 'S\u1ed1 ch\u1ed7 ng\u1ed3i', type: 'number' },
      { prop: 'nam_san_xuat', label: 'N\u0103m s\u1ea3n xu\u1ea5t', type: 'number' },
      { prop: 'ngay_bat_dau_van_hanh', label: 'Ng\xe0y b\u1eaft \u0111\u1ea7u v\u1eadn h\xf0nh', type: 'text' },
      { prop: 'ghi_chu', label: 'Ghi ch\xfa', type: 'textarea' },
    ],
  },
  tuyenXes: {
    title: 'Tuy\u1ebfn xe',
    endpoint: '/admin/van-hanh/tuyen-xes',
    extraActions: [
      { key: 'view-stops', label: 'Chi ti\u1ebft', type: 'success' },
      { key: 'add-route-detail', label: 'Th\u00eam Chi Ti\u1ebft L\u1ed9 Tr\u00ecnh', type: 'primary' },
    ],
    columns: [
      { prop: 'id', label: 'ID', width: 80 },
      { prop: 'ma_tuyen', label: 'M\u00e3 tuy\u1ebfn', width: 120, align: 'center' },
      { prop: 'ten_tuyen', label: 'T\u00ean tuy\u1ebfn', minWidth: 260 },
      { prop: 'loai_tuyen.ten_loai_tuyen', label: 'Lo\u1ea1i tuy\u1ebfn', minWidth: 150 },
      { prop: 'trang_thai_tuyen.ten_trang_thai', label: 'Tr\u1ea1ng th\u00e1i', minWidth: 150 },
      { prop: 'don_vi_van_hanh.ten_don_vi', label: '\u0110\u01a1n v\u1ecb v\u1eadn h\u00e0nh', minWidth: 220 },
    ],
    formFields: [
      { prop: 'ma_tuyen', label: 'M\u00e3 tuy\u1ebfn', type: 'text', required: true },
      { prop: 'ten_tuyen', label: 'T\u00ean tuy\u1ebfn', type: 'text', required: true },
      { prop: 'ten_tuyen_cu', label: 'T\u00ean c\u0169', type: 'text' },
      { prop: 'loai_tuyen_id', label: 'Lo\u1ea1i tuy\u1ebfn', type: 'select', required: true, optionsEndpoint: '/admin/danh-muc/loai-tuyens', optionLabel: 'ten_loai_tuyen', optionValue: 'id' },
      { prop: 'trang_thai_tuyen_id', label: 'Tr\u1ea1ng th\u00e1i tuy\u1ebfn', type: 'select', required: true, optionsEndpoint: '/admin/danh-muc/trang-thai-tuyens', optionLabel: 'ten_trang_thai', optionValue: 'id' },
      { prop: 'don_vi_van_hanh_id', label: '\u0110\u01a1n v\u1ecb v\u1eadn h\u00e0nh', type: 'select', optionsEndpoint: '/admin/danh-muc/don-vi-van-hanhs', optionLabel: 'ten_don_vi', optionValue: 'id' },
      { prop: 'diem_dau', label: '\u0110i\u1ec3m \u0111\u1ea7u', type: 'text', required: true },
      { prop: 'diem_cuoi', label: '\u0110i\u1ec3m cu\u1ed1i', type: 'text', required: true },
      { prop: 'thoi_gian_bat_dau_hoat_dong', label: 'B\u1eaft \u0111\u1ea7u (H:i:s)', type: 'text' },
      { prop: 'thoi_gian_ket_thuc_hoat_dong', label: 'K\u1ebft th\u00fac (H:i:s)', type: 'text' },
      { prop: 'tan_suat_phut', label: 'T\u1ea7n su\u1ea5t (ph\u00fat)', type: 'number' },
      { prop: 'gia_ve_luot', label: 'Gi\u00e1 v\u00e9 l\u01b0\u1ee3t', type: 'number' },
      { prop: 'ngay_bat_dau_van_hanh', label: 'Ng\u00e0y v\u1eadn h\u00e0nh (Y-m-d)', type: 'text' },
      { prop: 'nguon_du_lieu', label: 'Ngu\u1ed3n d\u1eef li\u1ec7u', type: 'text' },
      { prop: 'ghi_chu', label: 'Ghi ch\u00fa', type: 'textarea' },
    ],
  },
  loTrinhTuyens: {
    title: 'L\u1ed9 tr\u00ecnh tuy\u1ebfn',
    endpoint: '/admin/van-hanh/lo-trinh-tuyens',
    columns: [
      { prop: 'id', label: 'ID', width: 80 },
      { prop: 'tuyen_xe.ma_tuyen', label: 'M\u00e3 tuy\u1ebfn', width: 120, align: 'center' },
      {
        prop: 'chieu',
        label: 'Chi\u1ec1u',
        width: 100,
        cellType: 'button',
        buttonWidth: '88px',
        align: 'center',
        valueMap: {
          di: '\u0110i',
          ve: 'V\u1ec1',
        },
        buttonTypeMap: {
          di: 'success',
          ve: 'warning',
        },
      },
      { prop: 'mo_ta_lo_trinh', label: 'M\u00f4 t\u1ea3', minWidth: 400 },
    ],
    formFields: [
      { prop: 'tuyen_xe_id', label: 'Tuy\u1ebfn xe', type: 'select', required: true, optionsEndpoint: '/admin/van-hanh/tuyen-xes', optionLabel: 'ten_tuyen', optionValue: 'id' },
      {
        prop: 'chieu',
        label: 'Chi\u1ec1u',
        type: 'select',
        required: true,
        options: [
          { label: '\u0110i', value: 'di' },
          { label: 'V\u1ec1', value: 've' },
        ],
      },
      { prop: 'mo_ta_lo_trinh', label: 'M\u00f4 t\u1ea3 l\u1ed9 tr\u00ecnh', type: 'textarea', required: true },
    ],
  },
  giaVeTuyens: {
    title: 'Gi\u00e1 v\u00e9 tuy\u1ebfn',
    endpoint: '/admin/van-hanh/gia-ve-tuyens',
    columns: [
      { prop: 'id', label: 'ID', width: 80 },
      { prop: 'tuyen_xe.ma_tuyen', label: 'M\u00e3 tuy\u1ebfn', width: 120, align: 'center' },
      { prop: 'tuyen_xe.loai_tuyen.ten_loai_tuyen', label: 'Lo\u1ea1i tuy\u1ebfn', minWidth: 180 },
      { prop: 'loai_gia_ve', label: 'Nh\u00f3m gi\u00e1 v\u00e9', minWidth: 180 },
      { prop: 'so_tien', label: 'S\u1ed1 ti\u1ec1n', width: 140 },
      { prop: 'don_vi_tien_te', label: 'Ti\u1ec1n t\u1ec7', width: 110 },
      { prop: 'ghi_chu', label: 'Ghi ch\u00fa', minWidth: 200 },
    ],
    formFields: [
      {
        prop: 'tuyen_xe_id',
        label: 'Tuy\u1ebfn xe',
        type: 'select',
        required: true,
        optionsEndpoint: '/admin/van-hanh/tuyen-xes',
        optionLabel: 'ten_tuyen',
        optionValue: 'id',
        optionFormatter: (item) => {
          const tenTuyen = item?.ten_tuyen || `#${item?.id || ''}`;
          const loaiTuyen = item?.loai_tuyen?.ten_loai_tuyen;
          return loaiTuyen ? `${tenTuyen} - ${loaiTuyen}` : tenTuyen;
        },
      },
      {
        prop: 'loai_gia_ve',
        label: 'Nh\u00f3m gi\u00e1 v\u00e9',
        type: 'select',
        required: true,
        options: [
          { label: 'V\u00e9 l\u01b0\u1ee3t', value: 'V\u00e9 l\u01b0\u1ee3t' },
          { label: 'V\u00e9 th\u00e1ng', value: 'V\u00e9 th\u00e1ng' },
          { label: 'H\u1ecdc sinh - sinh vi\u00ean', value: 'H\u1ecdc sinh - sinh vi\u00ean' },
          { label: 'Ng\u01b0\u1eddi cao tu\u1ed5i', value: 'Ng\u01b0\u1eddi cao tu\u1ed5i' },
          { label: 'Mi\u1ec5n ph\u00ed', value: 'Mi\u1ec5n ph\u00ed' },
        ],
        placeholder: 'Ch\u1ecdn nh\u00f3m gi\u00e1 v\u00e9',
      },
      { prop: 'so_tien', label: 'S\u1ed1 ti\u1ec1n', type: 'number', required: true },
      { prop: 'don_vi_tien_te', label: '\u0110\u01a1n v\u1ecb ti\u1ec1n t\u1ec7', type: 'text' },
      { prop: 'ghi_chu', label: 'Ghi ch\u00fa', type: 'text' },
    ],
  },
};
