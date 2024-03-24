@extends('layouts.master')

@section('title')
    Daftar Pembelian {{ tanggal_indonesia($tanggalAwal, false) }} s/d {{ tanggal_indonesia($tanggalAkhir, false) }}
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Daftar Pembelian</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addForm()" class="btn btn-success btn-xs btn-flat"><i class="fa fa-plus-circle"></i> Transaksi Baru</button>
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-plus-circle"></i> Ubah Periode</button>
                <button id="printPDF" class="btn btn-success btn-xs btn-flat"><i class="fa fa-file-pdf"></i> Print PDF</button>
                @empty(! session('id_pembelian'))
                <a href="{{ route('pembelian_detail.index') }}" class="btn btn-info btn-xs btn-flat"><i class="fa fa-pencil"></i> Transaksi Aktif</a>
                @endempty
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered table-pembelian">
                    <thead>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>Total Item</th>
                        <th>Total Harga</th>
                        <th>Diskon</th>
                        <th>Total Bayar</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                    <tbody>
                        <!-- Data pembelian akan ditampilkan di sini -->
                    </tbody>
                    <tfoot>
                        <tr>
                        <td colspan="6" style="text-align: center;"><b>Total Pembelian</b></td>
                            <td id="total-pembelian" style="text-align: left;"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>            
        </div>
    </div>
</div>

<div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modal-form">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('pembelian.index') }}" method="get" data-toggle="validator" class="form-horizontal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Periode Laporan</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group row">
                        <label for="tanggal_awal" class="col-lg-2 col-lg-offset-1 control-label">Tanggal Awal</label>
                        <div class="col-lg-6">
                            <input type="text" name="tanggal_awal" id="tanggal_awal" class="form-control datepicker" required autofocus
                                value="{{ request('tanggal_awal') }}"
                                style="border-radius: 0 !important;">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="tanggal_akhir" class="col-lg-2 col-lg-offset-1 control-label">Tanggal Akhir</label>
                        <div class="col-lg-6">
                            <input type="text" name="tanggal_akhir" id="tanggal_akhir" class="form-control datepicker" required
                                value="{{ request('tanggal_akhir') ?? date('Y-m-d') }}"
                                style="border-radius: 0 !important;">
                            <span class="help-block with-errors"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-sm btn-flat btn-primary"><i class="fa fa-save"></i> Simpan</button>
                    <button type="button" class="btn btn-sm btn-flat btn-warning" data-dismiss="modal"><i class="fa fa-arrow-circle-left"></i> Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>

@includeIf('pembelian.supplier')
@includeIf('pembelian.detail')
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script>
    let table, table1;

    $(function () {
        table = $('.table-pembelian').DataTable({
            responsive: true,
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '{{ route('pembelian.data', ['awal' => $tanggalAwal, 'akhir' => $tanggalAkhir]) }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'tanggal'},
                {data: 'supplier'},
                {data: 'total_item'},
                {data: 'total_harga'},
                {data: 'diskon'},
                {data: 'bayar'},
                {data: 'aksi', searchable: false, sortable: false},
            ],
            // Menambahkan callback setelah tabel selesai dibuat
            initComplete: function () {
                // Menghitung total pembelian
                let totalPembelian = 0;
                table.rows().every(function () {
                    let data = this.data();
                    totalPembelian += parseFloat(data.bayar.replace('Rp. ', '').replace('.', '').replace(',', '.'));
                });
                
                // Menampilkan total pembelian dengan format rupiah
                $('#total-pembelian').text('Rp. ' + formatUang(totalPembelian));
            }
        });

        $('.table-supplier').DataTable();
        table1 = $('.table-detail').DataTable({
            processing: true,
            bSort: false,
            dom: 'Brt',
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'kode_produk'},
                {data: 'nama_produk'},
                {data: 'harga_beli'},
                {data: 'jumlah'},
                {data: 'subtotal'},
            ]
        })

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });

    function updatePeriode() {
        $('#modal-form').modal('show');
    }

    function addForm() {
        $('#modal-supplier').modal('show');
    }

    function showDetail(url) {
        $('#modal-detail').modal('show');
        table1.ajax.url(url);
        table1.ajax.reload();
    }

    function deleteData(url) {
        if (confirm('Yakin ingin menghapus data terpilih?')) {
            $.post(url, {
                    '_token': $('[name=csrf-token]').attr('content'),
                    '_method': 'delete'
                })
                .done((response) => {
                    table.ajax.reload();
                })
                .fail((errors) => {
                    alert('Tidak dapat menghapus data');
                    return;
                });
        }
    }

    // Fungsi untuk memformat angka menjadi format rupiah
    function formatUang(angka) {
        let reverse = angka.toString().split('').reverse().join('');
        let ribuan = reverse.match(/\d{1,3}/g);
        let formatted = ribuan.join('.').split('').reverse().join('');
        return formatted;
    }

</script>

<script>
    $(document).ready(function() {
        $('#printPDF').click(function() {
            // Mendapatkan HTML dari kolom yang diinginkan
            var tbodyHtml = '';
            $('.table-pembelian tbody tr').each(function(index) {
                tbodyHtml += `
                    <tr>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(0)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(1)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(2)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(3)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(4)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(5)').text()}</td>
                        <td style="font-size: 9px;">${$(this).find('td:eq(6)').text()}</td>
                    </tr>`;
            });

            // Mendapatkan HTML dari total pembelian
            var tfootHtml = $('.table-pembelian tfoot').html();

            // Membuat konten untuk tabel dengan menambahkan thead dan tbody
            var tableHtml = `
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">No</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Tanggal</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Supplier</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Total Item</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Total Harga</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Diskon</th>
                            <th style="font-size: 9px;">Total Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tbodyHtml}
                    </tbody>
                    <tfoot style="font-size: 9px;">
                        ${tfootHtml}
                    </tfoot>
                </table>`;

            // Membuka jendela baru untuk mencetak laporan
            var newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>Laporan Pembelian</title><style>body { font-family: Arial, sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { padding: 4px; text-align: left; border-bottom: 1px solid #ddd; } th { background-color: #f2f2f2; }</style></head><body>');
            newWindow.document.write('<h1 style="text-align: center; font-size: 12px;">Laporan Pembelian</h1>');
            newWindow.document.write(tableHtml);
            newWindow.document.write('</body></html>');
            newWindow.document.close();

            // Mencetak laporan
            newWindow.print();
        });
    });

</script>
@endpush