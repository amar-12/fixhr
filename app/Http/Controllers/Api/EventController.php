<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use ChandraHemant\HtkcUtils\CommonUtils;
use Illuminate\Support\Facades\Auth;
use App\Helpers\Aws\AwsHelper;

class EventController extends Controller
{
	protected $awsHelper;
	protected $bucket;
    protected $user;

	public function __construct(AwsHelper $awsHelper)
	{
	    $this->awsHelper = $awsHelper;
	    $this->bucket = 'fixhr-uploads'; // S3 bucket name
        $this->user = Auth::user();
	}

    /**
     * Display a listing of events
     */

    public function index(Request $request)
    {
        $user = Auth::user();

        $page  = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $myEvents = $request->input('my_event');

        if (isset($myEvents) && $myEvents == 'Yes') {
            $query = Event::query()
            ->where('ev_b_id', $user->emp_b_id)
            ->where('ev_emp_id', $user->emp_id)
            ->whereNull('deleted_at');
        } else {
            $query = Event::query()
            ->where('ev_b_id', $user->emp_b_id)
            ->whereNull('deleted_at');
        }

        // Optional: From Date
        if ($request->filled('from_date')) {
            $query->whereDate(
                'created_at',
                '>=',
                Carbon::createFromFormat('d M, Y', $request->from_date)->startOfDay()
            );
        }

        // Optional: To Date
        if ($request->filled('to_date')) {
            $query->whereDate(
                'created_at',
                '<=',
                Carbon::createFromFormat('d M, Y', $request->to_date)->endOfDay()
            );
        }

        // Optional: Last 15 Days
        if ($request->input('last_15_days')) {
            $query->whereDate('created_at', '>=', Carbon::now()->subDays(15));
        }

        $events = $query
            ->orderBy('ev_id', 'DESC')
            ->paginate($limit, ['*'], 'page', $page);

        // 🔹 Format images (img1, img2...)
        $events->getCollection()->transform(function ($event) {
            $formattedImages = [];
            if (!empty($event->ev_images)) {
                $images = explode(',', $event->ev_images);
                foreach ($images as $index => $img) {
                    $formattedImages['img' . ($index + 1)] = $img;
                }
            }
            $event->ev_images = $formattedImages;

            return $event;
        });

        return response()->json([
            'result' => [
                'data' => $events->items(),
                'pagination' => [
                    'total'         => $events->total(),
                    'count'         => $events->count(),
                    'per_page'      => $events->perPage(),
                    'current_page'  => $events->currentPage(),
                    'last_pages'    => $events->lastPage(),
                ]
            ],
            'status' => true
        ], 200);
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
	{
        $user = Auth::user();
        if(!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }

	    // Validation
	    $validator = Validator::make($request->all(), [
	        'title' => 'required|string|max:255',
	        'description' => 'required|string',
	        'images' => 'nullable|array',
	        'images.*' => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf|max:2048'
	    ]);

	    if ($validator->fails()) {
	        return response()->json([
	            'status' => false,
	            'errors' => $validator->errors()
	        ], 422);
	    }

	    $uploadedPhotos = [];
        if (config('app.store_on_s3')) {
            if (!empty($request->images)) {
                foreach ($request->images as $file) {
                    $imagePath = 'Events/' . $user->fh_business->b_unique_id . '/' . time() . '_' . $file->getClientOriginalName();

                    $uploadResult = $this->awsHelper->uploadFileToS3(
                        $this->bucket,
                        $imagePath,
                        $file
                    );

                    if (!empty($uploadResult['ObjectURL'])) {
                        $uploadedPhotos[] = $uploadResult['ObjectURL'];
                    }
                }
            }

        } else {
            if ($request->hasFile('images')) {
                $uploadedPath = CommonUtils::uploadFiles($request, 'images', 'Events', [
                    'prefix' => 'event',
                    'isApi' => true
                ]);

                if (!empty($uploadedPath)) {
                    foreach ($uploadedPath as $path) {
                        $uploadedPhotos[] = url($path);
                    }
                }
            }
        }

	    // Create Event
	    $event = Event::create([
	        'ev_emp_id' => $user->emp_id ?? 666,
	        'ev_b_id' => $user->emp_b_id ?? 44,
	        'ev_title' => $request->title ?? '',
	        'ev_description' => $request->description ?? '',
	        'ev_images' => !empty($uploadedPhotos) ? implode(',', $uploadedPhotos) : null
	    ]);

	    return response()->json([
	        'status' => true,
	        'message' => 'Event created successfully',
	        'data' => $event
	    ], 200);
	}

    /**
     * Remove the specified event
     */
    public function destroy($id)
    {
        $event = Event::where('ev_id', $id)->whereNull('deleted_at')->first();

        if (!$event) {
            return response()->json([
                'status' => false,
                'result' => false,
                'message' => 'Event not found'
            ], 404);
        }

        $event->delete();

        return response()->json([
            'status' => true,
            'result' => true,
            'message' => 'Event deleted successfully'
        ], 200);
    }
}