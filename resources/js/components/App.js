import { addDays, endOfDay, endOfMonth, format, startOfDay, startOfMonth } from 'date-fns';
import React, { useEffect, useState } from 'react';
import DatePicker from 'react-datepicker';
import 'react-datepicker/dist/react-datepicker.css';
import ReactDOM from 'react-dom';
import { Controller, useForm } from 'react-hook-form';
import { BrowserRouter } from 'react-router-dom';
import { DATE_FORMAT, DATE_RANGE } from '../constants/app.constant';
import * as dashboardService from '../services/dashboard.service';
import { _formatNumber, _mergeArrayByKey } from '../_helper/utils';
import './App.scss';
import DesignChart from './chart/design-chart.component';
import OrderRevenueChart from './chart/order-revenue-chart.component';

const App = () => {
  const { register, control, handleSubmit, reset, getValues } = useForm()
  const [timeLabel, settimeLabel] = useState('Today')
  const [loading, setLoading] = useState(false)
  const [sellerList, setSellerList] = useState([])
  const [statistic, setStatistic] = useState({
    datas: [],
    top_products: [],
    count_order: 0,
    count_cancel: 0,
    count_cost: 0,
    total_revenue: 0,
    total_cancel: 0,
    total_cost: 0,
    total_profit: 0,
    cost_amazon: 0,
    cost_etsy: 0,
    amazon_statistics: [],
    etsy_statistics: [],
    design_statistics: [],
    user_credit_statistics: [],
    amazon_count_cost: 0,
    etsy_count_cost: 0,
  })

  useEffect(() => {
    reset({ platform: '' })
    changeDateRange(DATE_RANGE.TODAY)
    loadSellerList().then(res => {
      loadData(getValues())
    })
  }, [])

  const loadSellerList = () => {
    return dashboardService.getSellerList().then(res => {
      // console.log(res.data.data.sellers)
      setSellerList(res.data.data.sellers)
      return res
    })
  }

  const loadData = (formdata) => {
    const postdata = {
      ...formdata,
      date_from: format(formdata.date_from, DATE_FORMAT.yyyyMMdd),
      date_to: format(formdata.date_to, DATE_FORMAT.yyyyMMdd)
    }
    const queryString = new URLSearchParams(postdata).toString()

    setLoading(true)

    dashboardService.getStatistics(queryString).then(res => {
      const datas = _mergeArrayByKey(res.data.data.amazon_statistics, res.data.data.etsy_statistics)
      const cost_amazon = res.data.data.amazon_statistics.reduce((total, cur) => total + Number(cur.total_cost), 0)
      const cost_etsy = res.data.data.etsy_statistics.reduce((total, cur) => total + Number(cur.total_cost), 0)

      // console.log('datas=', datas)

      setStatistic({
        datas,
        top_products: res.data.data.top_products,
        count_order: datas.reduce((total, cur) => total + Number(cur.count_order), 0),
        count_cancel: datas.reduce((total, cur) => total + Number(cur.count_cancel), 0),
        count_cost: datas.reduce((total, cur) => total + Number(cur.count_cost), 0),
        total_revenue: datas.reduce((total, cur) => total + Number(cur.total_revenue), 0),
        total_cancel: datas.reduce((total, cur) => total + Number(cur.total_cancel), 0),
        total_cost: datas.reduce((total, cur) => total + Number(cur.total_cost), 0),
        total_profit: datas.reduce((total, cur) => total + Number(cur.total_profit), 0),
        cost_amazon,
        cost_etsy,
        amazon_statistics: res.data.data.amazon_statistics,
        etsy_statistics: res.data.data.etsy_statistics,
        design_statistics: res.data.data.design_statistics,
        user_credit_statistics: res.data.data.user_credit_statistics,
        amazon_count_cost: res.data.data.amazon_statistics.reduce((total, cur) => total + Number(cur.count_cost), 0),
        etsy_count_cost: res.data.data.etsy_statistics.reduce((total, cur) => total + Number(cur.count_cost), 0),
      })

      setLoading(false)
    }).catch(err => {
      console.log('err=', err)
      setLoading(false)
    })
  }

  const handleFilter = (formdata) => {
    loadData(formdata)
  }

  const changeDateRange = (dateRangeType) => {
    let date_from
    let date_to

    switch (dateRangeType) {
      case DATE_RANGE.TODAY:
        date_from = startOfDay(new Date())
        date_to = endOfDay(date_from)
        settimeLabel('Today')
        break;
      case DATE_RANGE.YESTERDAY:
        date_from = startOfDay(new Date())
        date_from = addDays(date_from, -1)
        date_to = endOfDay(date_from)
        settimeLabel('Yesterday')
        break;
      case DATE_RANGE.LAST_7_DAYS:
        date_to = endOfDay(new Date())
        date_from = startOfDay(addDays(date_to, -6))
        settimeLabel('Last 7 days')
        break;
      case DATE_RANGE.LAST_30_DAYS:
        date_to = endOfDay(new Date())
        date_from = startOfDay(addDays(date_to, -29))
        settimeLabel('Last 30 days')
        break;
      case DATE_RANGE.THIS_MONTH:
        date_from = startOfMonth(new Date())
        date_to = endOfMonth(date_from)
        settimeLabel('This month')
        break;
      case DATE_RANGE.LAST_MONTH:
        date_from = startOfMonth(new Date())
        date_to = endOfDay(addDays(date_from, -1))
        date_from = startOfMonth(date_to)
        settimeLabel('Last month')
        break;
      default:
        break;
    }

    reset({
      date_from,
      date_to
    })
  }

  return (
    <BrowserRouter>
      <div className="container">
        <div className="row">
          <div className="px-0 col-lg-9 col-md-8 left-zone">
            <div className='row'>
              <div className='col-auto px-0'>
                <div className='row'>
                  <label className="form-label">Platform</label>
                  <select className="form-control" {...register('platform')}>
                    <option value="">-- All --</option>
                    <option value="amazon">Amazon</option>
                    <option value="etsy">Etsy</option>
                  </select>
                </div>
                {sellerList.length > 0 && <div className='row'>
                  <label className="form-label">Seller</label>
                  <select className="form-control" {...register('user_id')}>
                    <option value="">-- All --</option>
                    {
                      sellerList.map((item, idx) =>
                        <option key={idx} value={item.id}>{item.name}</option>
                      )
                    }
                  </select>
                </div>}
              </div>
              <div className='col-md-8 px-0 px-lg-2'>
                <div className='row'>
                  <div className='col pl-0 pr-1'>
                    <label className="form-label">From</label>
                    <Controller
                      name='date_from'
                      control={control}
                      render={({ field }) => <DatePicker
                        {...field}
                        autoComplete="off"
                        className="form-control"
                        dateFormat="dd/MM/yyyy"
                        selected={field.value}
                        onChange={data => { field.onChange(data); settimeLabel('custom') }}
                      />}
                    />
                  </div>
                  <div className='col pr-0 pl-1'>
                    <label className="form-label">To</label>
                    <Controller
                      name='date_to'
                      control={control}
                      render={({ field }) => <DatePicker
                        {...field}
                        autoComplete="off"
                        className="form-control"
                        dateFormat="dd/MM/yyyy"
                        selected={field.value}
                        onChange={data => { field.onChange(data); settimeLabel('custom') }}
                      />}
                    />
                  </div>
                </div>
                <div className='row'>
                  <div className='col'>
                    <span className='pointer text-danger' onClick={e => changeDateRange(DATE_RANGE.TODAY)}>Today</span>
                    <span className='ml-3 pointer text-danger' onClick={e => changeDateRange(DATE_RANGE.YESTERDAY)}>Yesterday</span>
                    <span className='ml-3 pointer text-danger' onClick={e => changeDateRange(DATE_RANGE.LAST_7_DAYS)}>Last 7 days</span>
                    <span className='ml-3 pointer text-danger' onClick={e => changeDateRange(DATE_RANGE.LAST_30_DAYS)}>Last 30 days</span>
                    <span className='ml-3 pointer text-danger' onClick={e => changeDateRange(DATE_RANGE.THIS_MONTH)}>This month</span>
                    <span className='ml-3 pointer text-danger' onClick={e => changeDateRange(DATE_RANGE.LAST_MONTH)}>Last month</span>
                  </div>
                </div>
              </div>

              <div className='col-auto px-0 px-md-2'>
                <label className="form-label">&nbsp;</label>
                <button type="button" className="form-control btn btn-primary" disabled={loading} onClick={handleSubmit(handleFilter)}>Go</button>
              </div>
            </div>

            <div className='order-chart'>
              <OrderRevenueChart datas={statistic.datas} />
            </div>
            <div className='summary'>

            </div>
            <div className='design-chart'>
              <DesignChart datas={statistic.design_statistics} />
            </div>
          </div>
          <div className="pr-0 col-lg-3 col-md-4 right-zone">
            <div className='row justify-content-between'>
              <h4 className='col px-0'>Total sales</h4>
              <div className='col px-0 text-right'>{timeLabel}</div>
            </div>
            <div className='row justify-content-between'>
              <div className='col px-0'>Total Sales({statistic.count_order}):</div>
              <div className='col px-0 text-right'>${_formatNumber(statistic.total_revenue)}</div>
            </div>
            <div className='row justify-content-between'>
              <div className='col px-0'>Sales Deduction({statistic.count_cancel}):</div>
              <div className='col px-0 text-right'>${_formatNumber(statistic.total_cancel)}</div>
            </div>
            <div className='row justify-content-between'>
              <div className='col px-0'>Costs({statistic.count_cost}):</div>
              <div className='col px-0 text-right'>${_formatNumber(statistic.total_cost)}</div>
            </div>
            <div className='row justify-content-between'>
              <div className='col px-0'>Profit:</div>
              <div className='col px-0 text-right font-weight-bold'>${_formatNumber(statistic.total_profit)}</div>
            </div>

            <div className='row justify-content-between border-primary border-top pt-2 mt-2'>
              <h4 className='col px-0'>Costs:</h4>
              <div className='col px-0 text-right'>{timeLabel}</div>
            </div>
            <div className='row justify-content-between'>
              <div className='col px-0'>Amazon({statistic.amazon_count_cost}):</div>
              <div className='col px-0 text-right'>${_formatNumber(statistic.cost_amazon)}</div>
            </div>
            <div className='row justify-content-between'>
              <div className='col px-0'>Etsy({statistic.etsy_count_cost}):</div>
              <div className='col px-0 text-right'>${_formatNumber(statistic.cost_etsy)}</div>
            </div>
            <div className='row justify-content-between'>
              <div className='col px-0'>Total:</div>
              <div className='col px-0 text-right font-weight-bold'>${_formatNumber(statistic.cost_amazon + statistic.cost_etsy)}</div>
            </div>

            <h4 className='border-primary border-top mt-2 pt-2'>Top products:</h4>
            {statistic.top_products && statistic.top_products.map((item, idx) => (
              <div key={idx} className='row justify-content-between'>
                <div className='col px-0'>
                  <a href={`https://www.amazon.com/dp/${item.asin}`} target='_blank'>{item.asin}</a>
                </div>
                <div className='col px-0 text-right'>{item.count_product}</div>
              </div>
            ))}

            <h4 className='border-primary border-top mt-2 pt-2'>User credit:</h4>
            {statistic.user_credit_statistics && statistic.user_credit_statistics.map((item, idx) => (
              <div key={idx} className='row justify-content-between'>
                <div className='col px-0'>
                  {item.name}
                </div>
                <div className='col px-0 text-right'>{_formatNumber(item.total_credit)}</div>
              </div>
            ))}
          </div>
        </div>
      </div>
    </BrowserRouter>
  );
}

ReactDOM.render(<App />, document.getElementById('appReact'));
