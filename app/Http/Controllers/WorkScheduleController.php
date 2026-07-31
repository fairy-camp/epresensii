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
        $request->validate([
            'name'           => 'required|string|max:255',
            'check_in_time'  => 'required|date_format:H:i',
            'check_out_time' => 'required|date_format:H:i',
        ]);

        WorkSchedule::create($request->only(['name', 'check_in_time', 'check_out_time']));

        return redirect()->back()->with('success', 'Jadwal kerja berhasil ditambahkan.');
    }

    public function edit(WorkSchedule $workSchedule)
    {
        $schedules = WorkSchedule::latest()->get();
        return view('work_schedules.index', compact('workSchedule', 'schedules'));
    }

    public function update(Request $request, WorkSchedule $workSchedule)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'check_in_time'  => 'required|date_format:H:i',
            'check_out_time' => 'required|date_format:H:i',
        ]);

        $workSchedule->update($request->only(['name', 'check_in_time', 'check_out_time']));

        return redirect()->route('work-schedules.index')->with('success', 'Jadwal kerja berhasil diperbarui.');
    }

    public function destroy(WorkSchedule $workSchedule)
    {
        $workSchedule->delete();
        return redirect()->back()->with('success', 'Jadwal kerja berhasil dihapus.');
    }
}
