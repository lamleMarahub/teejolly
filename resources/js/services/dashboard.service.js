function getStatistics(queryString) {
  return window.axios.get(`/dashboard/getStatistics?${queryString}`)
}

function getSellerList() {
  return window.axios.get(`/dashboard/getSellerList`)
}

export {
  getStatistics,
  getSellerList
}
