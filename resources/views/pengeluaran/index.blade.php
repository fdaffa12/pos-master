@extends('layouts.master')

@section('title')
    Daftar Pengeluaran
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Daftar Pengeluaran</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header with-border">
                <button onclick="addForm('{{ route('pengeluaran.store') }}')" class="btn btn-success btn-xs btn-flat"><i class="fa fa-plus-circle"></i> Tambah</button>
                <button onclick="updatePeriode()" class="btn btn-info btn-xs btn-flat"><i class="fa fa-plus-circle"></i> Ubah Periode</button>
                <button id="printPDF" class="btn btn-success btn-xs btn-flat"><i class="fa fa-file-pdf"></i> Print PDF</button>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-stiped table-bordered">
                    <thead>
                        <th width="5%">No</th>
                        <th>Tanggal</th>
                        <th>Deskripsi</th>
                        <th>Nominal</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: center;"><b>Total Pengeluaran</b></td>
                            <td id="total-pengeluaran" style="text-align: left;"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-form2" tabindex="-1" role="dialog" aria-labelledby="modal-form2">
    <div class="modal-dialog modal-lg" role="document">
        <form action="{{ route('pengeluaran.index') }}" method="get" data-toggle="validator" class="form-horizontal">
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

@includeIf('pengeluaran.form')
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
                url: '{{ route('pengeluaran.data', ['awal' => $tanggalAwal, 'akhir' => $tanggalAkhir]) }}',
            },
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'created_at'},
                {data: 'deskripsi'},
                {data: 'nominal'},
                {data: 'aksi', searchable: false, sortable: false},
            ],
            // Add this callback to calculate total pengeluaran
            "footerCallback": function (row, data, start, end, display) {
                var api = this.api(), data;
                // Convert nominal to integer for summing
                var intVal = function (i) {
                    return typeof i === 'string' ?
                        i.replace(/[\.,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i : 0;
                };
                // Total over all pages
                total = api
                    .column(3)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                // Format total pengeluaran to Rupiah
                var totalPengeluaranFormatted = new Intl.NumberFormat('id-ID').format(total);
                // Update total pengeluaran display
                $('#total-pengeluaran').html(totalPengeluaranFormatted);
            }
        });

        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });


        // $('#modal-form').validator().on('submit', function (e) {
        //     if (! e.preventDefault()) {
        //         $.post($('#modal-form form').attr('action'), $('#modal-form form').serialize())
        //             .done((response) => {
        //                 $('#modal-form').modal('hide');
        //                 table.ajax.reload();
        //             })
        //             .fail((errors) => {
        //                 alert('Tidak dapat menyimpan data');
        //                 return;
        //             });
        //     }
        // });
    });

    function addForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Tambah Pengeluaran');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('post');
        $('#modal-form [name=deskripsi]').focus();
    }

    function updatePeriode() {
            $('#modal-form2').modal('show');
        }

    function editForm(url) {
        $('#modal-form').modal('show');
        $('#modal-form .modal-title').text('Edit Pengeluaran');

        $('#modal-form form')[0].reset();
        $('#modal-form form').attr('action', url);
        $('#modal-form [name=_method]').val('put');
        $('#modal-form [name=deskripsi]').focus();

        $.get(url)
            .done((response) => {
                $('#modal-form [name=deskripsi]').val(response.deskripsi);
                $('#modal-form [name=nominal]').val(response.nominal);
            })
            .fail((errors) => {
                alert('Tidak dapat menampilkan data');
                return;
            });
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
                        <td style="font-size: 9px;">${$(this).find('td:eq(3)').text()}</td>
                    </tr>`;
            });

            // Mendapatkan HTML dari total pengeluaran
            var tfootHtml = $('.table tfoot').html();
            

            // Membuat konten untuk tabel dengan menambahkan thead dan tbody
            var tableHtml = `
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">No</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Tanggal</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Deskripsi</th>
                            <th style="font-size: 9px;">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tbodyHtml}
                    </tbody>
                    <tfoot style="font-size: 9px; border-bottom: 1px solid #ddd;">
                        ${tfootHtml}
                    </tfoot>
                </table>`;

            // Membuka jendela baru untuk mencetak laporan
            var newWindow = window.open('', '_blank');
            newWindow.document.write('<html><head><title>Laporan Pengeluaran</title><style>body { font-family: Arial, sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { padding: 4px; text-align: left; border-bottom: 1px solid #ddd; } th { background-color: #f2f2f2; }</style></head><body>');
            newWindow.document.write('<h1 style="text-align: center; font-size: 12px;">Laporan Pengeluaran</h1>');
            newWindow.document.write(tableHtml);
            newWindow.document.write('</body></html>');
            newWindow.document.close();
            

            // Mencetak laporan
            newWindow.print();
        });
    });

</script>

@endpush