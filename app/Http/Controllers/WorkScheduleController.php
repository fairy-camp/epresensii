<?php

namespace App\Http\Controllers;

use App\Models\WorkSchedule;
use Illuminate\Http\Request;

class WorkScheduleController extends Controller
{
    public function index()
    {
        $schedules = WorkSchedule::latest()->get();
        return view('work_schedules.index', compact('schedules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'type'                 => 'required|in:fixed,shift',
            'start_check_in_time'  => 'required|date_format:H:i',
            'check_in_time'        => 'required|date_format:H:i',
            'end_check_in_time'    => 'required|date_format:H:i',
            'start_check_out_time' => 'required|date_format:H:i',
            'check_out_time'       => 'required|date_format:H:i',
            'end_check_out_time'   => 'required|date_format:H:i',
        ]);

        WorkSchedule::create($validated);

        return redirect()->back()->with('success', 'Jadwal kerja berhasil ditambahkan.');
    }

    public function update(Request $request, WorkSchedule $workSchedule)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'type'                 => 'required|in:fixed,shift',
            'start_check_in_time'  => 'required|date_format:H:i',
            'check_in_time'        => 'required|date_format:H:i',
            'end_check_in_time'    => 'required|date_format:H:i',
            'start_check_out_time' => 'required|date_format:H:i',
            'check_out_time'       => 'required|date_format:H:i',
            'end_check_out_time'   => 'required|date_format:H:i',
        ]);

        $workSchedule->update($validated);

        return redirect()->back()->with('success', 'Jadwal kerja berhasil diperbarui.');
    }

    public function destroy(WorkSchedule $workSchedule)
    {
        $workSchedule->delete();
        return redirect()->back()->with('success', 'Jadwal kerja berhasil dihapus.');
    }
}