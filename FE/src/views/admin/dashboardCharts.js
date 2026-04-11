// ECharts options for dashboard
export const kpiBarOptions = (metrics, sourceMap) => ({
  tooltip: { trigger: 'axis' },
  xAxis: {
    type: 'category',
    data: sourceMap.map((item) => item.label),
    axisLabel: { rotate: 20 },
  },
  yAxis: { type: 'value' },
  series: [
    {
      data: sourceMap.map((item) => metrics[item.key]),
      type: 'bar',
      itemStyle: {
        color: {
          type: 'linear',
          x: 0,
          y: 0,
          x2: 1,
          y2: 1,
          colorStops: [
            { offset: 0, color: '#db5a20' },
            { offset: 1, color: '#f49a3f' },
          ],
        },
        borderRadius: [8, 8, 0, 0],
      },
      barWidth: '50%',
    },
  ],
});

export const kpiPieOptions = (metrics, sourceMap) => {
  const pieData = sourceMap
    .map((item) => ({
      value: Number(metrics[item.key] || 0),
      name: item.label,
    }))
    .filter((item) => item.value > 0);

  const hasData = pieData.length > 0;

  return {
    tooltip: {
      trigger: 'item',
      formatter: '{b}: {c} ({d}%)',
    },
    legend: {
      type: 'scroll',
      top: 0,
      left: 'center',
      width: '88%',
      itemWidth: 22,
      itemHeight: 12,
      textStyle: {
        fontSize: 13,
      },
    },
    series: [
      {
        name: 'T\u1ef7 tr\u1ecdng',
        type: 'pie',
        radius: ['42%', '68%'],
        center: ['50%', '62%'],
        avoidLabelOverlap: true,
        itemStyle: {
          borderRadius: 10,
          borderColor: '#fff',
          borderWidth: 2,
        },
        label: {
          show: false,
          position: 'center',
        },
        emphasis: {
          label: {
            show: true,
            fontSize: 16,
            fontWeight: 'bold',
            formatter: '{b}\n{d}%',
          },
        },
        labelLine: { show: false },
        data: hasData
          ? pieData
          : [
              {
                value: 1,
                name: 'Ch\u01b0a c\u00f3 d\u1eef li\u1ec7u',
                itemStyle: { color: '#d9d9d9' },
              },
            ],
      },
    ],
  };
};
