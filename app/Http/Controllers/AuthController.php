<?php
namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data=$request->validate(['alias'=>'required|string|max:50','password'=>'required|string|min:8|confirmed','policy_accepted'=>'accepted']);
        do { $username='AMAN-'.random_int(10000,99999); } while(User::where('username',$username)->exists());
        $user=User::create(['name'=>$data['alias'],'alias'=>$data['alias'],'username'=>$username,'email'=>Str::uuid().'@anonymous.aman','password'=>$data['password'],'policy_accepted_at'=>now(),'policy_version'=>'1.0']);
        Auth::login($user); $request->session()->regenerate();
        return redirect()->route('dashboard')->with('created_username',$username);
    }
    public function login(Request $request)
    {
        $data=$request->validate(['username'=>'required|string','password'=>'required|string']);
        if(!Auth::attempt(['username'=>strtoupper($data['username']),'password'=>$data['password']])) return back()->withErrors(['username'=>'بيانات الدخول غير صحيحة.']);
        $request->session()->regenerate();
        $destination = match(Auth::user()->role) {
            'admin' => route('admin.dashboard'),
            'counselor' => route('counselor.dashboard'),
            default => route('dashboard'),
        };
        return redirect()->intended($destination);
    }
    public function logout(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect('/');}
}
