@extends('layouts.master')

@section('title')
    Dashboard
@endsection

@section('breadcrumb')
    @parent
    <li class="active">Dashboard</li>
@endsection

@section('content')
<!-- Small boxes (Stat box) -->
<div class="row">
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3>{{ $category }}</h3>

                <p>Total Kategori</p>
            </div>
            <div class="icon">
                <i class="fa fa-cube"></i>
            </div>
            <a href="{{ route('kategori.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-green">
            <div class="inner">
                <h3>{{ $produk }}</h3>

                <p>Total Produk</p>
            </div>
            <div class="icon">
                <i class="fa fa-cubes"></i>
            </div>
            <a href="{{ route('produk.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-yellow">
            <div class="inner">
                <h3>{{ $member }}</h3>

                <p>Total Member</p>
            </div>
            <div class="icon">
                <i class="fa fa-id-card"></i>
            </div>
            <a href="{{ route('member.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-3 col-xs-6">
        <!-- small box -->
        <div class="small-box bg-red">
            <div class="inner">
                <h3>{{ $supplier }}</h3>

                <p>Total Supplier</p>
            </div>
            <div class="icon">
                <i class="fa fa-truck"></i>
            </div>
            <a href="{{ route('supplier.index') }}" class="small-box-footer">Lihat <i class="fa fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <!-- ./col -->
