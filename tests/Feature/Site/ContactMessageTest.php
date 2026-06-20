<?php

namespace Tests\Feature\Site;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::setValue('site.contact.notify_email', 'admin@hyperion.local', 'site');
    }

    public function test_valid_submission_persists_and_dispatches_email(): void
    {
        Mail::fake();

        $response = $this->from('/')->post('/contact', [
            'nombre' => 'Ana Test',
            'email' => 'ana@example.com',
            'asunto' => 'cotizacion',
            'mensaje' => 'Hola, quiero cotizar un seguro de vida por favor.',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('contact_status', 'sent');

        $this->assertDatabaseHas('hycms_contact_messages', [
            'cmsg_nmname' => 'Ana Test',
            'cmsg_dsemai' => 'ana@example.com',
            'cmsg_cdsubj' => 'cotizacion',
        ]);

        Mail::assertSent(ContactMessageReceived::class, function ($mail) {
            return $mail->hasTo('admin@hyperion.local');
        });
    }

    public function test_missing_fields_fail_validation(): void
    {
        $response = $this->from('/')->post('/contact', []);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['nombre', 'email', 'asunto', 'mensaje']);
        $this->assertDatabaseCount('hycms_contact_messages', 0);
    }

    public function test_invalid_email_fails_validation(): void
    {
        $response = $this->from('/')->post('/contact', [
            'nombre' => 'Ana',
            'email' => 'not-an-email',
            'asunto' => 'cotizacion',
            'mensaje' => 'Mensaje suficientemente largo para validar.',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_invalid_subject_fails_validation(): void
    {
        $response = $this->from('/')->post('/contact', [
            'nombre' => 'Ana',
            'email' => 'ana@example.com',
            'asunto' => 'arbitrario',
            'mensaje' => 'Mensaje suficientemente largo para validar.',
        ]);

        $response->assertSessionHasErrors('asunto');
    }

    public function test_short_message_fails_validation(): void
    {
        $response = $this->from('/')->post('/contact', [
            'nombre' => 'Ana',
            'email' => 'ana@example.com',
            'asunto' => 'cotizacion',
            'mensaje' => 'corto',
        ]);

        $response->assertSessionHasErrors('mensaje');
    }

    public function test_email_failure_does_not_break_submission(): void
    {
        // Force the mailer to throw when called.
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $response = $this->from('/')->post('/contact', [
            'nombre' => 'Ana',
            'email' => 'ana@example.com',
            'asunto' => 'cotizacion',
            'mensaje' => 'Mensaje suficientemente largo para validar.',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('contact_status', 'sent');
        $this->assertDatabaseCount('hycms_contact_messages', 1);
    }

    public function test_marking_as_read_sets_timestamp(): void
    {
        $message = ContactMessage::create([
            'cmsg_nmname' => 'X',
            'cmsg_dsemai' => 'x@x.com',
            'cmsg_cdsubj' => 'otros',
            'cmsg_dsmess' => 'lorem ipsum dolor sit amet.',
        ]);

        $this->assertFalse($message->isRead());

        $message->markAsRead();

        $this->assertTrue($message->fresh()->isRead());
    }
}
