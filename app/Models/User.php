<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 * 
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string $phone
 * @property string $gender
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Task[] $tasks
 * @property Collection|TeamMember[] $team_members
 * @property Collection|Team[] $teams
 * @property Collection|Update[] $updates
 *
 * @package App\Models
 */
class User extends Authenticatable implements AuthenticatableContract
{
	use Notifiable;

	protected $table = 'users';

	protected $casts = [
		'email_verified_at' => 'datetime'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'first_name',
		'last_name',
		'email',
		'phone',
		'gender',
		'email_verified_at',
		'password',
		'remember_token'
	];

	public function tasks()
	{
		return $this->hasMany(Task::class, 'created_by');
	}

	public function team_members()
	{
		return $this->hasMany(TeamMember::class);
	}

	public function teams()
	{
		return $this->hasMany(Team::class, 'created_by');
	}

	public function updates()
	{
		return $this->hasMany(Update::class, 'updated_by');
	}
}
