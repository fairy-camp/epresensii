<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use Illuminate\Http\Request;

class SchoolSettingController extends Controller
{
    public function index()
    {
        // Ambil data pertama, atau buat data default jika tabel masih kosong
        $setting = SchoolSetting::first();

        if (!$setting) {
            $setting = SchoolSetting::create([
                'school_name'     => 'SMK UP RPL CodePelita',
                'latitude'        => -6.88983630,
                'longitude'       => 109.67459170,
                'geofence_radius' => 50,
            ]);
        }

        return view('settings.school', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'school_name'     => 'required|string|max:255',
            'latitude'        => 'required|numeric',
            'longitude'       => 'required|numeric',
            'geofence_radius' => 'required|integer|min:5|max:5000',
        ]);

        $setting = SchoolSetting::first();
        
        if (!$setting) {
            $setting = new SchoolSetting();
        }

        $setting->fill($request->only([
            'school_name',
            'latitude',
            'longitude',
            'geofence_radius',
        ]))->save();

        return back()->with('success', 'Pengaturan lokasi & profil sekolah berhasil diperbarui!');
    }
}