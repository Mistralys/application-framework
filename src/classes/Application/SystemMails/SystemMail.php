<?php

declare(strict_types=1);

namespace Application\SystemMails;

use Application\SystemMails\MailContents\BaseMailContent;
use Application\SystemMails\MailContents\MailButton;
use Application\SystemMails\MailContents\MailParagraph;
use Application\SystemMails\MailContents\MailPreformatted;
use Application\SystemMails\MailContents\MailSeparator;
use AppUtils\ConvertHelper;
use AppUtils\Interfaces\StringableInterface;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use template_default_system_mail_html_email;
use UI;
use UI_Exception;

class SystemMail
{
    private PHPMailer $mailer;

    /**
     * @var BaseMailContent[]
     */
    private array $contents = array();
    private string $preheaderText = '';

    public function __construct()
    {
        $this->mailer = new PHPMailer();
    }

    public function getPreheaderText(): string
    {
        return $this->preheaderText;
    }

    /**
     * @param string|number|StringableInterface $text
     * @return $this
     */
    public function setPreheaderText($text) : self
    {
        $this->preheaderText = $text;
        return $this;
    }

    /**
     * @param string|number|StringableInterface $title
     * @return $this
     * @throws UI_Exception
     */
    public function setSubject($title) : self
    {
        $this->mailer->Subject = toString($title);
        return $this;
    }

    /**
     * @return BaseMailContent[]
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * HTML to use within the mailing's body. The frame around
     * this, from the head to the footer, is added automatically.
     *
     * @param string|number|StringableInterface $text
     * @return $this
     * @throws UI_Exception
     */
    public function para($text) : self
    {
        $this->contents[] = new MailParagraph($text);
        return $this;
    }

    /**
     * @return $this
     */
    public function separator() : self
    {
        $this->contents[] = new MailSeparator();
        return $this;
    }

    /**
     * @param string|number|StringableInterface $text
     * @return $this
     * @throws UI_Exception
     */
    public function preformatted($text) : self
    {
        $this->contents[] = new MailPreformatted($text);
        return $this;
    }

    /**
     * @param string|number|StringableInterface $label
     * @param string $url
     * @return self
     * @throws UI_Exception
     */
    public function button($label, string $url) : self
    {
        $this->contents[] = new MailButton($label, $url);
        return $this;
    }

    /**
     * Plain text to use for the raw mail body.
     *
     * NOTE: This must be provided if HTML is not used.
     *
     * @param string|number|StringableInterface $body
     * @return $this
     * @throws UI_Exception
     */
    public function setBodyText($body) : self
    {
        $this->mailer->AltBody = toString($body);
        return $this;
    }

    public function getBodyText() : string
    {
        return $this->mailer->AltBody;
    }

    public function getRecipients() : array
    {
        return ConvertHelper::explodeTrim(',', APP_RECIPIENTS_DEV);
    }

    public function getSubject() : string
    {
        return $this->mailer->Subject;
    }

    /**
     * Creates the fully configured PHPMailer instance used
     * to send the email.
     *
     * @return PHPMailer|null Will be null if there are no recipients to send to.
     * @throws SystemMailException
     * @throws Exception
     */
    public function createMailer() : ?PHPMailer
    {
        $recipients = $this->getRecipients();

        if(empty($recipients)) {
            return null;
        }

        $mail = new PHPMailer();
        $mail->isMail();
        $mail->setFrom(APP_SYSTEM_EMAIL, APP_SYSTEM_NAME);

        foreach($recipients as $recipient) {
            $mail->addAddress($recipient);
        }

        if(!empty($this->html)) {
            $mail->isHTML();
            $mail->Body = $this->renderHTML();
        }

        if(empty($this->getSubject())) {
            throw new SystemMailException(
                'No subject has been set.',
                'The subject is empty.',
                SystemMailException::ERROR_NO_SUBJECT_SET
            );
        }

        if(empty($this->contents) && empty($this->mailer->AltBody)) {
            throw new SystemMailException(
                'No body content has been set.',
                'Both the HTML and plain text are empty.',
                SystemMailException::ERROR_NO_BODY_CONTENT
            );
        }

        return $mail;
    }

    public function send() : self
    {
        $mailer = $this->createMailer();
        if($mailer !== null) {
            $mailer->send();
        }

        return $this;
    }

    /**
     * @return string
     * @throws UI_Exception
     * @see template_default_system_mail_html_email
     */
    public function renderHTML() : string
    {
        return UI::getInstance()
            ->createTemplate(template_default_system_mail_html_email::TEMPLATE_ID)
            ->setVar(template_default_system_mail_html_email::VAR_EMAIL_INSTANCE, $this)
            ->render();
    }
}
