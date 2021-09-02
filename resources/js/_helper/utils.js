/**
 * INPUT
      "[
        {""id"": ""5c5030b9a1ccb11fe8c321f4"", ""quantity"": 1},
        {""id"": ""344430b94t4t34rwefewfdff"", ""quantity"": 5},
        {""id"": ""342343343t4t34rwefewfd53"", ""quantity"": 3}
      ]"
      "[
        {""id"": ""5c5030b9a1ccb11fe8c321f4"", ""quantity"": 2},
        {""id"": ""344430b94t4t34rwefewfdff"", ""quantity"": 1}
      ]"
    OUTPUT
      "[
        {""id"": ""5c5030b9a1ccb11fe8c321f4"", ""quantity"": 3},
        {""id"": ""344430b94t4t34rwefewfdff"", ""quantity"": 6},
        {""id"": ""342343343t4t34rwefewfd53"", ""quantity"": 3}
      ]"
 * @param {*} arr1
 * @param {*} arr2
 * @returns
 */
export function _mergeArrayByKey(arr1, arr2) {
  return Object.values([...arr1, ...arr2].reduce((acc, cur) => {
    acc[cur['date']] = {
      date: cur['date'],
      count_order: (acc[cur['date']] ? Number(acc[cur['date']]['count_order']) : 0) + (Number(cur['count_order']) || 0),
      count_cancel: (acc[cur['date']] ? Number(acc[cur['date']]['count_cancel']) : 0) + (Number(cur['count_cancel']) || 0),
      count_cost: (acc[cur['date']] ? Number(acc[cur['date']]['count_cost']) : 0) + (Number(cur['count_cost']) || 0),
      total_revenue: (acc[cur['date']] ? Number(acc[cur['date']]['total_revenue']) : 0) + (Number(cur['total_revenue']) || 0),
      total_cancel: (acc[cur['date']] ? Number(acc[cur['date']]['total_cancel']) : 0) + (Number(cur['total_cancel']) || 0),
      total_cost: (acc[cur['date']] ? Number(acc[cur['date']]['total_cost']) : 0) + (Number(cur['total_cost']) || 0)
    };

    acc[cur['date']].total_profit = Number(acc[cur['date']].total_revenue) - Number(acc[cur['date']].total_cancel) - Number(acc[cur['date']].total_cost)
    acc[cur['date']].total_net_revenue = Number(acc[cur['date']].total_revenue) - Number(acc[cur['date']].total_cancel)

    return acc;
  }, {}));
}

export function _formatNumber(yourNumber) {
  try {
    return new Intl.NumberFormat('en-US', { style: 'decimal' }).format(Number(yourNumber))
  } catch {
    return yourNumber
  }
}
