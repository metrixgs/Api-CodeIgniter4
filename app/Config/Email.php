<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig {

    public string $fromEmail = 'soporte@gestionplus.com.co';
    public string $fromName = 'GestionPlus';
    public string $recipients = '';

    /**
     * Direcciones de correo para copia oculta (BCC)
     */
    public array $BCCAddresses = [
        'rodaheva@gmail.com'
    ];

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = 'smtp';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    /**
     * SMTP Server Address
     */
    public string $SMTPHost = 'smtp.hostinger.com';

    /**
     * SMTP Username
     */
    public string $SMTPUser = 'soporte@gestionplus.com.co';

    /**
     * SMTP Password
     */
    public string $SMTPPass = 'Renter19892308open@';

    /**
     * SMTP Port
     */
    public int $SMTPPort = 587;

    /**
     * SMTP Timeout (in seconds)
     */
    public int $SMTPTimeout = 5;

    /**
     * Enable persistent SMTP connections
     */
    public bool $SMTPKeepAlive = false;

    /**
     * SMTP Encryption.
     *
     * @var string '', 'tls' or 'ssl'.
     */
    public string $SMTPCrypto = 'tls';

    /**
     * Enable word-wrap
     */
    public bool $wordWrap = true;

    /**
     * Character count to wrap at
     */
    public int $wrapChars = 76;

    /**
     * Type of mail, either 'text' or 'html'
     */
    public string $mailType = 'html';

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     */
    public string $charset = 'UTF-8';

    /**
     * Whether to validate the email address
     */
    public bool $validate = false;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = 3;

    /**
     * Newline character.
     */
    public string $CRLF = "\r\n";

    /**
     * Newline character.
     */
    public string $newline = "\r\n";

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode = false;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize = 200;

    /**
     * Enable notify message from server
     */
    public bool $DSN = false;

    public function __construct() {
        $config = session('configuracion');

        if ($config) {
            $this->fromEmail = $config['from_email'] ?? $this->fromEmail;
            $this->fromName = $config['from_name'] ?? $this->fromName;
            $this->SMTPHost = $config['smtp_host'] ?? $this->SMTPHost;
            $this->SMTPUser = $config['smtp_user'] ?? $this->SMTPUser;
            $this->SMTPPass = $config['smtp_pass'] ?? $this->SMTPPass;
            $this->SMTPPort = (int) ($config['smtp_port'] ?? $this->SMTPPort);
            $this->SMTPCrypto = $config['smtp_crypto'] ?? $this->SMTPCrypto;
            $this->mailType = $config['mail_type'] ?? $this->mailType;
            $this->charset = $config['charset'] ?? $this->charset;
            $this->priority = (int) ($config['priority'] ?? $this->priority);

            if (!empty($config['bcc_addresses'])) {
                $this->BCCAddresses = explode(',', $config['bcc_addresses']);
            }
        }
    }
}
