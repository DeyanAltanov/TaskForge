<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;

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
 * @property Collection|Comment[] $comments
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

	public function getProfilePictureAttribute($value): ?string
	{
		$default = 'uploads/avatars/default.jpg';

		if (!$value) {
			return asset($default);
		}

		if (preg_match('~^https?://~i', $value)) {
			return $value;
		}

		if (str_contains($value, '/')) {
			$rel = ltrim($value, '/');
			return asset($rel);
		}

		$path = 'uploads/avatars/' . $this->id . '/' . $value;

		if (!file_exists(public_path($path))) {
			return asset($default);
		}

		return asset($path);
	}

	public function tasks()
	{
		return $this->hasMany(Task::class, 'created_by');
	}

	public function team_members()
	{
		return $this->hasMany(TeamMember::class);
	}

	public function createdTeams()
	{
		return $this->hasMany(Team::class, 'created_by');
	}

	public function teams()
	{
		return $this->belongsToMany(Team::class, 'team_members', 'user_id', 'team_id');
	}

	public function updates()
	{
		return $this->hasMany(Update::class, 'updated_by');
	}

	public function comments()
    {
        return $this->hasMany(Comment::class);
    }

	public function reactions()
    { 
        return $this->hasMany(CommentReaction::class); 
    }

	public function files()
    {
        return $this->hasMany(TaskFile::class);
    }
}