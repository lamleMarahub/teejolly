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
                                <option value="0" @if ($owner_id == 0) ? selected : "" @endif>-all designers-</option>
                                @foreach ($users as $u)
                                <option value="{{ $u->id }}" @if ($owner_id == $u->id) ? selected : '' @endif>{{ $u->name }}</option>
                                @endforeach
                            </select>   
                        </div>
                        @endif
                        <button class="btn btn-light ml-2 float-right" type="submit">submit</button>
                    </form>
                    <canvas id="myChart"></canvas>
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
var design = {!! $designs !!};
var designs = [];
var designTotal = 0;
$.each(design, function( index, obj ) {
    designs[obj.date] = parseInt(obj.total);
    designTotal += parseInt(obj.total);
});

// $('#designs').html(designTotal);

var art_work = {!! $art_works !!};
var art_works = [];
var artTotal = 0;
$.each(art_work, function( index, obj ) {
    art_works[obj.date] = parseInt(obj.total);
    artTotal += parseInt(obj.total);
});


var credit = {!! $credits !!};
var credits = [];
var creditTotal = 0;
$.each(credit, function( index, obj ) {
    credits[obj.date] = parseInt(obj.total);
    creditTotal += parseInt(obj.total);
});

// $('#art_works').html(artTotal);

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
        labels: Object.keys(designs),
        datasets: [{
            label: 'Mockups',
            fill: false,
            backgroundColor: '#fb6483',
            borderColor: '#fb6483',
            data: Object.values(designs),
        },
        {
            label: 'ArtWorks',
            fill: false,
            backgroundColor: '#47c2c0',
            borderColor: '#47c2c0',
            data: Object.values(art_works),
        }],
    },

    // Configuration options go here
    // options: {}
    options: {
        responsive: true,
		title: {
			display: true,
			text: 'Designs Report ('+ designTotal +' designs include '+artTotal+' artworks or '+creditTotal+' credits)'
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
