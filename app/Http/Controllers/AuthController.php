<?php
namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller

{
    //
    // public function register(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'number' => 'required|string|max:255|unique:users',
    //         'password' => 'required|string|min:8',
    //     ]);

    //     $user = \App\Models\User::create([
    //         'name' => $validatedData['name'],
    //         'number' => $validatedData['number'],
    //         'password' => bcrypt($validatedData['password']),
    //     ]);

    //     return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    // }
    // public function login(Request $request)
    // {
    //     $credentials = $request->validate([
    //         'number' => 'required|string',
    //         'password' => 'required|string',
    //     ]);

    //     if (!auth()->attempt($credentials)) {
    //         return response()->json(['message' => 'Invalid credentials'], 401);
    //     }

    //     $user = auth()->user();
    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     return response()->json(['message' => 'Login successful', 'access_token' => $token, 'token_type' => 'Bearer']);
    // }


//     public function register(RegisterRequest $request)
//     {
//         $validatedData = $request->validated();
        

        

//         $user = User::create([
//             'first_name' => $validatedData['first_name'],
//             'number' => $validatedData['number'],
//             'password' => Hash::make($validatedData['password']),
//             'role' => 'student',

//         ]);
//         $student = $user->student()->create([
//             'last_name' => $validatedData['last_name'],
//             'mother_name' => $validatedData['mother_name'],
//             'father_name' => $validatedData['father_name'],
//             'home_address' => $validatedData['home_address'],
//             'goal' => $validatedData['goal'],
//             'college' => $validatedData['college'],
//             'path' => $validatedData['path'],
//             'start_page' => $validatedData['start_page'] ,
//             'end_page' => $validatedData['end_page'] ?? 0,
// 'days_of_memorization' => $validatedData['days_of_memorization'],
//             'user_id' => $user->id,
//         ]);
//         //$user->student()->associate($student);
//         $token = $user->createToken('auth_token')->plainTextToken;
//         $teachers=Teacher::where('path',$student->path)
//         ->where('days_of_memorization',$student->days_of_memorization)
//         ->where('current_students','<',function($query) {
//             $query->select('student_limit')
//                   ->from('teachers')
//                   ->whereColumn('id', 'teachers.id');
//         })
//         ->get();
//         $foundTeacher = false;
//         foreach ($teachers as $teacher){
//             $limit =$teacher->students_limit;
//             $currentStudents = $teacher->current_students;
//             $availablity=$limit-$currentStudents;
//             if($availablity==$limit)
//             {
//                 $teacher->current_students += 1;
//                 $teacher->save();
//                 $student->teacher_id = $teacher->id;
//                 $student->save();
//                 $foundTeacher = true;
//                 break;
//             }
//         }
//         if($foundTeacher==false){
//             $teacher=$teachers->maxBy(function($teacher){
//                 return $teacher->students_limit-$teacher->current_students;
//             });
//             $teacher->current_students += 1;
//             $teacher->students()->associate($student);
//             $teacher->save();
//             $student->teacher_id = $teacher->id;
//             $student->teacher()->associate($teacher);
//             $student->save();
//             $foundTeacher = true;
//         }

//             if (!$foundTeacher) {
//                 return response()->json([
//                     'message' => 'No available teacher found for the selected path and days of memorization.'
//                 ], 404);
//             }

//         return response()->json([
//             'message' => 'Registration successful',
//             'access_token' => $token,
//             'token_type' => 'Bearer'
//         ]);
//     }
//     use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Log;

public function register(RegisterRequest $request)
{
    $validatedData = $request->validated();

    try {

        $result = DB::transaction(function () use ($validatedData) {

            $user = User::create([
                'first_name' => $validatedData['first_name'],
                'number' => $validatedData['number'],
                'password' => Hash::make($validatedData['password']),
                'role' => 'student',
            ]);

            $student = $user->student()->create([
                'last_name' => $validatedData['last_name'],
                'mother_name' => $validatedData['mother_name'],
                'father_name' => $validatedData['father_name'],
                'home_address' => $validatedData['home_address'],
                'goal' => $validatedData['goal'],
                'college' => $validatedData['college'],
                'path' => $validatedData['path'],
                'start_page' => $validatedData['start_page'],
                'end_page' => $validatedData['end_page'] ?? 0,
                'days_of_memorization' => $validatedData['days_of_memorization'],
            ]);

          
            $teacher = Teacher::where('path', $student->path)
                ->where(
                    'days_of_memorization',
                    $student->days_of_memorization
                )
                ->where('current_students', 0)
                ->lockForUpdate()
                ->first();

            if (!$teacher) {

                $teacher = Teacher::where('path', $student->path)
                    ->where(
                        'days_of_memorization',
                        $student->days_of_memorization
                    )
                    ->whereColumn(
                        'current_students',
                        '<',
                        'students_limit'
                    )
                    ->orderByRaw(
                        '(students_limit - current_students) DESC'
                    )
                    ->lockForUpdate()
                    ->first();
            }

            
            if (!$teacher) {

                throw new \RuntimeException(
                    'No available teacher found.'
                );
            }

            $teacher->increment('current_students');
            $student->teacher()->associate($teacher);
            $student->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            return $token;
        });

        return response()->json([
            'message' => 'Registration successful',
            'access_token' => $result,
            'token_type' => 'Bearer',
        ], 201);

    } catch (\RuntimeException $e) {

        return response()->json([
            'message' => $e->getMessage(),
        ], 422);

    } catch (\Throwable $e) {

        Log::error('Student registration failed', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'message' => 'Registration failed.',
        ], 500);
    }
}

public function login(Request $request)
{
    $request->validate([
        'number' => 'required|string',
        'password' => 'required|string'
    ]);

    $user = User::where('number', $request->number)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Login successful',
        'access_token' => $token,
        'role' => $user->role
    ]);
}
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

}

