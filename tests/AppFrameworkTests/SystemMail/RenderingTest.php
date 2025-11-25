<?php

declare(strict_types=1);

namespace AppFrameworkTests\SystemMail;

use AppFrameworkTestClasses\ApplicationTestCase;
use Application\AppFactory;
use Application\SystemMails\SystemMailer;
use Application\SystemMails\SystemMailException;

final class RenderingTest extends ApplicationTestCase
{
    public function test_subject() : void
    {
        $mail = $this->mailer->createMail();
        $mail->setSubject('Test subject');

        $this->assertStringContainsString('Test subject', $mail->renderHTML());
    }

    public function test_preheader() : void
    {
        $mail = $this->mailer->createMail();
        $mail->setPreheaderText('Preheader text');

        $this->assertStringContainsString('Preheader text', $mail->renderHTML());
    }

    public function test_paragraph() : void
    {
        $mail = $this->mailer->createMail();
        $mail->para('Paragraph one');
        $mail->para('Paragraph two');

        $html = $mail->renderHTML();
        $this->assertStringContainsString('Paragraph one', $html);
        $this->assertStringContainsString('Paragraph two', $html);
    }

    public function test_button() : void
    {
        $mail = $this->mailer->createMail();
        $mail->button('Button text', 'https://example.com');

        $html = $mail->renderHTML();
        $this->assertStringContainsString('Button text', $html);
        $this->assertStringContainsString('https://example.com', $html);
    }

    public function test_recipientsNotEmpty() : void
    {
        $mail = $this->mailer->createMail();

        $this->assertNotEmpty($mail->getRecipients());
    }

    public function test_emptyBodyException() : void
    {
        $mail = $this->mailer->createMail();
        $mail->setSubject('Some subject');

        $this->expectExceptionCode(SystemMailException::ERROR_NO_BODY_CONTENT);

        $mail->createMailer();
    }

    public function test_emptySubjectException() : void
    {
        $mail = $this->mailer->createMail();
        $mail->para('Some content');

        $this->expectExceptionCode(SystemMailException::ERROR_NO_SUBJECT_SET);

        $mail->createMailer();
    }

    public function test_createMailerInstance() : void
    {
        $mail = $this->mailer->createMail();
        $mail->setSubject('Some subject');
        $mail->para('Some content');

        $this->assertNotNull($mail->createMailer());
    }

    private SystemMailer $mailer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailer = AppFactory::createSystemMailer();
    }
}
