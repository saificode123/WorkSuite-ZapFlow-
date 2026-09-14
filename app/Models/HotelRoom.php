<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HotelRoom extends BaseModel
{
    use HasCompany;

    protected $table = 'hotel_rooms';
 
    protected $fillable = [
        'company_id',
        'hotel_id',
        'room_number',
        'room_type',
        'floor',
        'capacity',
        'gender_restriction',
        'is_available',
        'notes',
        'added_by',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'capacity' => 'integer',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(RoomAllocation::class, 'hotel_room_id');
    }

    public function activeAllocations(): HasMany
    {
        return $this->hasMany(RoomAllocation::class, 'hotel_room_id')
            ->whereIn('status', ['reserved', 'checked_in']);
    }

    /**
     * Count available capacity (capacity minus active allocations).
     */
    public function getAvailableCapacityAttribute(): int
    {
        return max(0, $this->capacity - $this->activeAllocations()->count());
    }
}
