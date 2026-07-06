<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PolicyWeekOff
 *
 * @property int $pwo_id
 * @property string|null $pwo_name
 * @property string|null $pwo_day_ids
 * @property string|null $pwo_recurrence_day_ids
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class PolicyWeekOff extends Model
{
	protected $table = 'policy_week_off';
	protected $primaryKey = 'pwo_id';

	protected $fillable = [
        'pwo_b_id',
        'pwo_is_unpaid',
		'pwo_name',
		'pwo_day_ids',
		'pwo_recurrence_day_ids'
	];


    public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pwo_b_id');
	}

    // public function getDays($pwo_day_ids)
    // {
    //     // Decode the JSON array of day IDs
    //     $pwoDayIds = json_decode($pwo_day_ids, true);

    //     // Fetch the m_name values related to the m_id values from MasterTable
    //     return MasterTable::whereIn('m_id', $pwoDayIds)->pluck('m_name')->toArray(); // Return as an array
    // }

    public function getDays($pwo_day_ids)
    {
        // Step 1: Ensure $pwo_day_ids becomes an array
        if (is_string($pwo_day_ids)) {
            $decoded = json_decode($pwo_day_ids, true);
            $pwoDayIds = is_array($decoded) ? $decoded : explode(',', $pwo_day_ids);
        } elseif (is_array($pwo_day_ids)) {
            $pwoDayIds = [substr($pwo_day_ids[0], 1, 3)];
        } else {
            $pwoDayIds = [];
        }

        // Step 2: Fetch day names
        if (!empty($pwoDayIds)) {
            return MasterTable::whereIn('m_id', $pwoDayIds)->pluck('m_name')->toArray();
        }

        return [];
    }

    // public function getWeek($decodedIds)
    // {
    //     $decodedIds = (array)$decodedIds;

    //     // Decode unpaid top-level ids from this model (can be JSON, CSV or array)
    //     $unpaidRaw = $this->pwo_is_unpaid ?? null;
    //     $unpaidIds = [];
    //     if ($unpaidRaw !== null) {
    //         if (is_array($unpaidRaw)) {
    //             $unpaidIds = $unpaidRaw;
    //         } elseif (is_string($unpaidRaw)) {
    //             $decoded = json_decode($unpaidRaw, true);
    //             if (is_array($decoded)) {
    //                 $unpaidIds = $decoded;
    //             } elseif (strlen(trim($unpaidRaw)) > 0) {
    //                 // fallback to comma separated
    //                 $unpaidIds = array_map('trim', explode(',', $unpaidRaw));
    //             }
    //         } else {
    //             $unpaidIds = [$unpaidRaw];
    //         }
    //     }

    //     // Defensive flattening of nested child ids (avoid array_merge(... ) issues)
    //     $nested = [];
    //     foreach (array_values($decodedIds) as $val) {
    //         if (is_array($val)) {
    //             $nested = array_merge($nested, $val);
    //         } elseif ($val !== null && $val !== '') {
    //             $nested[] = $val;
    //         }
    //     }

    //     // Flatten all keys (top-level and nested) to fetch all m_name values in one query
    //     $allIds = array_merge(array_keys($decodedIds), $nested);

    //     // Fetch all m_name values at once, indexed by m_id
    //     $masterNames = MasterTable::whereIn('m_id', $allIds)->pluck('m_name', 'm_id')->toArray();

    //     $result = [];

    //     foreach ($decodedIds as $key => $item) {
    //         // Skip unpaid top-level keys if configured
    //         if (!empty($unpaidIds)) {
    //             // Compare as strings to be tolerant of types
    //             $strKey = (string)$key;
    //             $normalizedUnpaid = array_map('strval', $unpaidIds);
    //             if (in_array($strKey, $normalizedUnpaid, true)) {
    //                 continue;
    //             }
    //         }
    //         // Map the top-level m_name
    //         if (isset($masterNames[$key])) {
    //             $mName = $masterNames[$key];
    //             $result[$mName] = [];

    //             // Map nested m_name values
    //             foreach ($item as $itemId) {
    //                 if (isset($masterNames[$itemId])) {
    //                     $result[$mName][] = $masterNames[$itemId];
    //                 }
    //             }
    //         }
    //     }
    //     return $result;
    // }


    public function getWeek($decodedIds)
    {
        $decodedIds = (array)$decodedIds;
        // Flatten all keys (top-level and nested) to fetch all m_name values in one query
        $allIds = array_merge(
            array_keys($decodedIds),          // Top-level keys
            array_merge(...array_values($decodedIds)) // Nested values
        );

        // Fetch all m_name values at once, indexed by m_id
        $masterNames = MasterTable::whereIn('m_id', $allIds)->pluck('m_name', 'm_id')->toArray();

        $result = [];

        foreach ($decodedIds as $key => $item) {
            // Map the top-level m_name
            if (isset($masterNames[$key])) {
                $mName = $masterNames[$key];
                $result[$mName] = [];

                // Map nested m_name values
                foreach ($item as $itemId) {
                    if (isset($masterNames[$itemId])) {
                        $result[$mName][] = $masterNames[$itemId];
                    }
                }
            }
        }
        return $result;
    }
}
