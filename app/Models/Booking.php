<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Booking extends Model
{
    protected $fillable=['user_id','counselor_id','service_id','support_program_id','status','session_method','note','scheduled_at','meeting_url','location_url'];
    protected $casts=['scheduled_at'=>'datetime'];

    public function user(){return $this->belongsTo(User::class);}
    public function counselor(){return $this->belongsTo(Counselor::class);}
    public function service(){return $this->belongsTo(Service::class);}
    public function supportProgram(){return $this->belongsTo(SupportProgram::class);}
    public function messages(){return $this->hasMany(BookingMessage::class);}
}
