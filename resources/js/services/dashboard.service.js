function getStatistics(queryString) {
  return window.axios.get(`/dashboard/getStatistics?${queryString}`)
}

export {
  getStatistics
}
