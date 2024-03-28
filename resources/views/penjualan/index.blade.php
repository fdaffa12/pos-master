@extends('layouts.master')

@section('title')
    Daftar Penjualan {{ tanggal_indonesia($tanggalAwal, false) }} s/d {{ tanggal_indonesia($tanggalAkhir, false) }}
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endpush

@section('breadcrumb')
    @parent
    <li class="active">Daftar Penjualan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-body table-responsive">
                @if(auth()->user()->level == 1)
                <button class="btn btn-info btn-xs btn-flat" data-toggle="modal" data-target="#modalFilter"><i class="fa fa-filter"></i> Filter</button> 
                {{-- <button class="btn btn-primary btn-xs btn-flat" data-toggle="modal" data-target="#modalPaymentMethod"><i class="fa fa-money"></i> Filter Metode Pembayaran</button> --}}
                <button id="printPDF" class="btn btn-success btn-xs btn-flat"><i class="fa fa-file-pdf"></i> Print PDF</button>

                @else
                <button class="btn btn-primary btn-xs btn-flat" data-toggle="modal" data-target="#modalPaymentMethod"><i class="fa fa-money"></i> Filter Metode Pembayaran</button>
                @endif
                <br>
                <br>
                <table class="table table-stiped table-bordered table-penjualan">
                    <thead>
                        <th width="5%">No</th>
                        <th>Kode Bill</th>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Metode Pembayaran</th>
                        <th>Kode Member</th>
                        <th>Total Item</th>
                        <th>Total Harga</th>
                        <th>Diskon</th>
                        <th>Total Bayar</th>
                        <th>Kasir</th>
                        <th width="15%"><i class="fa fa-cog"></i></th>
                    </thead>
                    <tbody>
                    @foreach ($penjualan as $index => $data)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{($data->kode_bill)-1 }}</td>
                            <td>{{ tanggal_indonesia($data->created_at, false) }}</td>
                            <td>{{ ucfirst($data->nama) }}</td>
                            <td>{{ ucfirst($data->payment_method) }}</td>
                            <td>{{ $data->member->kode_member ?? '-' }}</td>
                            <td>{{ format_uang($data->total_item) }}</td>
                            <td>Rp. {{ format_uang($data->total_harga) }}</td>
                            <td>{{ $data->diskon }}%</td>
                            <td>Rp. {{ format_uang($data->bayar) }}</td>
                            <td>{{ $data->user->name ?? '-' }}</td>
                            <td>
                            @if(auth()->user()->level == 2)
                                <div class="btn-group">
                                    <button onclick="showDetail('{{ route('penjualan.show', $data->id_penjualan) }}')" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                                </div>
                            @elseif(auth()->user()->level == 1)
                                <div class="btn-group">
                                    <button onclick="showDetail('{{ route('penjualan.show', $data->id_penjualan) }}')" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                                    <button onclick="deleteData('{{ route('penjualan.destroy', $data->id_penjualan) }}')" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                                </div>
                            @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="9" style="text-align: center;"><b>Total Penjualan</b></td>
                            <td>Rp. {{ format_uang($totalPendapatan) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="modalTanggal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pilih Rentang Tanggal</h4>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="form-group">
                        <label for="tanggal_awal">Tanggal Awal:</label>
                        <input type="date" class="form-control datepicker" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}">
                    </div>
                    <div class="form-group">
                        <label for="tanggal_akhir">Tanggal Akhir:</label>
                        <input type="date" class="form-control datepicker" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="modalPaymentMethod" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Pilih Metode Pembayaran</h4>
            </div>
            <div class="modal-body">
                <form id="filterPaymentMethodForm">
                    <div class="form-group">
                        <label for="payment_method">Metode Pembayaran:</label>
                        <select class="form-control" id="payment_method" name="payment_method">
                            <option value="">Semua</option>
                            <option value="cash">Cash</option>
                            <option value="qris">Qris</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="modalFilter" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Filter Data Penjualan</h4>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="form-group">
                        <label for="tanggal_awal">Tanggal Awal:</label>
                        <input type="date" class="form-control datepicker" id="tanggal_awal" name="tanggal_awal" value="{{ $tanggalAwal }}">
                    </div>
                    <div class="form-group">
                        <label for="tanggal_akhir">Tanggal Akhir:</label>
                        <input type="date" class="form-control datepicker" id="tanggal_akhir" name="tanggal_akhir" value="{{ $tanggalAkhir }}">
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Metode Pembayaran:</label>
                        <select class="form-control" id="payment_method" name="payment_method">
                            <option value="">Semua</option>
                            <option value="cash">Cash</option>
                            <option value="qris">Qris</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>
        </div>
    </div>
</div>



@includeIf('penjualan.detail')
@endsection

@push('scripts')
<script src="{{ asset('/AdminLTE-2/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script>
    let table1;

    $(function () {
        table1 = $('.table-detail').DataTable({
            processing: true,
            bSort: false,
            dom: 'Brt',
            columns: [
                {data: 'DT_RowIndex', searchable: false, sortable: false},
                {data: 'kode_produk'},
                {data: 'nama_produk'},
                {data: 'harga_jual'},
                {data: 'jumlah'},
                {data: 'subtotal'},
            ]
        })

        $('#filterForm').submit(function (e) {
            e.preventDefault();
            
            var tanggalAwal = $('#tanggal_awal').val();
            var tanggalAkhir = $('#tanggal_akhir').val();
            var paymentMethod = $('#payment_method').val();
            
            var url = '{{ route('penjualan.index') }}?tanggal_awal=' + tanggalAwal + '&tanggal_akhir=' + tanggalAkhir;

            // Tambahkan parameter metode pembayaran jika dipilih
            if (paymentMethod) {
                url += '&payment_method=' + paymentMethod;
            }

            // Redirect ke URL dengan parameter filter yang sesuai
            window.location.href = url;
        });


        $('#filterPaymentMethodForm').submit(function (e) {
            e.preventDefault();

            var paymentMethod = $('#payment_method').val();

            // Redirect to filtered URL
            window.location.href = '{{ route('penjualan.index') }}?payment_method=' + paymentMethod;
        });
    });

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
                    window.location.reload();
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
            $('.table-penjualan tbody tr').each(function(index) {
                tbodyHtml += `
                    <tr>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(0)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(1)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(2)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(3)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(4)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(5)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(6)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(7)').text()}</td>
                        <td style="border-right: 1px solid #ddd; font-size: 9px;">${$(this).find('td:eq(8)').text()}</td>
                        <td style="font-size: 9px;">${$(this).find('td:eq(9)').text()}</td>
                    </tr>`;
            });

            // Mendapatkan HTML dari total pendapatan
            var tfootHtml = $('.table-penjualan tfoot').html();

            // Membuat konten untuk tabel dengan menambahkan thead dan tbody
            var tableHtml = `
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">No</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Kode Bill</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Tanggal</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Nama</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Metode Pembayaran</th>
                            <th style="border-right: 1px solid #ddd; font-size: 9px;">Kode Member</th>
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
            newWindow.document.write('<html><head><title>Laporan Penjualan</title><style>body { font-family: Arial, sans-serif; } table { width: 100%; border-collapse: collapse; } th, td { padding: 4px; text-align: left; border-bottom: 1px solid #ddd; } th { background-color: #f2f2f2; }</style></head><body>');
            newWindow.document.write('<h1 style="text-align: center; font-size: 12px;">Laporan Penjualan</h1>');
            newWindow.document.write(tableHtml);

                       
            newWindow.document.write('</body></html>');
            newWindow.document.close();

            // Mencetak laporan
            newWindow.print();
        });
    });

</script>








@endpush
