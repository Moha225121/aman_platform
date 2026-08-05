<?php
namespace Database\Seeders;

use App\Models\{Counselor,User};
use Illuminate\Database\Seeder;

class CounselorAccountsSeeder extends Seeder
{
    public function run(): void
    {
        Counselor::with('user')->get()->each(function (Counselor $counselor) {
            $user=$counselor->user;
            if (!$user) {
                $user=User::create([
                    'name'=>$counselor->name,'alias'=>$counselor->name,
                    'username'=>'AMAN-C'.str_pad((string)$counselor->id,3,'0',STR_PAD_LEFT),
                    'email'=>'counselor_'.$counselor->id.'@aman.local',
                    'password'=>'AMAN_123','role'=>'counselor',
                ]);
                $counselor->update(['user_id'=>$user->id]);
            } else {
                $user->update(['password'=>'AMAN_123','role'=>'counselor']);
            }
        });
    }
}
