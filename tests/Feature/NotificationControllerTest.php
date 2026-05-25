<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_index_returns_user_notifications()
    {
        $user = User::first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/notifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'noti_idnoti',
                        'noti_nmtype',
                        'noti_dsdata',
                        'noti_boread',
                    ]
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total',
                    'unread_count',
                ]
            ]);
    }

    public function test_index_returns_only_unread_when_requested()
    {
        $user = User::first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/notifications?unread=true');

        $response->assertStatus(200);

        // Verify all returned notifications are unread
        $notifications = $response->json('data');
        foreach ($notifications as $notification) {
            $this->assertFalse($notification['noti_boread']);
        }
    }

    public function test_show_returns_specific_notification()
    {
        $user = User::first();
        $notification = $user->notifications()->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/notifications/{$notification->noti_idnoti}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'noti_idnoti' => $notification->noti_idnoti,
                ]
            ]);
    }

    public function test_mark_as_read_marks_notification_as_read()
    {
        $user = User::first();
        $notification = $user->notifications()->unread()->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/notifications/{$notification->noti_idnoti}/mark-read");

        $response->assertStatus(200);

        $this->assertTrue($notification->fresh()->noti_boread);
    }

    public function test_mark_all_as_read_marks_all_as_read()
    {
        $user = User::first();
        $token = $user->createToken('test-token')->plainTextToken;

        $unreadCountBefore = $user->unreadNotificationsCount();

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/notifications/mark-all-read');

        $response->assertStatus(200);

        $unreadCountAfter = $user->unreadNotificationsCount();
        $this->assertEquals(0, $unreadCountAfter);
    }

    public function test_unread_count_returns_correct_count()
    {
        $user = User::first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'unread_count',
                ]
            ]);

        $unreadCount = $response->json('data.unread_count');
        $this->assertEquals($user->unreadNotificationsCount(), $unreadCount);
    }

    public function test_destroy_deletes_notification()
    {
        $user = User::first();
        $notification = $user->notifications()->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/notifications/{$notification->noti_idnoti}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('hycms_notifications', [
            'noti_idnoti' => $notification->noti_idnoti,
        ]);
    }

    public function test_user_can_only_access_own_notifications()
    {
        $user1 = User::first();
        $user2 = User::where('user_iduser', '!=', $user1->user_iduser)->first();
        $notification = $user1->notifications()->first();
        $token = $user2->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/notifications/{$notification->noti_idnoti}");

        $response->assertStatus(404);
    }
}
