<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentsController extends Controller
{
    //
    public function getranking(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }
        // get the number of students with a higher achievement
        $higherAchievementCount = \App\Models\Student::where('achievement', '>', $student->achievement)->count();

        // Calculate the ranking
        $ranking = $higherAchievementCount + 1;

        return response()->json(['ranking' => $ranking]);
    }
    public function getStudentInfo(Request $request)
    {
        $user = $request->user();
        $student = $user->student;
        $fullName = $user->first_name . ' ' . $student->last_name;
        $goal = $student->goal;
        $path=$student->path;
        $college=$student->college;


        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        return response()->json( ['full_name' => $fullName, 'goal' => $goal, 'path'=>$path, 'college'=>$college],200);
    }
    public function getCollageRanking(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $college = $student->college;

        // Get the number of students in the same college with a higher achievement
        $higherAchievementCount = \App\Models\Student::where('college', $college)
            ->where('achievement', '>', $student->achievement)
            ->count();

        // Calculate the ranking
        $ranking = $higherAchievementCount + 1;

        return response()->json(['college_ranking' => $ranking],200);
    }
    public function getPathRanking(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $path = $student->path;

        // Get the number of students in the same path with a higher achievement
        $higherAchievementCount = \App\Models\Student::where('path', $path)
            ->where('achievement', '>', $student->achievement)
            ->count();

        // Calculate the ranking
        $ranking = $higherAchievementCount + 1;

        return response()->json(['path_ranking' => $ranking],200);
    }
    public function getAchievementRelationToGoal(Request $request)
    {
        $user = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $achievement = $student->achievement;
        $goal = $student->goal;
        

        return response()->json(['achievement' => $achievement, 'goal' => $goal], 200);
    }

}
