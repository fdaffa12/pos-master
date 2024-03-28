<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Member;
use App\Models\Pembelian;
use App\Models\Pengeluaran;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Supplier;
use App\Models\PenjualanDetail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    public function index()
    {
        $kategori = Kategori::count();
        $produk = Produk::count();
        $supplier = Supplier::count();
        $member = Member::count();

        $tanggal_awal = date('Y-m-01');
        $tanggal_akhir = date('Y-m-d');

        $data_tanggal = array();
        $data_pendapatan = array();

        while (strtotime($tanggal_awal) <= strtotime($tanggal_akhir)) {
            $data_tanggal[] = (int) substr($tanggal_awal, 8, 2);

            $total_penjualan = Penjualan::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('bayar');
            $total_pembelian = Pembelian::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('bayar');
            $total_pengeluaran = Pengeluaran::where('created_at', 'LIKE', "%$tanggal_awal%")->sum('nominal');

            $pendapatan = $total_penjualan - $total_pembelian - $total_pengeluaran;
            $data_pendapatan[] += $pendapatan;

            $tanggal_awal = date('Y-m-d', strtotime("+1 day", strtotime($tanggal_awal)));
        }

        $tanggal_awal = date('Y-m-01');

        // Ambil data penjualan terbanyak per bulan untuk setiap produk
        $penjualanTerbanyakPerBulan = PenjualanDetail::select(DB::raw('YEAR(created_at) as tahun, MONTH(created_at) as bulan'), 'id_produk')
        ->selectRaw('COUNT(*) as total_penjualan')
        ->with('produk') // Eager load relasi produk
        ->groupBy('tahun', 'bulan', 'id_produk')
        ->orderByDesc('tahun')
        ->orderByDesc('bulan')
        ->limit(5)
        ->get();

        // Mendapatkan daftar nama produk dan total penjualannya
        $produkLabels = [];
        $produkData = [];

        foreach ($penjualanTerbanyakPerBulan as $penjualan) {
            if ($penjualan->produk) {
                $produkLabels[] = $penjualan->produk->nama_produk; // Ambil nama produk
                $produkData[] = $penjualan->total_penjualan; // Ambil total penjualan
            }
        }

        // Ambil data kasir dengan penjualan terbanyak
        $kasirTerbanyak = Penjualan::select('id_user')
        ->selectRaw('COUNT(*) as total_penjualan')
        ->groupBy('id_user')
        ->orderByDesc('total_penjualan')
        ->limit(5) // Ubah angka 5 menjadi jumlah kasir teratas yang ingin Anda tampilkan
        ->get();

        // Mendapatkan daftar ID kasir dengan penjualan terbanyak
        $kasirIds = $kasirTerbanyak->pluck('id_user');

        // Ambil informasi kasir berdasarkan ID kasir dengan penjualan terbanyak
        // Ambil informasi kasir dengan penjualan terbanyak
        $kasirInfo = DB::table('users')
        ->select('users.*', DB::raw('(SELECT COUNT(*) FROM penjualan WHERE penjualan.id_user = users.id) as total_penjualan'))
        ->whereIn('id', $kasirIds)
        ->orderByDesc('total_penjualan')
        ->get();

        // Kemudian, Anda dapat membuat array dari nama-nama kasir untuk digunakan sebagai labels di chart
        $kasirLabels = [];
        $kasirData = [];

        foreach ($kasirInfo as $kasir) {
            $kasirLabels[] = $kasir->name; // Ambil nama kasir
            $kasirData[] = $kasir->total_penjualan; // Ambil total penjualan
        }



        if (auth()->user()->level == 1) {
            return view('admin.dashboard', compact('kategori', 'kasirLabels', 'kasirData', 'kasirInfo', 'produkLabels', 'produkData', 'penjualanTerbanyakPerBulan', 'produk', 'supplier', 'member', 'tanggal_awal', 'tanggal_akhir', 'data_tanggal', 'data_pendapatan'));
        } else {
            return view('kasir.dashboard');
        }
    }
}
