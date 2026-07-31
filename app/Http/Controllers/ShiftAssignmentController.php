<?php

namespace App\Http\Controllers;

use App\Models\ShiftAssignment;
use App\Models\Teacher;
use App\Models\WorkSchedule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ShiftAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());

        $assignments = ShiftAssignment::with(['teacher', 'workSchedule'])
            ->where('date', $date)
            ->get();

        $teachers = Teacher::where('is_active', true)->get();
        $schedules = WorkSchedule::all();

        return view('shift_assignments.index', compact('assignments', 'teachers', 'schedules', 'date'));
    }

    // Fitur Generate Shift Massal
    public function storeBulk(Request $request)
    {
        $request->validate([
            'work_schedule_id' => 'required|exists:work_schedules,id',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'teacher_ids'      => 'required|array',
        ]);

        $period = CarbonPeriod::create($request->start_date, $request->end_date);
        $teacherIds = $request->teacher_ids;

        // Opsi "Pilih Semua Guru"
        if (in_array('all', $teacherIds)) {
            $teacherIds = Teacher::where('is_active', true)->pluck('id')->toArray();
        }

        $count = 0;
        foreach ($period as $date) {
            $formattedDate = $date->toDateString();

            foreach ($teacherIds as $teacherId) {
                ShiftAssignment::updateOrCreate(
                    [
                        'teacher_id' => $teacherId,
                        'date'       => $formattedDate,
                    ],
                    [
                        'work_schedule_id' => $request->work_schedule_id,
                    ]
                );
                $count++;
            }
        }

        return redirect()->back()->with('success', "Berhasil membuat/memperbarui {$count} penugasan shift.");
    }

    public function destroy(ShiftAssignment $shiftAssignment)
    {
        $shiftAssignment->delete();
        return redirect()->back()->with('success', 'Penugasan shift berhasil dihapus.');
    }
}