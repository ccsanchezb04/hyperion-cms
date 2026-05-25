<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            // Create some sample notifications for each user
            Notification::create([
                'noti_nmtype' => Notification::TYPE_CONTENT_PUBLISHED,
                'noti_dsdata' => [
                    'content_title' => 'Welcome to Hyperion CMS',
                    'content_id' => 1,
                ],
                'noti_iduser' => $user->user_iduser,
                'noti_boread' => false,
            ]);

            Notification::create([
                'noti_nmtype' => Notification::TYPE_SYSTEM,
                'noti_dsdata' => [
                    'message' => 'Welcome to the system!',
                ],
                'noti_iduser' => $user->user_iduser,
                'noti_boread' => true,
            ]);

            // Only give some users role change notifications
            if ($user->isAdmin()) {
                Notification::create([
                    'noti_nmtype' => Notification::TYPE_ROLE_CHANGED,
                    'noti_dsdata' => [
                        'old_role' => 'editor',
                        'new_role' => 'admin',
                    ],
                    'noti_iduser' => $user->user_iduser,
                    'noti_boread' => false,
                ]);
            }
        }

        $this->command->info('✅ Notifications seeded successfully');
    }
}
