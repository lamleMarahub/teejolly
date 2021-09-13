import React from 'react';
import { Bar } from 'react-chartjs-2';

/**
 *
 * @param {*} props.datas
 * @returns
 */
const DesignChart = (props) => {
  const config = {
    options: {
      responsive: true,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      stacked: false,
      plugins: {
        title: {
          display: true,
          text: 'Design - Credit'
        }
      },
      scales: {
        y: {
          type: 'linear',
          display: true,
          position: 'left',
        },
        y1: {
          type: 'linear',
          display: true,
          position: 'right',
          // grid line settings
          grid: {
            drawOnChartArea: false, // only want the grid lines for one axis to show up
          },
        }
      }
    },
  };

  const data = {
    labels: props.datas.map(item => item.date),//['1', '2', '3', '4', '5', '6'],
    datasets: [
      {
        type: 'line',
        label: 'Credit',
        data: props.datas.map(item => item.total_credit), //[12, 19, 3, 5, 2, 3],
        backgroundColor: 'rgb(54, 162, 235)',
        borderColor: 'rgba(54, 162, 235, 0.6)',
        yAxisID: 'y1',
        lineTension: 0.3,
      },
      {
        label: 'Design',
        data: props.datas.map(item => item.count_design), //[1, 2, 1, 1, 2, 2],
        backgroundColor: 'rgb(7, 219, 12)',
        borderColor: 'rgba(7, 219, 12, 0.6)',
        yAxisID: 'y', //'y1',
        lineTension: 0.3,
      }
    ]
  };

  return (
    <Bar data={data} options={config.options} />
  )
}

export default React.memo(DesignChart)
