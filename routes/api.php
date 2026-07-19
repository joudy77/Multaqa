<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TeachersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::get('logout',[AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('students/ranking', [\App\Http\Controllers\StudentsController::class, 'getranking']);
    Route::get('students/info', [\App\Http\Controllers\StudentsController::class, 'getStudentInfo']);
    Route::get('students/college-ranking', [\App\Http\Controllers\StudentsController::class, 'getCollageRanking']);
    Route::get('students/path-ranking', [\App\Http\Controllers\StudentsController::class, 'getPathRanking']);
    Route::get('students/achievement_relation_to_goal', [\App\Http\Controllers\StudentsController::class, 'getAchievementRelationToGoal']);
    Route::get('teachers/students', [\App\Http\Controllers\TeachersController::class, 'getStudents']);
    Route::get('teachers/students/numbers', [\App\Http\Controllers\TeachersController::class, 'getStudentsNumber']);
    Route::post('teachers/students/name', [\App\Http\Controllers\TeachersController::class, 'getStudentByName']);

    Route::middleware('auth:sanctum')->get(
    '/teacher/group-achievement',
    [TeachersController::class, 'groupAchievement']
);

Route::middleware('auth:sanctum')->get(
    '/teacher/active-students',
    [TeachersController::class, 'activeStudents']
);


Route::middleware('auth:sanctum')->get(
    '/teacher/student/{id}',
    [TeachersController::class, 'searchStudentById']
);
});

