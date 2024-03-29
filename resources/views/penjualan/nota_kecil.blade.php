<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nota Kecil</title>

    <style>
        @media print {
            @page {
                size: auto; /* Sesuaikan dengan ukuran lebar kertas printer Anda */
                margin: 0p;
            }
            body {
                size: 58mm;
                font-family: "consolas", sans-serif;
                margin: 0.1cm; /* Jarak 1cm pada kanan dan kiri */
                padding: 0;
                font-size: 10pt;
            }
            .text-center {
                text-align: center;
            }
            .text-right {
                text-align: right;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="btn-print" style="position: absolute; right: 1rem; top: 1rem;" onclick="window.print()">Print</button>
    <div class="text-center">
        <h3 style="margin-bottom: 5px;">{{ strtoupper($setting->nama_perusahaan) }}</h3>
        <p>{{ strtoupper($setting->alamat) }}</p>
    </div>
    <br>
    <div>
        <p style="float: left;">{{ date('d-m-Y') }}</p>
        <p style="float: right">{{ strtoupper(auth()->user()->name) }}</p>
    </div>
    <div style="clear: both;"></div>
    <p>No: {{($penjualan->kode_bill)-1 }}</p>
    <p class="text-center">===================================</p>
    
    <br>
    <table width="100%" style="border: 0;">
        @foreach ($detail as $item)
        <tr>
            <td colspan="3">
                <?php
                $nama_produk = $item->produk->nama_produk;
                $tanggal_dibuat = $item->created_at; // Sesuaikan dengan atribut yang sesuai
                $paket_1_jam_anak = "Paket 1 Jam Anak";
        
                if ($nama_produk == $paket_1_jam_anak) {
                    // Tampilkan nama produk
                    echo $nama_produk . "<br>";
        
                    // Tampilkan jam dibuatnya
                    echo "Chekin: " . date('H:i', strtotime($tanggal_dibuat)) . "<br>";
        
                    // Lakukan penjumlahan 60 menit dari jam dibuatnya
                    $tanggal_dibuat_plus_60_menit = strtotime('+60 minutes', strtotime($tanggal_dibuat));
                    $jam_ditambah_60 = date('H:i', $tanggal_dibuat_plus_60_menit);
        
                    echo "Check Out: " . $jam_ditambah_60;
                } else {
                    // Jika nama produk bukan "Paket 1 Jam Anak", tampilkan hanya nama produk
                    echo $nama_produk;
                }
                ?>
            </td>
        </tr>
        
        <tr>
            <td>{{ $item->jumlah }} x {{ format_uang($item->harga_jual) }}</td>
            <td></td>
            <td class="text-right">{{ format_uang($item->jumlah * $item->harga_jual) }}</td>
        </tr>
        @endforeach
    </table>
    <p class="text-center">-----------------------------------</p>

    <table width="100%" style="border: 0;">
        <tr>
            <td>Total Harga:</td>
            <td class="text-right">{{ format_uang($penjualan->total_harga) }}</td>
        </tr>
        <tr>
            <td>Total Item:</td>
            <td class="text-right">{{ format_uang($penjualan->total_item) }}</td>
        </tr>
        <tr>
            <td>Diskon:</td>
            <td class="text-right">{{ format_uang($penjualan->diskon) }}</td>
        </tr>
        <tr>
            <td>Total Bayar:</td>
            <td class="text-right">{{ format_uang($penjualan->bayar) }}</td>
        </tr>
        <tr>
            <td>Diterima:</td>
            <td class="text-right">{{ format_uang($penjualan->diterima) }}</td>
        </tr>
        <tr>
            <td>Kembali:</td>
            <td class="text-right">{{ format_uang($penjualan->diterima - $penjualan->bayar) }}</td>
        </tr>
    </table>

    <p class="text-center">===================================</p>
    <p class="text-center">-- TERIMA KASIH --</p>
</body>
<script>
    // Fungsi untuk mencetak konten ke printer termal
    function printData() {
        window.print();
    }
    // Panggil fungsi printData saat halaman dimuat
    window.onload = printData;
</script>

</html>
