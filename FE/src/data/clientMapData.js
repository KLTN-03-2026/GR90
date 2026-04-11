export const clientMapRoutes = [
  {
    id: 'dnr15',
    code: 'DNR15',
    name: 'Bến xe Trung tâm - Thọ Quang',
    type: 'Tuyến buýt không trợ giá',
    note: 'Tuyến mẫu hiển thị trung tâm thành phố và khu vực bán đảo Sơn Trà.',
    directions: {
      di: [
        { id: 'dnr15-di-1', name: 'Bến xe Trung tâm', address: 'Đối diện 75 Nguyễn Sáng', coords: [16.0549, 108.1752] },
        { id: 'dnr15-di-2', name: 'Điện Biên Phủ', address: '734 Điện Biên Phủ', coords: [16.0598, 108.1896] },
        { id: 'dnr15-di-3', name: 'Cầu Rồng', address: 'Đường Trần Hưng Đạo', coords: [16.0614, 108.2274] },
        { id: 'dnr15-di-4', name: 'Ngô Quyền', address: 'Ngã tư Ngô Quyền - Vương Thừa Vũ', coords: [16.0674, 108.2398] },
        { id: 'dnr15-di-5', name: 'Thọ Quang', address: 'Khu vực cảng cá Thọ Quang', coords: [16.0942, 108.2459] },
      ],
      ve: [
        { id: 'dnr15-ve-1', name: 'Thọ Quang', address: 'Khu vực cảng cá Thọ Quang', coords: [16.0942, 108.2459] },
        { id: 'dnr15-ve-2', name: 'Ngô Quyền', address: 'Đối diện số 551 Ngô Quyền', coords: [16.071, 108.2391] },
        { id: 'dnr15-ve-3', name: 'Cầu Sông Hàn', address: 'Đường Trần Hưng Đạo', coords: [16.0672, 108.226] },
        { id: 'dnr15-ve-4', name: 'Điện Biên Phủ', address: '522 Điện Biên Phủ', coords: [16.061, 108.1975] },
        { id: 'dnr15-ve-5', name: 'Bến xe Trung tâm', address: '75 Nguyễn Sáng', coords: [16.0549, 108.1752] },
      ],
    },
  },
  {
    id: 'dn11',
    code: 'DN11',
    name: 'Xuân Diệu - Bệnh viện Phụ Sản Nhi',
    type: 'Tuyến buýt không trợ giá',
    note: 'Phù hợp cho hành khách di chuyển từ khu vực ven sông tới trung tâm y tế.',
    directions: {
      di: [
        { id: 'dn11-di-1', name: 'Xuân Diệu', address: 'Công viên ven sông', coords: [16.0851, 108.2194] },
        { id: 'dn11-di-2', name: 'Ông Ích Khiêm', address: 'Đối diện chợ Cồn', coords: [16.067, 108.2131] },
        { id: 'dn11-di-3', name: 'Hàm Nghi', address: 'Gần công viên 29/3', coords: [16.0535, 108.2125] },
        { id: 'dn11-di-4', name: 'Bệnh viện Phụ Sản Nhi', address: '402 Lê Văn Hiến', coords: [16.0311, 108.2453] },
      ],
      ve: [
        { id: 'dn11-ve-1', name: 'Bệnh viện Phụ Sản Nhi', address: '402 Lê Văn Hiến', coords: [16.0311, 108.2453] },
        { id: 'dn11-ve-2', name: 'Ngũ Hành Sơn', address: 'Ngã tư Hồ Xuân Hương', coords: [16.0404, 108.2444] },
        { id: 'dn11-ve-3', name: 'Ông Ích Khiêm', address: 'Ngã ba Hùng Vương', coords: [16.0664, 108.2118] },
        { id: 'dn11-ve-4', name: 'Xuân Diệu', address: 'Bến xe buýt ven sông Hàn', coords: [16.0851, 108.2194] },
      ],
    },
  },
  {
    id: 'dnr6a',
    code: 'DNR6A',
    name: 'Bến xe Trung tâm - Khu du lịch Non Nước',
    type: 'Tuyến buýt du lịch',
    note: 'Tuyến kết nối khu vực trung tâm với cụm danh thắng phía Nam thành phố.',
    directions: {
      di: [
        { id: 'dnr6a-di-1', name: 'Bến xe Trung tâm', address: '75 Nguyễn Sáng', coords: [16.0549, 108.1752] },
        { id: 'dnr6a-di-2', name: 'Cầu Rồng', address: 'Đầu cầu phía Đông', coords: [16.0614, 108.2274] },
        { id: 'dnr6a-di-3', name: 'Biển Mỹ Khê', address: 'Võ Nguyên Giáp', coords: [16.0567, 108.2473] },
        { id: 'dnr6a-di-4', name: 'Khu du lịch Non Nước', address: 'Trường Sa - Ngũ Hành Sơn', coords: [15.9979, 108.2648] },
      ],
      ve: [
        { id: 'dnr6a-ve-1', name: 'Khu du lịch Non Nước', address: 'Trường Sa - Ngũ Hành Sơn', coords: [15.9979, 108.2648] },
        { id: 'dnr6a-ve-2', name: 'Biển Mỹ Khê', address: 'Đối diện công viên Biển Đông', coords: [16.0676, 108.2455] },
        { id: 'dnr6a-ve-3', name: 'Cầu Rồng', address: 'Đường 2/9', coords: [16.0585, 108.2247] },
        { id: 'dnr6a-ve-4', name: 'Bến xe Trung tâm', address: '75 Nguyễn Sáng', coords: [16.0549, 108.1752] },
      ],
    },
  },
];

export function buildSearchItems(routes = clientMapRoutes) {
  const routeItems = routes.map((route) => ({
    id: `route-${route.id}`,
    type: 'route',
    routeId: route.id,
    code: route.code,
    title: `${route.code} - ${route.name}`,
    subtitle: route.type,
    note: route.note,
  }));

  const stopItems = routes.flatMap((route) =>
    Object.entries(route.directions).flatMap(([directionKey, stops]) =>
      stops.map((stop, index) => ({
        id: `stop-${stop.id}`,
        type: 'stop',
        routeId: route.id,
        stopId: stop.id,
        directionKey,
        title: stop.name,
        subtitle: `${route.code} • ${directionKey === 've' ? 'Chiều về' : 'Chiều đi'}`,
        note: stop.address,
        order: index + 1,
      }))
    )
  );

  return [...routeItems, ...stopItems];
}
