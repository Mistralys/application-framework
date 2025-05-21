<?php

declare(strict_types=1);

use Application\SystemMails\MailContents\BaseMailContent;
use Application\SystemMails\MailContents\MailButton;
use Application\SystemMails\MailContents\MailParagraph;
use Application\SystemMails\MailContents\MailPreformatted;use Application\SystemMails\MailContents\MailSeparator;use Application\SystemMails\SystemMail;
use AppUtils\OutputBuffering;
use Mistralys\AppFramework\AppFramework;

/**
 * @link https://github.com/leemunroe/responsive-html-email-template/blob/master/email-inlined.html
 */
class template_default_system_mail_html_email extends UI_Page_Template_Custom
{
    public const TEMPLATE_ID = 'system-mail/html-email';
    public const VAR_EMAIL_INSTANCE = 'mail';

private SystemMail $mail;
    private AppFramework $framework;

    protected function preRender(): void
    {
        $this->mail = $this->getObjectVar(self::VAR_EMAIL_INSTANCE, SystemMail::class);
        $this->framework = AppFramework::getInstance();
    }

    protected function generateOutput(): void
    {
?><!doctype html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?php echo $this->mail->getSubject() ?></title>
    <style media="all" type="text/css">
        @media all {
            .btn-primary table td:hover {
                background-color: #ec0867 !important;
            }

            .btn-primary a:hover {
                background-color: #ec0867 !important;
                border-color: #ec0867 !important;
            }
        }
        @media only screen and (max-width: 640px) {
            .main p,
            .main td,
            .main span {
                font-size: 16px !important;
            }

            .wrapper {
                padding: 8px !important;
            }

            .content {
                padding: 0 !important;
            }

            .container {
                padding: 0 !important;
                padding-top: 8px !important;
                width: 100% !important;
            }

            .main {
                border-left-width: 0 !important;
                border-radius: 0 !important;
                border-right-width: 0 !important;
            }

            .btn table {
                max-width: 100% !important;
                width: 100% !important;
            }

            .btn a {
                font-size: 16px !important;
                max-width: 100% !important;
                width: 100% !important;
            }
        }
    </style>
</head>
<body style="font-family: Helvetica, sans-serif; -webkit-font-smoothing: antialiased; font-size: 16px; line-height: 1.3; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; background-color: #f4f5f6; margin: 0; padding: 0;">
<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background-color: #f4f5f6; width: 100%;" width="100%" bgcolor="#f4f5f6">
    <tr>
        <td style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top;" valign="top">&nbsp;</td>
        <td class="container" style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; max-width: 600px; padding: 0; padding-top: 24px; width: 600px; margin: 0 auto;" width="600" valign="top">
            <div class="content" style="box-sizing: border-box; display: block; margin: 0 auto; max-width: 600px; padding: 0;">

                <!-- START CENTERED WHITE CONTAINER -->
                <?php
                $preheader = $this->mail->getPreheaderText();
                if(!empty($preheader)) {
                    ?><span class="preheader" style="color: transparent; display: none; height: 0; max-height: 0; max-width: 0; opacity: 0; overflow: hidden; mso-hide: all; visibility: hidden; width: 0;"><?php echo $preheader ?></span><?php
                }
                ?>
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; background: #ffffff; border: 1px solid #eaebed; border-radius: 16px; width: 100%;" width="100%">

                    <!-- START MAIN CONTENT AREA -->
                    <tr>
                        <td class="wrapper" style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; box-sizing: border-box; padding: 24px;" valign="top">
                            <?php
                            foreach($this->mail->getContents() as $content) {
                                echo $this->renderContent($content);
                            }
                            ?>
                        </td>
                    </tr>

                    <!-- END MAIN CONTENT AREA -->
                </table>

                <!-- START FOOTER -->
                <div class="footer" style="clear: both; padding-top: 24px; text-align: center; width: 100%;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;" width="100%">
                        <tr>
                            <td class="content-block" style="font-family: Helvetica, sans-serif; vertical-align: top; color: #9a9ea6; font-size: 16px; text-align: center;" valign="top" align="center">
                                <br> <?php pt('This is a system email sent by %1$s v%2$s.', $this->driver->getAppName(), $this->driver->getExtendedVersion()) ?>
                                <br> <?php pt('Powered by %1$s v%2$s.', $this->framework->getNameLinked(), $this->framework->getVersion()) ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- END FOOTER -->

                <!-- END CENTERED WHITE CONTAINER -->
            </div>
        </td>
        <td style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top;" valign="top">&nbsp;</td>
    </tr>
</table>
</body>
</html><?php
    }

    private function renderContent(BaseMailContent $content) : string
    {
        if($content instanceof MailParagraph) {
            return $this->renderParagraph($content);
        }

        if($content instanceof MailButton) {
            return $this->renderButton($content);
        }

        if($content instanceof MailPreformatted) {
            return $this->renderPreformatted($content);
        }

        if($content instanceof MailSeparator) {
            return $this->renderSeparator($content);
        }

        return '';
    }

    private function renderParagraph(MailParagraph $content) : string
    {
        OutputBuffering::start();
        ?>
        <p style="font-family: Helvetica, sans-serif; font-size: 16px; font-weight: normal; margin: 0; margin-bottom: 16px;"><?php echo $content->getContent(); ?></p>
        <?php
        return OutputBuffering::get();
    }

    private function renderButton(MailButton $content) : string
    {
        OutputBuffering::start();
        ?>
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" class="btn btn-primary" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; box-sizing: border-box; width: 100%; min-width: 100%;" width="100%">
            <tbody>
            <tr>
                <td align="left" style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; padding-bottom: 16px;" valign="top">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: auto;">
                        <tbody>
                        <tr>
                            <td style="font-family: Helvetica, sans-serif; font-size: 16px; vertical-align: top; border-radius: 4px; text-align: center; background-color: #0867ec;" valign="top" align="center" bgcolor="#0867ec"> <a href="<?php echo $content->getURL() ?>" target="_blank" style="border: solid 2px #0867ec; border-radius: 4px; box-sizing: border-box; cursor: pointer; display: inline-block; font-size: 16px; font-weight: bold; margin: 0; padding: 12px 24px; text-decoration: none; text-transform: capitalize; background-color: #0867ec; border-color: #0867ec; color: #ffffff;"><?php echo $content->getLabel() ?></a> </td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
        return OutputBuffering::get();
    }

    private function renderPreformatted(MailPreformatted $content) : string
    {
        OutputBuffering::start();
        ?><pre><?php echo $content->getContent() ?></pre><?php
        return OutputBuffering::get();
    }

    private function renderSeparator(MailSeparator $content) : string
    {
        return '<hr/>';
    }
}
