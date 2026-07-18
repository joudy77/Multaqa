<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeachersController extends Controller
{
    //
    public function getStudents(Request $request)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $students = $teacher->students;

        return response()->json(200,['students' => $students]);
    }
    public function getStudentsNumber(Request $request)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $students = $teacher->students()->pluck('number');

        return response()->json(200 ,['students_numbers' => $students]);
    }
    public function getStudentById(Request $request, $id)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return response()->json(['message' => 'Teacher not found'], 404);
        }

        $student = $teacher->students()->find($id);

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json(200,['student' => $student]);
    }
}
