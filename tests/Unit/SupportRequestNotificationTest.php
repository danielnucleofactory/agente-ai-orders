<?php

namespace Tests\Unit;

use App\Mail\SupportRequestNotification;
use Illuminate\Mail\Mailables\Address;
use Tests\TestCase;

class SupportRequestNotificationTest extends TestCase
{
    /**
     * Test: Verifica que el envelope tiene el asunto correcto
     */
    public function test_envelope_has_correct_subject()
    {
        $mail = new SupportRequestNotification(
            'Juan Pérez',
            'usuario@test.com',
            'Consulta técnica',
            'Descripción de prueba'
        );

        $envelope = $mail->envelope();

        $this->assertEquals('Nueva Solicitud de Soporte: Consulta técnica', $envelope->subject);
    }

    /**
     * Test: Verifica que el envelope tiene Reply-To configurado correctamente
     */
    public function test_envelope_has_correct_reply_to()
    {
        $mail = new SupportRequestNotification(
            'Juan Pérez',
            'usuario@test.com',
            'Consulta técnica',
            'Descripción de prueba'
        );

        $envelope = $mail->envelope();

        $this->assertCount(1, $envelope->replyTo);
        $this->assertInstanceOf(Address::class, $envelope->replyTo[0]);
        $this->assertEquals('usuario@test.com', $envelope->replyTo[0]->address);
        $this->assertEquals('Juan Pérez', $envelope->replyTo[0]->name);
    }

    /**
     * Test: Verifica que el envelope tiene CC cuando se proporcionan emails válidos
     */
    public function test_envelope_has_cc_when_valid_emails_provided()
    {
        $ccEmails = ['usuario@test.com', 'admin@test.com'];

        $mail = new SupportRequestNotification(
            'Juan Pérez',
            'usuario@test.com',
            'Consulta técnica',
            'Descripción de prueba',
            $ccEmails
        );

        $envelope = $mail->envelope();

        $this->assertNotNull($envelope->cc);
        $this->assertCount(2, $envelope->cc);
        $this->assertEquals('usuario@test.com', $envelope->cc[0]->address);
        $this->assertEquals('admin@test.com', $envelope->cc[1]->address);
    }

    /**
     * Test: Verifica que el envelope NO tiene CC cuando no se proporcionan emails
     */
    public function test_envelope_has_no_cc_when_no_emails_provided()
    {
        $mail = new SupportRequestNotification(
            'Juan Pérez',
            'usuario@test.com',
            'Consulta técnica',
            'Descripción de prueba',
            []
        );

        $envelope = $mail->envelope();

        // Laravel puede devolver null o un array vacío dependiendo de la versión
        $this->assertTrue($envelope->cc === null || (is_array($envelope->cc) && empty($envelope->cc)));
    }

    /**
     * Test: Verifica que el envelope filtra emails inválidos en CC
     */
    public function test_envelope_filters_invalid_emails_in_cc()
    {
        $ccEmails = ['usuario@test.com', 'invalid-email', 'admin@test.com'];

        $mail = new SupportRequestNotification(
            'Juan Pérez',
            'usuario@test.com',
            'Consulta técnica',
            'Descripción de prueba',
            $ccEmails
        );

        $envelope = $mail->envelope();

        // Solo los emails válidos deben estar en CC
        $this->assertNotNull($envelope->cc);
        $this->assertCount(2, $envelope->cc);
        $this->assertEquals('usuario@test.com', $envelope->cc[0]->address);
        $this->assertEquals('admin@test.com', $envelope->cc[1]->address);
    }

    /**
     * Test: Verifica que el content usa la vista correcta
     */
    public function test_content_uses_correct_view()
    {
        $mail = new SupportRequestNotification(
            'Juan Pérez',
            'usuario@test.com',
            'Consulta técnica',
            'Descripción de prueba'
        );

        $content = $mail->content();

        $this->assertEquals('emails.support-request-notification', $content->view);
    }

    /**
     * Test: Verifica que las propiedades públicas se asignan correctamente
     */
    public function test_public_properties_are_assigned_correctly()
    {
        $mail = new SupportRequestNotification(
            'Juan Pérez',
            'usuario@test.com',
            'Consulta técnica',
            'Descripción de prueba',
            ['admin@test.com']
        );

        $this->assertEquals('Juan Pérez', $mail->userName);
        $this->assertEquals('usuario@test.com', $mail->userEmail);
        $this->assertEquals('Consulta técnica', $mail->subject);
        $this->assertEquals('Descripción de prueba', $mail->description);
        $this->assertIsArray($mail->ccEmails);
        $this->assertContains('admin@test.com', $mail->ccEmails);
        $this->assertIsString($mail->createdAt);
    }

    /**
     * Test: Verifica que createdAt tiene formato correcto
     */
    public function test_created_at_has_correct_format()
    {
        $mail = new SupportRequestNotification(
            'Juan Pérez',
            'usuario@test.com',
            'Consulta técnica',
            'Descripción de prueba'
        );

        // Verificar formato d/m/Y H:i
        $this->assertMatchesRegularExpression('/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/', $mail->createdAt);
    }

    /**
     * Test: Verifica que ccEmails se convierte a array cuando se pasa un string
     */
    public function test_cc_emails_converts_string_to_array()
    {
        $mail = new SupportRequestNotification(
            'Juan Pérez',
            'usuario@test.com',
            'Consulta técnica',
            'Descripción de prueba',
            'admin@test.com' // String en lugar de array
        );

        $this->assertIsArray($mail->ccEmails);
        $this->assertContains('admin@test.com', $mail->ccEmails);
    }

    /**
     * Test: Verifica que ccEmails es array vacío cuando se pasa null o string vacío
     */
    public function test_cc_emails_is_empty_array_when_null_or_empty()
    {
        $mail1 = new SupportRequestNotification(
            'Juan Pérez',
            'usuario@test.com',
            'Consulta técnica',
            'Descripción de prueba',
            null
        );

        $mail2 = new SupportRequestNotification(
            'Juan Pérez',
            'usuario@test.com',
            'Consulta técnica',
            'Descripción de prueba',
            ''
        );

        $this->assertIsArray($mail1->ccEmails);
        $this->assertEmpty($mail1->ccEmails);

        $this->assertIsArray($mail2->ccEmails);
        $this->assertEmpty($mail2->ccEmails);
    }

    /**
     * Test: Verifica que no hay attachments
     */
    public function test_has_no_attachments()
    {
        $mail = new SupportRequestNotification(
            'Juan Pérez',
            'usuario@test.com',
            'Consulta técnica',
            'Descripción de prueba'
        );

        $attachments = $mail->attachments();

        $this->assertIsArray($attachments);
        $this->assertEmpty($attachments);
    }
}

