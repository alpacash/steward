<?php

namespace App\Service\Http;

use App\Shell;
use App\StewardConfig;
use League\CLImate\CLImate;

class Secure
{
    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $sslHomePath;

    /**
     * @var \League\CLImate\CLImate
     */
    protected $cli;

    /**
     * Secure constructor.
     *
     * @param string $domain
     */
    public function __construct(
        string $domain
    ) {
        $this->domain = $domain;
        $this->sslHomePath = StewardConfig::home('.ssl');
        $this->cli = new CLImate();
    }

    /**
     * @param string $domain
     *
     * @return \App\Service\Http\Secure
     */
    public static function domain(string $domain)
    {
        return new static($domain);
    }

    /**
     * @return string
     */
    public function keyPath()
    {
        return $this->sslHomePath . '/' . $this->domain . '.key';
    }

    /**
     * @return string
     */
    public function crtPath()
    {
        return $this->sslHomePath . '/' . $this->domain . '.crt';
    }

    /**
     * @return void
     */
    public function secure()
    {
        $keyPath = $this->keyPath();
        $crtPath = $this->crtPath();

        $csrPath = $this->sslHomePath . '/' . $this->domain . '.csr';
        $confPath = $this->sslHomePath . '/' . $this->domain . '.conf';

        if (\file_exists($crtPath)) {
            $this->cli->red("{$this->domain} is already secure."
                . " Unsecure it first in order to issue a new certificate.");

            return;
        }

        $this->buildCertificateConf($confPath)
            ->createPrivateKey($keyPath)
            ->createSigningRequest($keyPath, $csrPath, $confPath);

        $this->cli->yellow("Creating self-signed certificate...");

        Shell::cmd(sprintf(
            'openssl x509 -req -days 365 -in %s -signkey %s -out %s -extensions v3_req -extfile %s',
            $csrPath, $keyPath, $crtPath, $confPath
        ));

        $this->cli->comment("The certificate files were stored in {$this->sslHomePath}.");
        $this->trustCertificate($crtPath);

        $this->cli->green("The site {$this->domain} was secured successfully.");
    }

    /**
     * @param string $confPath
     *
     * @return self
     */
    protected function buildCertificateConf(string $confPath)
    {
        $this->cli->yellow("Building certificate configuration file...");

        $config = str_replace(
            '%SSL_DOMAIN%',
            $this->domain,
            \file_get_contents(base_path('stubs/openssl.conf'))
        );

        \file_put_contents($confPath, $config);

        return $this;
    }

    /**
     * @param string $keyPath
     *
     * @return self
     */
    protected function createPrivateKey(string $keyPath)
    {
        $this->cli->yellow("Creating private key file...");
        Shell::cmd(sprintf('openssl genrsa -out %s 2048', $keyPath));

        return $this;
    }

    /**
     * @param string $keyPath
     * @param string $csrPath
     * @param string $confPath
     *
     * @return self
     */
    protected function createSigningRequest(string $keyPath, string $csrPath, string $confPath)
    {
        $this->cli->yellow("Creating certificate signing request...");

        Shell::cmd(sprintf(
            'openssl req -new -key %s -out %s -subj'
                . ' "/C=/ST=/O=/localityName=/commonName=*.%s/organizationalUnitName=/emailAddress=/"'
                . ' -config %s -passin pass:',
            $keyPath, $csrPath, $this->domain, $confPath
        ));

        return $this;
    }

    /**
     * @param string $crtPath
     */
    protected function trustCertificate(string $crtPath)
    {
        $this->cli->yellow("[WARNING] Sudo password required to trust self-signed certificate...");

        Shell::cmd(sprintf(
            'sudo security add-trusted-cert -d -r trustRoot -k /Library/Keychains/System.keychain %s',
            $crtPath
        ));
    }

    /**
     * @return self
     */
    public function unsecure()
    {
        $extensions = ['conf', 'key', 'csr', 'crt'];

        foreach ($extensions as $extension) {
            @\unlink("{$this->sslHomePath}/{$this->domain}.{$extension}");

            $this->cli->comment("Deleted file {$this->domain}.{$extension}");
        }

        $this->cli->yellow("[WARNING] Sudo password required to untrust certiticate...");

        Shell::cmd(sprintf('sudo security delete-certificate -c "%s" -t', $this->domain));

        return $this;
    }

    /**
     * @return bool
     */
    public function isSecure()
    {
        return \file_exists("{$this->sslHomePath}/{$this->domain}.crt");
    }
}
