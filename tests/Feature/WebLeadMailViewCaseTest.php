<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * En Windows resources/views/Mail y resources/views/mail son la misma carpeta; en el servidor Linux NO. Git versiona
 * la carpeta en minuscula (resources/views/mail/*), asi que el Mailable debe referenciarla en minuscula o el aviso
 * de correo fallaria en produccion con "View [Mail.web-lead-received] not found".
 */
class WebLeadMailViewCaseTest extends TestCase
{
    public function test_the_mailable_references_the_lowercase_mail_view_directory(): void
    {
        $source = file_get_contents(app_path('Mail/WebLeadReceivedMail.php'));

        $this->assertStringContainsString("'mail.web-lead-received'", $source);
        $this->assertStringNotContainsString("'Mail.web-lead-received'", $source);
    }

    public function test_the_mail_view_file_exists_in_the_lowercase_directory(): void
    {
        $this->assertTrue(view()->exists('mail.web-lead-received'));
        $this->assertFileExists(resource_path('views/mail/web-lead-received.blade.php'));
    }
}
