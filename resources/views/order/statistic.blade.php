@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">business report from <strong> {{date('d/m/Y',strtotime($startDate))}} </strong> to <strong> {{date('d/m/Y',strtotime($endDate))}} </strong></div>
                <div class="card-body">
                    <form action="" method="GET" class="form-inline">
                        @csrf
                        <input type="text" class="form-control" value="" id="reportrange" name="reportrange">
                        @if (Auth::user()->id == 1)
                        <div class="btn-group btn-group-sm ml-2 float-left" role="group">                    
                            <select id="owner_id" name="owner_id" class="btn form-control border bg-light" style="width:auto;">
                                <option value="0" @if ($owner_id == 0) ? selected : "" @endif>-all sellers-</option>
                                @foreach ($users as $u)
                                @if ($u->isActive() && !$u->isDeleted() && $u->isSeller())
                                <option value="{{ $u->id }}" @if ($owner_id == $u->id) ? selected : '' @endif>{{ $u->name }}</option>
                                @endif
                                @endforeach
                            </select>   
                        </div>
                        @endif
                        <button class="btn btn-light ml-2 float-right" type="submit">submit</button>
                    </form>
                    
                    <canvas id="myChart"></canvas>

                    <div class="row text-center" style="padding-top:10px">
                        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                            <div class="">
                                <h2><span class="counter" id="orders"></span></h2>
                                <p>total orders</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                            <div class="">
                                <h2><span class="counter" id="orders_unit"></span></h2>
                                <p>total units</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                            <div class="">
                                <h2><span class="counter" id="revenues">40,000</span></h2>
                                <p>total revenues</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                            <div class="text-danger">
                                <h2><span class="counter" id="costs"></span></h2>
                                <p>total costs</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>

<script type="text/javascript">

var order = {!! $orders !!};
var orders = [];
var orderTotal = 0;
$.each(order, function( index, obj ) {
    orders[obj.date] = parseInt(obj.total);
    orderTotal += parseInt(obj.total);
});

var cost = {!! $costs !!};
var costs = [];
var costTotal = 0;
$.each(cost, function( index, obj ) {
    costs[obj.date] = parseFloat(obj.total);
    costTotal += parseFloat(obj.total);
});

var revenue = {!! $revenues !!};
var revenues = [];
var revenueTotal = 0;
$.each(revenue, function( index, obj ) {
    revenues[obj.date] = parseFloat(obj.total);
    revenueTotal += parseFloat(obj.total);
});

$('#orders').html(orderTotal);
$('#orders_unit').html({!! $orders_units !!});
$('#revenues').html(new Intl.NumberFormat('us-US', { style: 'currency', currency: 'USD' }).format(revenueTotal*0.8));
$('#costs').html(new Intl.NumberFormat('us-US', { style: 'currency', currency: 'USD' }).format(costTotal));

$(function() {

    var start = moment().subtract(6, 'days');
    var end = moment();

    function cb(start, end) {
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    }

    $('#reportrange').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
           'Today': [moment(), moment()],
           'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           'Last 7 Days': [moment().subtract(6, 'days'), moment()],
           'Last 30 Days': [moment().subtract(29, 'days'), moment()],
           'This Month': [moment().startOf('month'), moment().endOf('month')],
           'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
    }, cb);

    cb(start, end);
});

var ctx = document.getElementById('myChart').getContext('2d');
var chart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'line',
    // The data for our dataset
    data: {
        labels: Object.keys(orders),
        datasets: [{
            label: 'Orders',
            fill: false,
            backgroundColor: '#34a5eb',
            borderColor: '#34a5eb',
            data: Object.values(orders),
        },
        {
            label: 'Revenue',
            fill: false,
            backgroundColor: '#47c2c0',
            borderColor: '#47c2c0',
            data: Object.values(revenues),
        },
        // {
        //     label: 'Cost',
        //     fill: false,
        //     backgroundColor: '#ffca57',
        //     borderColor: '#ffca57',
        //     data: Object.values(costs),
        // }
        ],
    },

    // Configuration options go here
    // options: {}
    options: {
        responsive: true,
		title: {
			display: true,
			text: 'Amazon Orders Report'
		},
		tooltips: {
			mode: 'index',
			intersect: false,
		},
		hover: {
			mode: 'nearest',
			intersect: true
		},
		scales: {
			xAxes: [{
				display: true,
				scaleLabel: {
					display: true,
					labelString: 'Date'
				}
			}],
			yAxes: [{
				display: true,
				scaleLabel: {
					display: true,
					labelString: 'Value'
				}
			}]
		}
    }
});


</script>

@endsection
