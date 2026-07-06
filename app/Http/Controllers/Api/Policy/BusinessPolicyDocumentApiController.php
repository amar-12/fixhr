<?php

namespace App\Http\Controllers\Api\Policy;

use App\Http\Controllers\Controller;
use App\Models\BusinessPolicyDocument;
use ChandraHemant\HtkcUtils\ReturnHelper;
use App\Http\Resources\BusinessPolicyResource;
use App\Models\BusinessPolicyFolder;
use Illuminate\Support\Facades\Auth;


class BusinessPolicyDocumentApiController extends Controller
{


    // public function index()
    // {
    //     $business_id = 44;

    //     // Eager load documents via relationship
    //     $folders = BusinessPolicyFolder::with('documents')
    //         ->where('pbf_b_id', $business_id)
    //         ->get();

    //     // Flatten all documents from related folders
    //     $documents = $folders->flatMap(function ($folder) {
    //         return $folder->documents;
    //     });

    //     if ($documents->isNotEmpty()) {
    //         return ReturnHelper::jsonApiReturn(BusinessPolicyResource::collection($documents)->all());
    //     }

    //     return response()->json(['data' => [], 'status' => true]);
    // }


    // public function index()
    // {
    //     $business_id = 44;

    //     // Eager load documents with folder relationship
    //     $folders = BusinessPolicyFolder::with('documents')
    //         ->where('pbf_b_id', $business_id)
    //         ->get();

    //     // Flatten and collect all documents
    //     $documents = $folders->flatMap(function ($folder) {
    //         return $folder->documents->map(function ($doc) use ($folder) {
    //             $doc->folder_name = $folder->bpf_name;
    //             return $doc;
    //         });
    //     });

    //     if ($documents->isNotEmpty()) {
    //         // Group documents by folder name
    //         $grouped = $documents->groupBy('folder_name')->map(function ($group) {
    //             return BusinessPolicyResource::collection($group)->resolve();
    //         });

    //         return response()->json([
    //             'result' => [$grouped],
    //             'status' => true,
    //         ]);
    //     }

    //     return response()->json([
    //         'result' => [],
    //         'status' => true
    //     ]);
    // }


    public function index()
    {
        $business_id = Auth::user()->emp_b_id ?? 44;
        $businessFolders = BusinessPolicyFolder::where('pbf_b_id', $business_id)->pluck('bpf_id');
        $documents = BusinessPolicyDocument::with('folder')
            ->whereIn('bpd_folder_id', $businessFolders)
            ->where('bpd_status', 1)
            ->orderByDesc('created_at')
            ->get();

        // Group documents by folder
        $grouped = $documents->groupBy('bpd_folder_id')->map(function ($docs) {
            $folder = $docs->first()->folder;

            return [
                'folder_name' => $folder->bpf_name ?? 'Unknown',
                'folder_id' => $folder->bpf_id ?? null,
                'versions' => $docs->map(function ($doc) {
                    return [
                        'bpd_version' => $doc->bpd_version,
                        'bpd_file_name' => $doc->bpd_file_name,
                        'bpd_with_effect_from' => $doc->bpd_with_effect_from,
                           'bpd_file_path' => asset($doc->bpd_file_path),
                        'bpd_status' => $doc->bpd_status,
                    ];
                })->values()
            ];
        })->values();

        return response()->json([
            'result' => $grouped,
            'status' => true
        ]);
    }
}
