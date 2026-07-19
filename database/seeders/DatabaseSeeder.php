<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
 $user=User::create([
            'first_name' => 'معلمة',
            'number' => '0911111111',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);
        $user->teacher = Teacher::create([
            'user_id' => 1, // Assuming the teacher user has ID 2
        ]);
        

        
        User::create([
            'first_name' => 'طالبة',
            'number' => '0900000000',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);
        $user->student = Student::create([
            'last_name' => 'تقي الدين',
            'mother_name' => ' والدة الطالبة',
            'father_name' => 'مؤمن',
            'home_address' => 'مزة',
            'goal' => 100,
            'achievement' => 50,
            'college' => 'الهندسة المعلوماتية ',
            'path' => ' زاد',
            'user_id' => 2, // Assuming the student user has ID 1
            'teacher_id' => 1, // Assuming the teacher user has ID 2
            'start_page' => 1,
            'end_page' => 10,
        ]);
       
    }
}
