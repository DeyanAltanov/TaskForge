<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Team
 * 
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 * @property Collection|TeamMember[] $team_members
 *
 * @package App\Models
 */
class Team extends Model
{
	protected $table = 'teams';

	protected $casts = [
		'created_by' => 'int'
	];

	protected $fillable = [
		'name',
		'description',
		'created_by',
		'updated_by'
	];

	public function createdBy()
	{
		return $this->belongsTo(User::class, 'created_by');
	}

	public function updatedBy()
	{
		return $this->belongsTo(User::class, 'updated_by');
	}

	public function team_members()
	{
		return $this->hasMany(TeamMember::class);
	}

	public function tasks()
	{
		return $this->hasMany(Task::class);
	}
}