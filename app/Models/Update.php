<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Update
 * 
 * @property int $id
 * @property int $user_id
 * @property string $field_name
 * @property string|null $old_value
 * @property string|null $new_value
 * @property int $updated_by
 * @property Carbon|null $created_at
 * 
 * @property User $user
 *
 * @package App\Models
 */
class Update extends Model
{
	protected $table = 'updates';
	public $timestamps = false;

	protected $casts = [
		'user_id' => 'int',
		'updated_by' => 'int'
	];

	protected $fillable = [
		'user_id',
		'field_name',
		'old_value',
		'new_value',
		'updated_by'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'updated_by');
	}
}
