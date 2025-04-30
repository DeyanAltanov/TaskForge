<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Task
 * 
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $status
 * @property string|null $priority
 * @property int|null $assigned_to
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Task extends Model
{
	protected $table = 'tasks';

	protected $casts = [
		'assigned_to' => 'int',
		'created_by' => 'int'
	];

	protected $fillable = [
		'title',
		'description',
		'status',
		'priority',
		'assigned_to',
		'created_by'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'created_by');
	}
}
