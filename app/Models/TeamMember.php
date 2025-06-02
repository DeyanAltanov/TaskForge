<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class TeamMember
 * 
 * @property int $id
 * @property int $team_id
 * @property int $user_id
 * @property string|null $role
 * @property Carbon|null $created_at
 * 
 * @property Team $team
 * @property User $user
 *
 * @package App\Models
 */
class TeamMember extends Model
{
	protected $table = 'team_members';
	public $timestamps = false;

	protected $casts = [
		'team_id' => 'int',
		'user_id' => 'int'
	];

	protected $fillable = [
		'team_id',
		'user_id',
		'role'
	];

	public function team()
	{
		return $this->belongsTo(Team::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}