</div>
<!-- /.row -->
<!-- Main row -->
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Grafik Pendapatan {{ tanggal_indonesia($tanggal_awal, false) }} s/d {{ tanggal_indonesia($tanggal_akhir, false) }}</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="chart">
                            <!-- Sales Chart Canvas -->
                            <canvas id="salesChart" style="height: 180px;"></canvas>
                        </div>
                        <!-- /.chart-responsive -->
                    </div>
                </div>
                <!-- /.row -->
            </div>
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
<!-- Chart Section -->
<div class="row">
<div class="col-lg-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Hari Paling Ramai</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="chart">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Hari Penjualan</th>
                                        <th>Total Penjualan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($penjualanTerbanyakPerHari as $penjualan)
                                    <tr>
                                        <td>{{ $penjualan->hari_penjualan }}</td>
                                        <td>{{ $penjualan->total_penjualan }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <!-- /.chart-responsive -->
                    </div>
                </div>
                <!-- /.row -->
            </div>
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
    <div class="col-lg-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Kasir dengan Penjualan Terbanyak</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-bo">
                <canvas id="kasirTerbanyakChart" style="height: 300px;"></canvas>
            </div>
            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Kasir</th>
                            <th>Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kasirInfo as $kasir)
                        <tr>
                            <td>{{ $kasir->name }}</td>
                            <td>{{ $kasir->total_penjualan }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <div class="col-lg-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Produk Teratas</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- Canvas untuk Pie Chart -->
                <canvas id="produkTeratasChart" style="height: 300px;"></canvas>
            </div>
            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($penjualanTerbanyakPerBulan as $produk)
                        <tr>
                            <td>{{ $produk->produk->nama_produk }}</td>
                            <td>{{ $produk['total_penjualan'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <div class="col-lg-6">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Kategori Teratas Bulan Ini</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- Canvas untuk Pie Chart -->
                <canvas id="kategoriTeratasChart" style="height: 300px;"></canvas>
            </div>
            <div class="box-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Kategori</th>
                            <th>Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kategoriTerbanyakPerBulan as $kategori)
                        <tr>
                            <td>{{ $kategori->nama_kategori }}</td>
                            <td>{{ $kategori['total_penjualan'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
</div>



@endsection

@push('scripts')
<!-- ChartJS -->
<script src="{{ asset('AdminLTE-2/bower_components/chart.js/Chart.js') }}"></script>
<script>
$(function() {
    // Get context with jQuery - using jQuery's .get() method.
    var salesChartCanvas = $('#salesChart').get(0).getContext('2d');
    // This will get the first returned node in the jQuery collection.
    var salesChart = new Chart(salesChartCanvas);

    var salesChartData = {
        labels: {{ json_encode($data_tanggal) }},
        datasets: [
            {
                label: 'Pendapatan',
                fillColor           : 'rgba(60,141,188,0.9)',
                strokeColor         : 'rgba(60,141,188,0.8)',
                pointColor          : '#3b8bba',
                pointStrokeColor    : 'rgba(60,141,188,1)',
                pointHighlightFill  : '#fff',
                pointHighlightStroke: 'rgba(60,141,188,1)',
                data: {{ json_encode($data_pendapatan) }}
            }
        ]
    };

    var salesChartOptions = {
        pointDot : false,
        responsive : true
    };

    salesChart.Line(salesChartData, salesChartOptions);
});
</script>
<script>
    $(function() {
        // Get context with jQuery - using jQuery's .get() method.
        var pieChartCanvas = $('#produkTeratasChart').get(0).getContext('2d');

        // Data produk teratas Anda
        var produkLabels = {!! json_encode($produkLabels) !!};
        var produkData = {!! json_encode($produkData) !!};
        var produkColors = [
            '#f56954', // Merah
            '#00a65a', // Hijau
            '#f39c12', // Kuning
            '#00c0ef', // Biru
            '#3c8dbc', // Biru Tua
            '#d2d6de'  // Abu-Abu
        ];

        var PieData = [];
        for (var i = 0; i < produkLabels.length; i++) {
            PieData.push({
                value: produkData[i],
                color: produkColors[i],
                highlight: produkColors[i],
                label: produkLabels[i]
            });
        }

        var pieOptions = {
            //Boolean - Whether we should show a stroke on each segment
            segmentShowStroke: true,
            //String - The colour of each segment stroke
            segmentStrokeColor: '#fff',
            //Number - The width of each segment stroke
            segmentStrokeWidth: 2,
            //Number - The percentage of the chart that we cut out of the middle
            percentageInnerCutout: 50, // This is 0 for Pie charts
            //Number - Amount of animation steps
            animationSteps: 100,
            //String - Animation easing effect
            animationEasing: 'easeOutBounce',
            //Boolean - Whether we animate the rotation of the Doughnut
            animateRotate: true,
            //Boolean - Whether we animate scaling the Doughnut from the centre
            animateScale: false,
            //Boolean - whether to make the chart responsive to window resizing
            responsive: true,
            // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
            maintainAspectRatio: true,
            //String - A legend template
            legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
        };

        // Create pie chart
        var pieChart = new Chart(pieChartCanvas).Pie(PieData, pieOptions);
    });

</script>
<script>
    $(function() {
    // Get context with jQuery - using jQuery's .get() method.
    var pieChartCanvas = $('#kasirTerbanyakChart').get(0).getContext('2d');

    // Data kasir terbanyak Anda
    var kasirLabels = {!! json_encode($kasirLabels) !!};
    var kasirData = {!! json_encode($kasirData) !!};
    var kasirColors = [
        '#f56954', // Merah
        '#00a65a', // Hijau
        '#f39c12', // Kuning
        '#00c0ef', // Biru
        '#3c8dbc', // Biru Tua
        '#d2d6de'  // Abu-Abu
    ];

    var PieData = [];
    for (var i = 0; i < kasirLabels.length; i++) {
        PieData.push({
            value: kasirData[i],
            color: kasirColors[i],
            highlight: kasirColors[i],
            label: kasirLabels[i]
        });
    }

    var pieOptions = {
        //Boolean - Whether we should show a stroke on each segment
        segmentShowStroke: true,
        //String - The colour of each segment stroke
        segmentStrokeColor: '#fff',
        //Number - The width of each segment stroke
        segmentStrokeWidth: 2,
        //Number - The percentage of the chart that we cut out of the middle
        percentageInnerCutout: 50, // This is 0 for Pie charts
        //Number - Amount of animation steps
        animationSteps: 100,
        //String - Animation easing effect
        animationEasing: 'easeOutBounce',
        //Boolean - Whether we animate the rotation of the Doughnut
        animateRotate: true,
        //Boolean - Whether we animate scaling the Doughnut from the centre
        animateScale: false,
        //Boolean - whether to make the chart responsive to window resizing
        responsive: true,
        // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
        maintainAspectRatio: true,
        //String - A legend template
        legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
    };

    // Create pie chart
    var pieChart = new Chart(pieChartCanvas).Pie(PieData, pieOptions);
});

</script>
<script>
    $(function() {
        // Get context with jQuery - using jQuery's .get() method.
        var pieChartCanvas = $('#kategoriTeratasChart').get(0).getContext('2d');

        // Data kategori teratas Anda
        var kategoriLabels = {!! json_encode($kategoriLabels) !!};
        var kategoriData = {!! json_encode($kategoriData) !!};
        var kategoriColors = [
            '#f56954', // Merah
            '#00a65a', // Hijau
            '#f39c12', // Kuning
            '#00c0ef', // Biru
            '#3c8dbc', // Biru Tua
            '#d2d6de'  // Abu-Abu
        ];

        var PieData = [];
        for (var i = 0; i < kategoriLabels.length; i++) {
            PieData.push({
                value: kategoriData[i],
                color: kategoriColors[i],
                highlight: kategoriColors[i],
                label: kategoriLabels[i]
            });
        }

        var pieOptions = {
            //Boolean - Whether we should show a stroke on each segment
            segmentShowStroke: true,
            //String - The colour of each segment stroke
            segmentStrokeColor: '#fff',
            //Number - The width of each segment stroke
            segmentStrokeWidth: 2,
            //Number - The percentage of the chart that we cut out of the middle
            percentageInnerCutout: 50, // This is 0 for Pie charts
            //Number - Amount of animation steps
            animationSteps: 100,
            //String - Animation easing effect
            animationEasing: 'easeOutBounce',
            //Boolean - Whether we animate the rotation of the Doughnut
            animateRotate: true,
            //Boolean - Whether we animate scaling the Doughnut from the centre
            animateScale: false,
            //Boolean - whether to make the chart responsive to window resizing
            responsive: true,
            // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
            maintainAspectRatio: true,
            //String - A legend template
            legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>'
        };

        // Create pie chart
        var pieChart = new Chart(pieChartCanvas).Pie(PieData, pieOptions);
    });
</script>
@endpush