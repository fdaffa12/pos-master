<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use App\Models\Produk;
use App\Models\Setting;
use Illuminate\Http\Request;
use PDF;
use Illuminate\Support\Facades\Auth;


class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $tanggalAwal = date('Y-m-d');
        $tanggalAkhir = date('Y-m-d');

        if ($request->has('tanggal_awal') && $request->has('tanggal_akhir')) {
            $tanggalAwal = $request->input('tanggal_awal');
            $tanggalAkhir = $request->input('tanggal_akhir');
        }

        $query = Penjualan::with('member')
            ->where('total_item', '!=', 0)
            ->whereDate('created_at', '>=', $tanggalAwal)
            ->whereDate('created_at', '<=', $tanggalAkhir)
            ->orderBy('id_penjualan', 'desc');

        // Filter berdasarkan metode pembayaran jika ada parameter payment_method yang dikirim melalui URL
        if ($request->has('payment_method')) {
            $paymentMethod = $request->input('payment_method');
            if ($paymentMethod != '') { // 'all' adalah nilai default jika tidak ada filter
                $query->where('payment_method', $paymentMethod);
            }
        }

        // Validasi peran pengguna
        if (Auth::user()->level == 1) {
            // Admin - tampilkan semua data kasir
            $penjualan = $query->get();
        } elseif (Auth::user()->level == 2) {
            // User biasa - tampilkan data kasir sesuai dengan user
            $penjualan = $query->whereHas('user', function ($query) {
                $query->where('id', Auth::id());
            })->get();
        }

        // Hitung total pendapatan (total bayar)
        $totalPendapatan = $penjualan->sum('bayar');

        return view('penjualan.index', compact('tanggalAwal', 'tanggalAkhir', 'penjualan', 'totalPendapatan'));
    }



    public function data(Request $request)
    {
        $query = Penjualan::with('member')->where('total_item', '!=', 0)->orderBy('id_penjualan', 'desc');

        if ($request->has('tanggal_awal') && $request->has('tanggal_akhir')) {
            $tanggalAwal = $request->input('tanggal_awal');
            $tanggalAkhir = $request->input('tanggal_akhir');
            $query->whereDate('created_at', '>=', $tanggalAwal)
                ->whereDate('created_at', '<=', $tanggalAkhir);
        }

        $penjualan = $query->get();

        return datatables()
            ->of($penjualan)
            ->addIndexColumn()
            ->addColumn('total_item', function ($penjualan) {
                return format_uang($penjualan->total_item);
            })
            ->addColumn('total_harga', function ($penjualan) {
                return 'Rp. '. format_uang($penjualan->total_harga);
            })
            ->addColumn('bayar', function ($penjualan) {
                return 'Rp. '. format_uang($penjualan->bayar);
            })
            ->addColumn('tanggal', function ($penjualan) {
                return tanggal_indonesia($penjualan->created_at, false);
            })
            ->addColumn('kode_member', function ($penjualan) {
                $member = $penjualan->member->kode_member ?? '';
                return '<span class="label label-success">'. $member .'</spa>';
            })
            ->editColumn('diskon', function ($penjualan) {
                return $penjualan->diskon . '%';
            })
            ->editColumn('kasir', function ($penjualan) {
                return $penjualan->user->name ?? '';
            })
            ->addColumn('aksi', function ($penjualan) {
                return '
                <div class="btn-group">
                    <button onclick="showDetail(`'. route('penjualan.show', $penjualan->id_penjualan) .'`)" class="btn btn-xs btn-info btn-flat"><i class="fa fa-eye"></i></button>
                    <button onclick="deleteData(`'. route('penjualan.destroy', $penjualan->id_penjualan) .'`)" class="btn btn-xs btn-danger btn-flat"><i class="fa fa-trash"></i></button>
                </div>
                ';
            })
            ->rawColumns(['aksi', 'kode_member'])
            ->make(true);
    }

    public function create()
    {
        $penjualan = new Penjualan();
        $penjualan->id_member = null;
        $penjualan->total_item = 0;
        $penjualan->total_harga = 0;
        $penjualan->diskon = 0;
        $penjualan->bayar = 0;
        $penjualan->kode_bill = 0;
        $penjualan->nama = null;
        $penjualan->payment_method = 'cash';
        $penjualan->id_user = auth()->id();
        $penjualan->save();

        session(['id_penjualan' => $penjualan->id_penjualan]);
        return redirect()->route('transaksi.index');
    }

    public function store(Request $request)
    {

        // Mendapatkan tanggal hari ini
        $tanggal = now()->format('Ymd');

        // Mendapatkan tanggal sebelumnya dari data penjualan terbaru
        $previousDate = Penjualan::latest()->value('created_at');
        $previousTanggal = $previousDate ? $previousDate->format('Ymd') : null;

        // Jika tanggal sebelumnya sama dengan tanggal hari ini, tambahkan nomor urut
        if ($previousTanggal == $tanggal) {
            // Mendapatkan nomor urut dari penjualan hari ini
            $count = Penjualan::whereDate('created_at', now())->count();
        } else {
            // Jika tanggal berbeda, nomor urut direset ke 1
            $count = 0;
        }

        // Nomor urut ditambah 1 untuk mendapatkan nomor urut baru
        $count += 1;

        // Menghasilkan kode bill dengan format tanggal + nomor urut yang sesuai
        $kode_bill = $tanggal . str_pad($count, 3, '0', STR_PAD_LEFT);



    
        $penjualan = Penjualan::findOrFail($request->id_penjualan);

        $penjualan->id_member = $request->id_member;
        $penjualan->total_item = $request->total_item;
        $penjualan->total_harga = $request->total;
        $penjualan->diskon = $request->diskon;
        $penjualan->bayar = $request->bayar;
        $penjualan->diterima = $request->diterima;
        $penjualan->nama = $request->nama;
        $penjualan->payment_method = $request->payment_method;
        // Menetapkan kode bill pada objek penjualan
        $penjualan->kode_bill = $kode_bill;
        $penjualan->update();

        $detail = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
            $item->diskon = $request->diskon;
            $item->update();

            $produk = Produk::find($item->id_produk);
            $produk->stok -= $item->jumlah;
            $produk->update();
        }

        return redirect()->route('transaksi.selesai');
    }

    public function show($id)
    {
        $detail = PenjualanDetail::with('produk')->where('id_penjualan', $id)->get();

        return datatables()
            ->of($detail)
            ->addIndexColumn()
            ->addColumn('kode_produk', function ($detail) {
                return '<span class="label label-success">'. $detail->produk->kode_produk .'</span>';
            })
            ->addColumn('nama_produk', function ($detail) {
                return $detail->produk->nama_produk;
            })
            ->addColumn('harga_jual', function ($detail) {
                return 'Rp. '. format_uang($detail->harga_jual);
            })
            ->addColumn('jumlah', function ($detail) {
                return format_uang($detail->jumlah);
            })
            ->addColumn('subtotal', function ($detail) {
                return 'Rp. '. format_uang($detail->subtotal);
            })
            ->rawColumns(['kode_produk'])
            ->make(true);
    }

    public function destroy($id)
    {
        $penjualan = Penjualan::find($id);
        $detail    = PenjualanDetail::where('id_penjualan', $penjualan->id_penjualan)->get();
        foreach ($detail as $item) {
            $produk = Produk::find($item->id_produk);
            if ($produk) {
                $produk->stok += $item->jumlah;
                $produk->update();
            }

            $item->delete();
        }

        $penjualan->delete();

        return response(null, 204);
    }

    public function selesai()
    {
        $setting = Setting::first();

        return view('penjualan.selesai', compact('setting'));
    }

    public function notaKecil()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();
        
        return view('penjualan.nota_kecil', compact('setting', 'penjualan', 'detail'));
    }

    public function notaBesar()
    {
        $setting = Setting::first();
        $penjualan = Penjualan::find(session('id_penjualan'));
        if (! $penjualan) {
            abort(404);
        }
        $detail = PenjualanDetail::with('produk')
            ->where('id_penjualan', session('id_penjualan'))
            ->get();

        $pdf = PDF::loadView('penjualan.nota_besar', compact('setting', 'penjualan', 'detail'));
        $pdf->setPaper(0,0,609,440, 'potrait');
        return $pdf->stream('Transaksi-'. date('Y-m-d-his') .'.pdf');
    }
}
