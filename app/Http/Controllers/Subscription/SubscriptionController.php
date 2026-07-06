<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Helpers\CentralLogics;
use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\Subscription;
use App\Models\PlanPriceSlab;
use App\Models\Employee;
use App\Models\Plan;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function index2()
    {
        $modules = Module::select('mdl_id','mdl_name','mdl_code','mdl_description')->get();
        $breadcrumbs = CentralLogics::getBreadcrumbs();
        return view('admin.subscriptions.index',compact('breadcrumbs','modules'));
    }

    public function indexOld(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        // Total employees
        $totalEmployees = Employee::where('emp_b_id', $businessId)->count();

        // All subscriptions
        $subscriptionAllData = Subscription::where('business_id', $businessId)->get();

        $subscriptionData = Subscription::where('business_id', $businessId)->orderBy('sub_id', 'desc')->firstOrFail();
        $priceSlabs = PlanPriceSlab::where('id', $subscriptionData->price_slab_id)->firstOrFail();
        $plan = Plan::where('plan_id', $subscriptionData->plan_id)->orderBy('plan_id', 'desc')->firstOrFail();

        // Loop through subscriptions and calculate active/inactive
        foreach ($subscriptionAllData as $subscription) {

            $startDate = Carbon::parse($subscription->start_date)->startOfDay();
            $endDate   = Carbon::parse($subscription->end_date)->endOfDay();


            // ACTIVE Employees
            $activeCount = Employee::where('emp_b_id', $businessId)
                ->whereDate('emp_date_of_joining', '<=', $endDate)
                ->where(function ($q) use ($startDate) {
                    $q->whereNull('emp_last_working_date')
                      ->orWhereDate('emp_last_working_date', '>', $startDate);
                })
                ->where('emp_status', 71)
                ->count();

            // INACTIVE Employees
            $inactiveCount = $totalEmployees - $activeCount;

            // attach values to object
            $subscription->active_count = $activeCount;
            $subscription->inactive_count = $inactiveCount;
        }

        return view('admin.subscriptions.index', compact(
            'user',
            'plan',
            'totalEmployees',
            'subscriptionAllData',
            'subscriptionData',
            'priceSlabs',
        ));
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $businessId = $user->emp_b_id;

        // TOTAL EMPLOYEES
        $totalEmployees = Employee::where('emp_b_id', $businessId)->count();

        // ALL SUBSCRIPTIONS
        $subscriptionAllData = Subscription::where('business_id', $businessId)
            ->orderBy('sub_id','desc')
            ->paginate(4);

        // CURRENT SUBSCRIPTION
        $subscriptionData = Subscription::where('business_id', $businessId)
            ->latest('sub_id')
            ->firstOrFail();

        $priceSlabs = PlanPriceSlab::findOrFail($subscriptionData->price_slab_id);
        $plan = Plan::findOrFail($subscriptionData->plan_id);

        // LOOP FOR ACTIVE/INACTIVE CALCULATION
        foreach ($subscriptionAllData as $subscription) {

            $startDate = Carbon::parse($subscription->start_date)->startOfDay();
            $endDate   = Carbon::parse($subscription->end_date)->endOfDay();

            /*
            ACTIVE EMPLOYEE CONDITIONS:
            ✔ Joined before period end
            ✔ Last working null OR after period start
            ✔ Status = 1 OR 71
            */

            $activeCount = Employee::where('emp_b_id', $businessId)
                ->whereDate('emp_date_of_joining', '<=', $endDate)
                ->where(function ($q) use ($startDate) {
                    $q->whereNull('emp_last_working_date')
                      ->orWhereDate('emp_last_working_date', '>', $startDate);
                })
                ->whereIn('emp_status', [1, 71])
                ->count();

            // INACTIVE
            $inactiveCount = $totalEmployees - $activeCount;

            // ATTACH VALUES
            $subscription->active_count   = $activeCount;
            $subscription->inactive_count = $inactiveCount;
        }

        /*
        |--------------------------------------------------------------------------
        | BILLING CALCULATION (LIVE)
        |--------------------------------------------------------------------------
        */

        $pricePerUser = $subscriptionData->price_per_user;
        $activeUsersForBilling = $subscriptionAllData->last()->active_count ?? 0;

        $amountPerCycle = $activeUsersForBilling * $pricePerUser;

        // NEXT BILLING DATE
        $nextBillingDate = $subscriptionData->end_date
            ? Carbon::parse($subscriptionData->end_date)->addDay()
            : null;

        if ($request->ajax()) {
            return view('admin.subscriptions.table', compact('subscriptionAllData'))->render();
        }

        return view('admin.subscriptions.index', compact(
            'user',
            'plan',
            'priceSlabs',
            'totalEmployees',
            'subscriptionAllData',
            'subscriptionData',
            'amountPerCycle',
            'activeUsersForBilling',
            'nextBillingDate'
        ));
    }
}
