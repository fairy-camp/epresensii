<?php

namespace App\Http\Controllers;

use App\Models\ShiftAssignment;
use App\Models\Teacher;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;

class ShiftAssignmentController extends Controller
{
    public function index()
    {
        // Ambil semua penugasan shift permanen
        $assignments = ShiftAssignment::with(['teacher', 'workSchedule'])->get();
        $teachers = Teacher::where('is_active', true)->get();
        $schedules = WorkSchedule::all();

        return view('shift_assignments.index', compact('assignments', 'teachers', 'schedules'));
    }

    // Fitur Set Shift Permanen Guru
    public function storeBulk(Request $request)
    {
        $request->validate([
            'work_schedule_id' => 'required|exists:work_schedules,id',
            'teacher_ids'      => 'required|array',
        ]);

        $teacherIds = $request->teacher_ids;

        // Opsi "Pilih Semua Guru"
        if (in_array('all', $teacherIds)) {
            $teacherIds = Teacher::where('is_active', true)->pluck('id')->toArray();
        }

        $count = 0;
        foreach ($teacherIds as $teacherId) {
            // updateOrCreate berdasarkan teacher_id saja (tanpa date)
            ShiftAssignment::updateOrCreate(
                [
                    'teacher_id' => $teacherId,
                ],
                [
                    'work_schedule_id' => $request->work_schedule_id,
                ]
            );
            $count++;
        }

        return redirect()->back()->with('success', "Berhasil mengatur shift permanen untuk {$count} guru.");
    }

    public function destroy(ShiftAssignment $shiftAssignment)
    {
        $shiftAssignment->delete();
        return redirect()->back()->with('success', 'Penugasan shift berhasil dihapus.');
    }
}