<?php
namespace App\Http\Controllers;
use App\Models\{Booking,Counselor,Service,SupportProgram,User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AdminController extends Controller
{
    private function authorizeAdmin(): void { abort_unless(auth()->user()?->role === 'admin', 403); }

    public function index(){ $this->authorizeAdmin(); return view('admin.dashboard',['users'=>User::where('role','user')->latest()->get(),'bookings'=>Booking::with(['user','counselor'])->latest()->get(),'services'=>Service::all(),'programs'=>SupportProgram::all(),'counselors'=>Counselor::with('user')->latest()->get()]);}
    public function booking(Request $request,Booking $booking){
        $this->authorizeAdmin();
        $data=$request->validate([
            'status'=>'required|in:pending,accepted,completed,cancelled','scheduled_at'=>'nullable|date',
            'meeting_url'=>['nullable','url:http,https','max:1000','regex:#^https://meet\.google\.com/[a-z0-9-]+(?:\?.*)?$#i'],
            'location_url'=>['nullable','url:http,https','max:1000'],
        ]);
        if($data['status']==='accepted' && $booking->session_method==='online' && empty($data['meeting_url'])) throw \Illuminate\Validation\ValidationException::withMessages(['meeting_url'=>'أضف رابط Google Meet قبل تأكيد الحجز الأونلاين.']);
        if($data['status']==='accepted' && $booking->session_method==='in_person' && empty($data['location_url'])) throw \Illuminate\Validation\ValidationException::withMessages(['location_url'=>'أضف رابط موقع الجلسة قبل تأكيد الحجز الحضوري.']);
        if($booking->session_method==='online')$data['location_url']=null;else $data['meeting_url']=null;
        $booking->update($data);return back()->with('success','تم تحديث الحجز والموعد ورابط الجلسة.');
    }

    public function storeCounselor(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate(['username'=>'required|string|max:50|unique:users,username']);
        DB::transaction(function () use ($request) {
            $user=User::create(['name'=>$request->name,'alias'=>$request->name,'username'=>strtoupper($request->username),'email'=>uniqid('counselor_').'@aman.local','password'=>'AMAN_123','role'=>'counselor']);
            $user->counselor()->create($this->counselorData($request));
        });
        return back()->with('success','تم إنشاء المرشد بنجاح.')->withFragment('counselors');
    }

    public function updateCounselor(Request $request, Counselor $counselor)
    {
        $this->authorizeAdmin();
        $request->validate(['username'=>'required|string|max:50|unique:users,username,'.$counselor->user_id]);
        DB::transaction(function () use ($request,$counselor) {
            $counselor->update($this->counselorData($request));
            $user=$counselor->user;
            if(!$user){$user=User::create(['name'=>$request->name,'alias'=>$request->name,'username'=>strtoupper($request->username),'email'=>uniqid('counselor_').'@aman.local','password'=>'AMAN_123','role'=>'counselor']);$counselor->update(['user_id'=>$user->id]);}
            else{$user->update(['name'=>$request->name,'alias'=>$request->name,'username'=>strtoupper($request->username),'password'=>'AMAN_123']);}
        });
        return back()->with('success','تم تحديث بيانات المرشد.')->withFragment('counselors');
    }

    public function destroyCounselor(Counselor $counselor)
    {
        $this->authorizeAdmin();
        DB::transaction(function () use ($counselor) {$user=$counselor->user;$counselor->delete();$user?->delete();});
        return back()->with('success','تم حذف المرشد.')->withFragment('counselors');
    }

    private function counselorData(Request $request): array
    {
        $data = $request->validate([
            'name'=>'required|string|max:100','title'=>'required|string|max:150',
            'specialties'=>'nullable|string|max:500','qualifications'=>'nullable|string|max:1000',
            'bio'=>'nullable|string|max:2000','languages'=>'nullable|string|max:255',
            'experience_years'=>'nullable|integer|min:0|max:70','rating'=>'required|numeric|min:0|max:5',
            'active'=>'nullable|boolean',
        ]);
        $data['active'] = $request->boolean('active');
        return $data;
    }
}
