<?php

namespace App\Http\Controllers;

use App\Models\MemorizationLog;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeachersController extends Controller
{
    //
    public function getStudents(Request $request)
    {
        $user = $request->user();
        if (!$user || $user->role !== 'teacher') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user_id = $user->id;
        $teacher = Teacher::where('user_id', $user_id)->first();

        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $students = $teacher->students;

        return response()->json(['students' => $students],200);
    }
    public function getStudentsNumber(Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;
        $teacher = Teacher::where('user_id', $user_id)->first();
        //$teacher = $user->teacher;

        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $students = $teacher->students()->count();

        return response()->json(['students_number' => $students],200);
    }
    public function getStudentByName(Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;
        $teacher = Teacher::where('user_id', $user_id)->first();

        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }
        $name = $request->input('name');
        $student = $teacher->students()->user()->where('first_name', 'like', "%{$name}%")->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json(['student' => $student], 200);
    }
    public function groupAchievement(Request $request)
{
    $teacher = $request->user();

    $acceptedParts = MemorizationLog::where('teacher_id', $teacher->id)
        ->where('status', 'accepted')
        ->sum('parts');

    $rejectedCount = MemorizationLog::where('teacher_id', $teacher->id)
        ->where('status', 'rejected')
        ->count();

    $absentCount = MemorizationLog::where('teacher_id', $teacher->id)
        ->where('status', 'absent')
        ->count();

    return response()->json([
        'accepted_parts' => $acceptedParts,
        'rejected_count' => $rejectedCount,
        'absent_count' => $absentCount,
    ]);
}




public function activeStudents(Request $request)
{
    $teacher = $request->user();

    $students = Student::where('teacher_id', $teacher->id)
        ->with('user')
        ->get();

    $activeStudents = [];

    foreach ($students as $student) {

        $lastThree = MemorizationLog::where('student_id', $student->id)
            ->where('teacher_id', $teacher->id)
            ->latest()
            ->take(3)
            ->pluck('status');

        if ($lastThree->count() == 3 &&
            $lastThree->every(fn($status) => $status == 'accepted')) {

            $activeStudents[] = [
                'student_id' => $student->id,
                'name' => $student->user->first_name . ' ' . $student->last_name
            ];
        }
    }

    return response()->json($activeStudents);
}




public function searchStudentById(Request $request, $id)
{
    $teacher = $request->user();

    $student = Student::with('user')
        ->where('id', $id)
        ->where('teacher_id', $teacher->id)
        ->first();

    if (!$student) {
        return response()->json([
            'message' => 'Student not found'
        ], 404);
    }

    return response()->json([
        'id' => $student->id,
        'first_name' => $student->user->first_name,
        'last_name' => $student->last_name,
        'mother_name' => $student->mother_name,
        'father_name' => $student->father_name,
        'home_address' => $student->home_address,
        'goal' => $student->goal,
    ]);
}
}
