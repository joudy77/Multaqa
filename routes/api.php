<?php

use App\Http\Controllers\Api\SemanticSearchController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RecitationSessionController;
use App\Http\Controllers\TeachersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WordSearchController;
use App\Http\Controllers\SmartRecitationController;

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


Route::post('/recitation-sessions/store', [RecitationSessionController::class, 'store'])->middleware('auth:sanctum');
Route::get('/students/{student}/next-session', [RecitationSessionController::class, 'nextSession']);
Route::post('/recitation-sessions/{session}/errors', [RecitationSessionController::class, 'storeErrors']);
Route::patch('/recitation-sessions/{session}/status', [RecitationSessionController::class, 'updateStatus']);
Route::delete('/recitation-sessions/{session}', [RecitationSessionController::class, 'destroy']);
Route::get('/students/{student}/recitation-history', [RecitationSessionController::class, 'history']);
Route::post('/recitation-sessions/show', [RecitationSessionController::class, 'show']);
Route::get('/quran/semantic-search', [SemanticSearchController::class, 'search']);



Route::post('/search-words', [WordSearchController::class, 'search']);
Route::get('/recitation-sessions/{session}/mawdi-review', [RecitationSessionController::class, 'mawdiReview']);


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/teacher/smart-recitation/suggest', [SmartRecitationController::class, 'suggest']);
    Route::post('/teacher/smart-recitation/sessions', [SmartRecitationController::class, 'createSession']);
    Route::get('/teacher/smart-recitation/students/{studentId}/upcoming', [SmartRecitationController::class, 'upcomingForStudent']);
});
