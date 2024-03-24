@extends('layouts.master')

@section('title')
    Laporan Pendapatan {{ tanggal_indonesia($tanggalAwal, false) }} s/d {{ tanggal_indonesia($tanggalAkhir, false) }}
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Laporan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-plus-circle"></i> Ubah Periode</button>
                <button id="printPDF" class="btn btn-success btn-xs btn-flat"><i class="fa fa-file-pdf"></i> Print PDF</button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered">
                    <thead>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Penjualan</th>
                        <th>Pembelian</th>
                        <th>Pengeluaran</th>
                        <th>Pendapatan</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@includeIf('laporan.form')
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script>
    let table;

    $(function () {
        table = $('.table').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('laporan.data', [$tanggalAwal, $tanggalAkhir]) }}',
            },
            columns: [
            {data: 'DT_RowIndex', searchable: false, sortable: false},
            {data: 'tanggal'},
            {
                data: 'penjualan',
            render: function(data, type, row) {
                var tanggalAwal = $('#tanggal_awal').val();
                var tanggalAkhir = $('#tanggal_akhir').val();
                var paymentMethod = $('#payment_method').val();

                var tanggalParts = row.tanggal.split(' ');
                var hari = tanggalParts[0]; // Mengambil bagian hari
                var bulan = tanggalParts[1]; // Mengambil bagian bulan
                var tahun = tanggalParts[2]; // Mengambil bagian tahun

                // Membuat objek bulan yang berisi penamaan bulan dalam bahasa Indonesia
                var bulanIndonesia = {
                    'Januari': '01',
                    'Februari': '02',
                    'Maret': '03',
                    'April': '04',
                    'Mei': '05',
                    'Juni': '06',
                    'Juli': '07',
                    'Agustus': '08',
                    'September': '09',
                    'Oktober': '10',
                    'November': '11',
                    'Desember': '12'
                };

                var bulanAngka = bulanIndonesia[bulan]; // Mengambil angka bulan berdasarkan penamaan bulan

                // Format tanggal dalam format YYYY-MM-DD
                var tanggal = tahun + '-' + bulanAngka + '-' + hari;



                // Buat URL berdasarkan tanggal, tanggal_awal, dan tanggal_akhir
                var url = '{{ route('penjualan.index') }}?tanggal_awal=' + tanggal + '&tanggal_akhir=' + tanggal + '&payment_method=';



                // Tambahkan parameter metode pembayaran jika dipilih
                if (paymentMethod) {
                    url += '&payment_method=' + paymentMethod;
                }

                // Buat tautan dengan URL dinamis
                return '<a href="' + url + '">' + data + '</a>';
            }
            },
            {data: 'pembelian'},
            {data: 'pengeluaran'},
            {data: 'pendapatan'}
        ],
            dom: 'Brt',
            bSort: false,
            bPaginate: false,
        });

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });

    function updatePeriode() {
        $('#modal-form').modal('show');
    }
</script>
<script>
    $(document).ready(function() {
        $('#printPDF').click(function() {
            // Mendapatkan HTML dari kolom yang diinginkan
            var tbodyHtml = '';
            $('.table tbody tr').each(function(index) {
                tbodyHtml += `
                    <tr>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(0)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(1)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(2)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(3)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(4)').text()}</td>
                        <td style="font-size: 9px;">${$(this).find('td:eq(5)').text()}</td>
                    </tr>`;
            });

            // Mendapatkan HTML dari total pendapatan
            // var tfootHtml = $('.table tfoot').html();

            // Membuat konten untuk tabel dengan menambahkan thead dan tbody
            var tableHtml = `
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">No</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Tanggal</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Penjualan</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Pembelian</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Pengeluaran</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tbodyHtml}
                    </tbody>
                </table>`;

            // Membuka jendela baru untuk mencetak laporan
            var newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>Laporan Pendapatan</title><style>body { font-family: Arial, sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { padding: 4px; text-align: left; border-bottom: 1px solid #ddd; } th { background-color: #f2f2f2; }</style></head><body>');
            newWindow.document.write('<h1 style="text-align: center; font-size: 12px;">Laporan Pendapatan</h1>');
            newWindow.document.write(tableHtml);

            // Menambahkan tfoot hanya di halaman terakhir
            // if ($('.table').DataTable().page.info().end === $('.table tbody tr').length) {
            //     newWindow.document.write('<table style="width: 100%; border-collapse: collapse;"><tfoot>' + tfootHtml + '</tfoot></table>');
            // }
            
            newWindow.document.write('</body></html>');
            newWindow.document.close();

            // Mencetak laporan
            newWindow.print();
        });
    });

</script>
@endpush