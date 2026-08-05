<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Counselor extends Model
{
    protected $fillable=['user_id','name','title','specialties','qualifications','bio','languages','experience_years','rating','active'];
    protected $casts=['active'=>'boolean','rating'=>'decimal:1','experience_years'=>'integer'];

    public function user(){return $this->belongsTo(User::class);}
    public function bookings(){return $this->hasMany(Booking::class);}
}
