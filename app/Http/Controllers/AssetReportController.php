<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\AssetsBrand;
use App\Models\AssetCategory;
use Illuminate\Http\Request;
use App\Exports\AssetsExport;
use App\Models\AssetService;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssetsAssignedExport;
use App\Exports\AssetsScrapExport;
use App\Exports\AssetsReplaceExport;
use App\Exports\AssetsServiceExport;
use App\Exports\AssetsStockExport;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Designation;
use App\Models\Department;
use App\Models\Branch;

class AssetReportController extends Controller
{

    public function summary(Request $request)
    {

        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $assetTypes       = AssetType::where('assets_type_b_id', $b_id)->where('is_active', 1)->get();
        $assetTags        = Asset::where('assets_b_id', $b_id)->select('id', 'asset_tag', 'model_number', 'serial_number', 'vendor_name')->get();
        $assetBrands      = AssetsBrand::where('br_b_id', $b_id)->get();
        $assetCategories  = AssetCategory::where('ac_b_id', $b_id)->get();

        return view('admin.assets.reports.summary', compact(
            'assetTypes',
            'assetCategories',
            'assetBrands',
            'assetTags',
        ));
    }

    public function stock(Request $request)
    {
        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $assetTypes       = AssetType::where('assets_type_b_id', $b_id)->where('is_active', 1)->get();
        $assetTags        = Asset::where('assets_b_id', $b_id)->select('id', 'asset_tag', 'model_number', 'serial_number', 'vendor_name')->get();
        $assetCategories  = AssetCategory::where('ac_b_id', $b_id)->get();
        $assetBrands      = AssetsBrand::where('br_b_id', $b_id)->get();

        return view('admin.assets.reports.stock', compact(
            'assetTypes',
            'assetBrands',
            'assetCategories',
            'assetTags',
        ));
    }

    public function assigned(Request $request)
    {
        $user = Auth::user();
        $b_id = $user->emp_b_id;
        $assetTypes      = AssetType::where('assets_type_b_id', $b_id)->where('is_active', 1)->get();
        $assetTags       = Asset::where('assets_b_id', $b_id)->select('id', 'asset_tag', 'model_number', 'serial_number', 'vendor_name')->get();
        $assetBrands     = AssetsBrand::where('br_b_id', $b_id)->get();
        $assetCategories = AssetCategory::where('ac_b_id', $b_id)->get();
        $employees    = Employee::where('emp_b_id', $b_id)->get();
        $designations = Designation::where('dg_b_id', $b_id)->get();
        $departments  = Department::where('d_b_id', $b_id)->get();
        $branches     = Branch::where('br_b_id', $b_id)->get();

        // Pass everything to view
        return view('admin.assets.reports.assigned', compact(
            'assetTypes',
            'assetBrands',
            'assetCategories',
            'assetTags',
            'employees',
            'designations',
            'departments',
            'branches'
        ));
    }


    public function service(Request $request)
    {

        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $assetTypes       = AssetType::where('assets_type_b_id', $b_id)->where('is_active', 1)->get();
        $assetTags        = Asset::where('assets_b_id', $b_id)->select('id', 'asset_tag', 'model_number', 'serial_number', 'vendor_name')->get();
        $assetBrands      = AssetsBrand::where('br_b_id', $b_id)->get();
        $assetCategories  = AssetCategory::where('ac_b_id', $b_id)->get();
        $serviceLocations = Branch::where('br_b_id', $b_id)->get();
        $employees    = Employee::where('emp_b_id', $b_id)->get();
        $designations = Designation::where('dg_b_id', $b_id)->get();
        $departments  = Department::where('d_b_id', $b_id)->get();
        $branches     = Branch::where('br_b_id', $b_id)->get();

        return view('admin.assets.reports.service', compact(
            'assetTypes',
            'assetBrands',
            'assetCategories',
            'assetTags',
            'serviceLocations',
            'employees',
            'designations',
            'departments',
            'branches'
        ));
    }

    public function scrap(Request $request)
    {

        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $assetTypes       = AssetType::where('assets_type_b_id', $b_id)->where('is_active', 1)->get();
        $assetTags        = Asset::where('assets_b_id', $b_id)->select('id', 'asset_tag', 'model_number', 'serial_number', 'vendor_name')->get();
        $assetBrands      = AssetsBrand::where('br_b_id', $b_id)->get();
        $assetCategories  = AssetCategory::where('ac_b_id', $b_id)->get();

        return view('admin.assets.reports.scrap', compact(
            'assetTypes',
            'assetBrands',
            'assetCategories',
            'assetTags',

        ));
    }

    public function replace(Request $request)
    {

        $user = Auth::user();
        $b_id = $user->emp_b_id;

        $assetTypes       = AssetType::where('assets_type_b_id', $b_id)->where('is_active', 1)->get();
        $assetTags        = Asset::where('assets_b_id', $b_id)->select('id', 'asset_tag', 'model_number', 'serial_number', 'vendor_name')->get();
        $assetBrands      = AssetsBrand::where('br_b_id', $b_id)->get();
        $assetCategories  = AssetCategory::where('ac_b_id', $b_id)->get();

        return view('admin.assets.reports.replace', compact(
            'assetTypes',
            'assetBrands',
            'assetCategories',
            'assetTags',
        ));
    }

    public function exportSummary(Request $request)
    {
        $filters = $request->only(['assetType', 'category', 'assetTag', 'brand', 'dateRange']);
        return Excel::download(new AssetsExport($filters), 'assets_summary_report.xlsx');
    }



    public function exportStock(Request $request)
    {
        $filters = $request->only(['assetType', 'assetTag', 'category', 'modelNumber', 'vendorName', 'brand', 'serialNumber', 'dateRange']);
        $currentDate = Carbon::now()->format('d-M-Y_H-i-A'); // e.g., 23-Sep-2025_02-29-PM
        return Excel::download(new AssetsStockExport($filters), "Asset_Stock_Report.xlsx");
    }

    public function exportAssigned(Request $request)
    {
        $filters = $request->only(['assetType', 'category', 'assetTag', 'brand', 'vendorName', 'modelNumber', 'serialNumber', 'employee', 'designation', 'department', 'branch', 'dateRange']);
        return Excel::download(new AssetsAssignedExport($filters), 'assets_assigned_report.xlsx');
    }


    public function exportScrap(Request $request)
    {
        $filters = $request->only(['assetType', 'category', 'assetTag', 'brand', 'modelNumber', 'vendorName', 'serialNumber', 'dateRange']);
        return Excel::download(new AssetsScrapExport($filters), 'assets_scrap_report.xlsx');
    }

    public function exportReplace(Request $request)
    {
        $filters = $request->only(['assetType', 'category', 'assetTag', 'brand', 'modelNumber', 'vendorName', 'serialNumber', 'dateRange']);
        return Excel::download(new AssetsReplaceExport($filters), 'assets_replace_report.xlsx');
    }

    public function exportService(Request $request)
    {
        $filters = $request->only(['assetType', 'category', 'assetTag', 'brand', 'modelNumber', 'vendorName', 'serialNumber', 'employee', 'designation', 'department', 'branch', 'serviceType', 'serviceLocation', 'dateRange',]);

        return Excel::download(new AssetsServiceExport($filters), 'assets_service_report.xlsx');
    }
}
