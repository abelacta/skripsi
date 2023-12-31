<?php

namespace App\Http\Controllers;

use App\Models\BookingModel;
use App\Models\PelangganModel;
use App\Models\WorkingOrderModel;
use App\Models\User;
use App\Models\WIPModel;
use DbPelanggan;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ServiceController extends Controller
{
    public function indexBiodata()
    {
        $title = 'Service';
        return view('customer.biodata', compact('title'));
    }

    public function onBooking($no_pol)
    {
        $title = 'Service';
        $pelanggan = PelangganModel::where('no_polisi', $no_pol)->first();
        $booking = BookingModel::where('no_polisi', $no_pol)->where('status', 'pending')->first();

        return view('customer.onBooking', compact('title', 'pelanggan', 'booking'));
    }

    public function onService()
    {
        $title = 'Service';
        return view('customer.onService', compact('title'));
    }

    public function inputCustomer(Request $request)
    {
        $title = 'Service';
        $noPolisi = $request->no_polisi;
        $checkNo = PelangganModel::where('no_polisi',  $noPolisi)->first();
        // dd($checkNo);
        if ($checkNo != null) {
            BookingModel::create([
                'no_polisi' =>   $noPolisi,
                'tgl_booking' => $request->tgl_booking,

            ]);
        } else {
            PelangganModel::create([
                'no_polisi' =>  $noPolisi,
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'email' => $request->email,
                'no_telp' => $request->no_telp,
                'jenis_mobil' => $request->jenis_mobil,
            ]);

            User::create([
                'no_polisi' =>  $noPolisi,
                'nama' => $request->nama,
                'username' => $request->nama
            ]);

            BookingModel::create([
                'no_polisi' =>   $noPolisi,
                'tgl_booking' => $request->tgl_booking,

            ]);
        }

        $nopol = $noPolisi;
        return redirect()->route('indexOnBooking', compact('nopol'));
    }

    ////
    public function woTable()
    {
        $title = 'BMW OFFICE';
        return view('admin.WO', ['title' => $title]);
    }

    public function inputWO()
    {
        $dataWo = WorkingOrderModel::all();
        $dataWip = WIPModel::all();
        $title = 'BMW OFFICE';
        return view('admin.inputWO', compact('title', 'dataWo', 'dataWip'));
    }

    public function submitWO(Request $request)
    {
        $user = User::where('nama', $request->pic_Service)->first();
        WorkingOrderModel::create([
            'no_wo' => $request->no_wo,
            'tanggal_mulai' => $request->tgl_mulai,
            'waktu_mulai' => $request->waktu_mulai,
            'no_polisi' => $request->no_polisi,
            'service_advisor' =>  $user->id,
            'status' => 'prepare'
        ]);

        WIPModel::create([
            'no_wip' => $request->no_wip,
            'no_wo' => $request->no_wo
        ]);

        //Update Pelanggan
        $pelanggan = PelangganModel::where('no_polisi', $request->no_polisi)->first();

        $pelanggan->update([
            'no_rangka' => $request->no_kerangka,
            'kilometer' => $request->kilometer,
            'tanggal_registrasi' => $request->tanggal_registrasi
        ]);

        $wo = WorkingOrderModel::all();
        $last = $wo->last();
        $id = $last->no_wo;
        $dataWo = WorkingOrderModel::where('no_wo', $id)->first();
        $dataWip = WIPModel::where('no_wo', $id)->first();
        $title = 'BMW OFFICE';

        // Debug output
        // dd($id, $dataWo, $dataWip); // Add this line to see the values

        return redirect()->route('detailWO', ['id' => $id])->with(compact('dataWo', 'title', 'dataWip'));
    }

    public function detailWO($id)
    {
        $dataWo = WorkingOrderModel::where('no_wo', $id)->first();
        $dataWip = WIPModel::where('no_wip', $id)->first();
        $title = 'BMW OFFICE';
        return view('admin.detailWO', compact('title', 'dataWo', 'dataWip'));
    }

    public function dataWO(Request $request)
    {
        if ($request->ajax()) {
            $data = WorkingOrderModel::with('db_pelanggan')->select('*');
            return Datatables::of($data)
                ->addColumn('no_wo', function ($data) {
                    $wo = '<a href="/detail/wo/' . $data->no_wo . '" style="text-decoration: none;">' . $data->no_wo . '</a>';
                    return $wo;
                })
                ->addColumn('pelanggan', function ($data) {
                    return optional($data->pelanggan)->nama;
                })
                ->addColumn('jenis_mobil', function ($data) {
                    return optional($data->pelanggan)->jenis_mobil;
                })
                ->rawColumns(['no_wo', 'pelanggan', 'jenis_mobil'])
                ->make(true);
        }
        // return  view('createWO', [
        //     "title" => 'Create WO'
        // ], compact('data'));
    }

    public function getPelanggan($id)
    {
        $pelanggan = WorkingOrderModel::where('no_polisi', $id)->first();
        return json_decode($pelanggan);
        // return view('admin.inputWO', compact('title', 'dataWo', 'dataWip'));
    }
}
