<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Announcement;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use App\Helpers\NotificationHelper;
use Illuminate\Support\Facades\Log;
use App\Helpers\CentralLogics;

class SendBirthdayNotification extends Command
{
    protected $signature = 'app:birthday-reminder';
    protected $description = 'Send birthday reminder notifications';

    public function handle()
    {
        // 🎂 Get today's birthday employees (ONLY ACTIVE)
        $employees = Employee::whereMonth('emp_dob', now()->month)
            ->whereDay('emp_dob', now()->day)
            ->where('emp_status', 71)
            ->get();

        // ✅ No birthdays found
        if ($employees->isEmpty()) {
            $this->info('No birthdays today.');
            return;
        }

        $serviceAccountPath = public_path('fixhr-app-firebase.json');

        // 👉 Group by business
        $groupedEmployees = $employees->groupBy('emp_b_id');
        $emailStats = ['sent' => 0, 'failed' => 0];

        foreach ($groupedEmployees as $businessId => $emps) {

            $names = $emps->pluck('emp_full_name')->toArray();

            // ----------------------------------------------------------
            // 🎂 STEP 1: Birthday push notification (per birthday employee)
            // ----------------------------------------------------------
            foreach ($emps as $emp) {

                $title = '🎂 Happy Birthday!';

                $body = "🎉 Today’s Birthday: "
                    . implode(', ', $names)
                    . " — Wishing you all a blessed year filled with happiness, good health, and success! 🙏✨";

                $additionalData = [
                    'user_id' => $emp->emp_id,
                    'notification_type' => 'birthday',
                    'route' => '/AnnouncementScreen'
                ];

                // 🔔 Push Notification
                if (
                    $emp->emp_is_notification_enabled == '1'
                    && !empty($emp->emp_fcm_token)
                ) {
                    FirebaseNotification::sendPushNotification(
                        $title,
                        $body,
                        $emp->emp_fcm_token,
                        $serviceAccountPath,
                        config('credentials.FIREBASE_MESSAGING_CONFIG'),
                        $additionalData
                    );
                }

                // 🗂️ Save Notification
                // NotificationHelper::saveNotification(
                //     $emp->emp_id,
                //     $emp->emp_id,
                //     $title,
                //     $body,
                //     $additionalData
                // );

                $mailVariables = [
                    'employee_name' => $emp->emp_full_name ?? 'Employee',
                    'emp_code' => $emp->emp_code ?? 'N/A',
                    'request_type' => 'Birthday Wishes',
                    'from_date' => now()->format('Y-m-d'),
                    'to_date' => now()->format('Y-m-d'),
                    'company_name' => $emp->fh_business->b_name ?? 'Our Company',
                    'reference_no' => 'BDAY-' . now()->format('Ymd') . '-' . $emp->emp_id,
                    'current_date' => now()->format('d F Y'),
                ];

                // === CALL sendDynamicMail EXACTLY LIKE YOUR CODE ===
                $mailSent = CentralLogics::sendDynamicMail(
                    $businessId,
                    1,
                    'custom',
                    $emp->emp_email,
                    $mailVariables
                );

                if ($mailSent) {
                    $emailStats['sent']++;
                    $this->info("✅ Email sent to: {$emp->emp_email} ({$emp->emp_full_name})");
                    
                    // Log success like your code
                    Log::info('Birthday mail sent to: ' . $emp->emp_email, [
                        'employee_name' => $emp->emp_full_name,
                        'business_id' => $businessId,
                        'variables' => $mailVariables
                    ]);
                    // dd($mailVariables, $mailSent);
                } else {
                    $this->warn("⚠️ Email failed for: {$emp->emp_email}");
                    
                    Log::warning('Birthday mail failed for: ' . $emp->emp_email);
                }
            }

            // ----------------------------------------------------------
            // 📢 STEP 2: Announcement (ONCE per business, outside emp loop)
            // ----------------------------------------------------------
            $alreadyExists = Announcement::where('ann_b_id', $businessId)
                ->where('ann_category', 'Birthday Announcement')
                ->whereDate('created_at', now())
                ->exists();


            if (!$alreadyExists) {

                // ✅ Create Announcement
                $announcement = Announcement::create([
                    'ann_b_id'     => $businessId,
                    'ann_title'    => '🎂 Birthday Announcement',
                    'ann_message'  => 'Today\'s Birthday: ' . implode(', ', $names) . ' 🎉',
                    'ann_user_id'  => null,
                    'ann_role_id'  => 0,
                    'ann_category' => 'Birthday Announcement',
                    'ann_image'    => null,
                ]);

                // 👉 Same business, ALL active employees
                $recipients = Employee::where('emp_b_id', $businessId)
                    ->where('emp_status', 71)
                    ->get();

                $annTitle = '📢 Birthday Announcement';

                $annBody = 'Today\'s Birthday: '
                    . implode(', ', $names)
                    . ' 🎉';

                $annData = [
                    'notification_type' => 'announcement',
                    'announcement_id'  => $announcement->ann_id,
                    'route'            => '/AnnouncementScreen',
                ];

                foreach ($recipients as $user) {

                    // 🔔 Push Notification
                    if (
                        $user->emp_is_notification_enabled == '1'
                        && !empty($user->emp_fcm_token)
                    ) {
                        FirebaseNotification::sendPushNotification(
                            $annTitle,
                            $annBody,
                            $user->emp_fcm_token,
                            $serviceAccountPath,
                            config('credentials.FIREBASE_MESSAGING_CONFIG'),
                            $annData
                        );
                    }

                    // 🗂️ Save Notification
                    NotificationHelper::saveNotification(
                        null,
                        $user->emp_id,
                        $annTitle,
                        $annBody,
                        $annData
                    );
                }
            }
        }

        $this->info('✅ Birthday + Announcement notifications sent successfully!');
    }
}
