<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Conversation extends Model
{
    protected $table = 'conversations';
    protected $fillable = ['user_id', 'title'];

    public function user()
    {
        $this->belongsTo(User::class, 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }
}
