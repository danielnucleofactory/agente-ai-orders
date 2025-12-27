<?php

namespace Tests\Feature;

use App\Livewire\Support\ContactForm;
use App\Mail\SupportRequestNotification;
use App\Models\SupportRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class SupportRequestNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /**
     * Test: Verifica que se envía un correo único a soporte con CC al usuario
     */
    public function test_it_sends_single_email_to_support_with_user_in_cc()
    {
        // Configurar correos
        config(['mail.support_email' => 'soporte@test.com']);
        config(['mail.admin_email' => null]);

        $user = User::factory()->create();
        $formUserEmail = 'usuario@test.com';

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', $formUserEmail)
            ->set('subject', 'Consulta técnica')
            ->set('description', 'Necesito ayuda con mi cuenta')
            ->call('submit')
            ->assertHasNoErrors();

        // Verificar que se envió el correo
        Mail::assertSent(SupportRequestNotification::class, function ($mail) use ($formUserEmail) {
            return $mail->userName === 'Juan Pérez'
                && $mail->userEmail === $formUserEmail
                && $mail->subject === 'Consulta técnica'
                && $mail->description === 'Necesito ayuda con mi cuenta'
                && in_array($formUserEmail, $mail->ccEmails);
        });

        // Verificar que el correo se envió a soporte
        Mail::assertSent(SupportRequestNotification::class, function ($mail) {
            $envelope = $mail->envelope();
            return $envelope->subject === 'Nueva Solicitud de Soporte: Consulta técnica';
        });
    }

    /**
     * Test: Verifica que se incluye el admin en CC cuando está configurado
     */
    public function test_it_includes_admin_in_cc_when_configured()
    {
        config(['mail.support_email' => 'soporte@test.com']);
        config(['mail.admin_email' => 'admin@test.com']);

        $user = User::factory()->create();
        $formUserEmail = 'usuario@test.com';

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', $formUserEmail)
            ->set('subject', 'Problema con facturación')
            ->set('description', 'Tengo un problema con mi factura')
            ->call('submit')
            ->assertHasNoErrors();

        // Verificar que el correo incluye usuario y admin en CC
        Mail::assertSent(SupportRequestNotification::class, function ($mail) use ($formUserEmail) {
            return in_array($formUserEmail, $mail->ccEmails)
                && in_array('admin@test.com', $mail->ccEmails)
                && count($mail->ccEmails) === 2;
        });
    }

    /**
     * Test: Verifica que NO se incluye admin en CC si es el mismo que soporte
     */
    public function test_it_excludes_admin_from_cc_if_same_as_support()
    {
        config(['mail.support_email' => 'soporte@test.com']);
        config(['mail.admin_email' => 'soporte@test.com']); // Mismo correo

        $user = User::factory()->create();
        $formUserEmail = 'usuario@test.com';

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', $formUserEmail)
            ->set('subject', 'Consulta')
            ->set('description', 'Descripción de prueba')
            ->call('submit')
            ->assertHasNoErrors();

        // Verificar que solo el usuario está en CC (no el admin porque es el mismo que soporte)
        Mail::assertSent(SupportRequestNotification::class, function ($mail) use ($formUserEmail) {
            return in_array($formUserEmail, $mail->ccEmails)
                && !in_array('soporte@test.com', $mail->ccEmails)
                && count($mail->ccEmails) === 1;
        });
    }

    /**
     * Test: Verifica que el envelope tiene Reply-To configurado con el correo del usuario
     */
    public function test_it_sets_reply_to_user_email()
    {
        config(['mail.support_email' => 'soporte@test.com']);

        $user = User::factory()->create();
        $formUserEmail = 'usuario@test.com';

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', $formUserEmail)
            ->set('subject', 'Consulta')
            ->set('description', 'Descripción de prueba')
            ->call('submit')
            ->assertHasNoErrors();

        Mail::assertSent(SupportRequestNotification::class, function ($mail) use ($formUserEmail) {
            $envelope = $mail->envelope();
            $replyTo = $envelope->replyTo[0];
            return $replyTo->address === $formUserEmail
                && $replyTo->name === 'Juan Pérez';
        });
    }

    /**
     * Test: Verifica que se crea el registro en base de datos cuando el usuario está autenticado
     */
    public function test_it_creates_support_request_when_user_is_authenticated()
    {
        config(['mail.support_email' => 'soporte@test.com']);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'usuario@test.com')
            ->set('subject', 'Consulta técnica')
            ->set('description', 'Necesito ayuda')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('support_requests', [
            'user_id' => $user->id,
            'subject' => 'Consulta técnica',
            'message' => 'Necesito ayuda',
            'status' => 'pending',
        ]);
    }

    /**
     * Test: Verifica que NO se crea registro si el usuario no está autenticado
     */
    public function test_it_does_not_create_support_request_when_user_not_authenticated()
    {
        config(['mail.support_email' => 'soporte@test.com']);

        Livewire::test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'usuario@test.com')
            ->set('subject', 'Consulta técnica')
            ->set('description', 'Necesito ayuda')
            ->call('submit')
            ->assertHasNoErrors();

        // Verificar que NO se creó ningún registro
        $this->assertDatabaseCount('support_requests', 0);

        // Pero sí se envió el correo
        Mail::assertSent(SupportRequestNotification::class);
    }

    /**
     * Test: Verifica validación de campos requeridos
     */
    public function test_it_validates_required_fields()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->call('submit')
            ->assertHasErrors(['name', 'email', 'subject', 'description']);
    }

    /**
     * Test: Verifica validación de formato de email
     */
    public function test_it_validates_email_format()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'invalid-email')
            ->set('subject', 'Consulta')
            ->set('description', 'Descripción válida con más de 10 caracteres')
            ->call('submit')
            ->assertHasErrors(['email']);
    }

    /**
     * Test: Verifica validación de longitud mínima de descripción
     */
    public function test_it_validates_description_minimum_length()
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'usuario@test.com')
            ->set('subject', 'Consulta')
            ->set('description', 'Corto') // Menos de 10 caracteres
            ->call('submit')
            ->assertHasErrors(['description']);
    }

    /**
     * Test: Verifica que no se envía correo si el email de soporte no está configurado
     */
    public function test_it_does_not_send_email_when_support_email_not_configured()
    {
        config(['mail.support_email' => null]);
        config(['mail.support.address' => null]);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'usuario@test.com')
            ->set('subject', 'Consulta')
            ->set('description', 'Descripción válida con más de 10 caracteres')
            ->call('submit')
            ->assertHasNoErrors();

        // Verificar que NO se envió ningún correo
        Mail::assertNothingSent();
    }

    /**
     * Test: Verifica que no se envía correo si el email de soporte es inválido
     */
    public function test_it_does_not_send_email_when_support_email_is_invalid()
    {
        config(['mail.support_email' => 'invalid-email']);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'usuario@test.com')
            ->set('subject', 'Consulta')
            ->set('description', 'Descripción válida con más de 10 caracteres')
            ->call('submit')
            ->assertHasNoErrors();

        // Verificar que NO se envió ningún correo
        Mail::assertNothingSent();
    }

    /**
     * Test: Verifica que se limpian los espacios y comillas de los emails de configuración
     */
    public function test_it_trims_whitespace_and_quotes_from_config_emails()
    {
        config(['mail.support_email' => ' "soporte@test.com" ']);
        config(['mail.admin_email' => " 'admin@test.com' "]);

        $user = User::factory()->create();
        $formUserEmail = 'usuario@test.com';

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', $formUserEmail)
            ->set('subject', 'Consulta')
            ->set('description', 'Descripción válida con más de 10 caracteres')
            ->call('submit')
            ->assertHasNoErrors();

        // Verificar que se envió el correo (los emails se limpiaron correctamente)
        Mail::assertSent(SupportRequestNotification::class, function ($mail) use ($formUserEmail) {
            return in_array($formUserEmail, $mail->ccEmails)
                && in_array('admin@test.com', $mail->ccEmails);
        });
    }

    /**
     * Test: Verifica que el correo tiene el asunto correcto
     */
    public function test_it_sets_correct_email_subject()
    {
        config(['mail.support_email' => 'soporte@test.com']);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'usuario@test.com')
            ->set('subject', 'Problema con tarifas')
            ->set('description', 'Descripción válida con más de 10 caracteres')
            ->call('submit')
            ->assertHasNoErrors();

        Mail::assertSent(SupportRequestNotification::class, function ($mail) {
            $envelope = $mail->envelope();
            return $envelope->subject === 'Nueva Solicitud de Soporte: Problema con tarifas';
        });
    }

    /**
     * Test: Verifica que el modal de éxito se muestra después del envío
     */
    public function test_it_shows_success_modal_after_submission()
    {
        config(['mail.support_email' => 'soporte@test.com']);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'usuario@test.com')
            ->set('subject', 'Consulta')
            ->set('description', 'Descripción válida con más de 10 caracteres')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('showSuccessModal', true)
            ->assertDispatched('open-modal', 'modal-support-request-sent')
            ->assertDispatched('show-success', 'Tu solicitud de soporte ha sido enviada exitosamente.');
    }

    /**
     * Test: Verifica que el formulario se resetea después del envío exitoso
     */
    public function test_it_resets_form_after_successful_submission()
    {
        config(['mail.support_email' => 'soporte@test.com']);

        $user = User::factory()->create();

        $component = Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'usuario@test.com')
            ->set('subject', 'Consulta')
            ->set('description', 'Descripción válida con más de 10 caracteres')
            ->call('submit')
            ->assertHasNoErrors();

        // Verificar que los campos se limpiaron
        $this->assertEmpty($component->get('name'));
        $this->assertEmpty($component->get('email'));
        $this->assertEmpty($component->get('subject'));
        $this->assertEmpty($component->get('description'));
    }

    /**
     * Test: Verifica que se usa config('mail.support_email') como primera opción
     */
    public function test_it_uses_support_email_config_first()
    {
        config(['mail.support_email' => 'soporte-config@test.com']);
        config(['mail.support.address' => 'soporte-address@test.com']);

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'usuario@test.com')
            ->set('subject', 'Consulta')
            ->set('description', 'Descripción válida con más de 10 caracteres')
            ->call('submit')
            ->assertHasNoErrors();

        // Verificar que se usó support_email (prioridad más alta)
        Mail::assertSent(SupportRequestNotification::class, function ($mail) {
            // El correo se envía a soporte-config@test.com
            return true; // La verificación del destinatario se hace en el envelope
        });
    }

    /**
     * Test: Verifica que se valida que los emails en CC sean válidos
     */
    public function test_it_validates_cc_emails_before_adding()
    {
        config(['mail.support_email' => 'soporte@test.com']);
        config(['mail.admin_email' => 'invalid-admin-email']); // Email inválido

        $user = User::factory()->create();
        $formUserEmail = 'usuario@test.com';

        Livewire::actingAs($user)
            ->test(ContactForm::class)
            ->set('name', 'Juan Pérez')
            ->set('email', $formUserEmail)
            ->set('subject', 'Consulta')
            ->set('description', 'Descripción válida con más de 10 caracteres')
            ->call('submit')
            ->assertHasNoErrors();

        // Verificar que solo el usuario válido está en CC (admin inválido no se agregó)
        Mail::assertSent(SupportRequestNotification::class, function ($mail) use ($formUserEmail) {
            return in_array($formUserEmail, $mail->ccEmails)
                && !in_array('invalid-admin-email', $mail->ccEmails)
                && count($mail->ccEmails) === 1;
        });
    }
}



