<?php

namespace Affenrakete;

use INWX\Domrobot;
use Katzgrau\KLogger\Logger;
use Psr\Log\LogLevel;

/**
 * INWX DDNS Manager
 *
 * @author Peter Siemer <info@affenrakete.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @link https://affenrakete.de
 *
 */
class DDNS {

    protected $logFilePath = "./logs/";
    protected $iniFilePath = "./conf/";
    protected $iniFileInwx = "inwx.ini";
    protected $iniFileDomain = "";
    protected $inwx = [];       // {apiurl, username, password}
    protected $domain = [];     // [inwx] => {domain, subdomain}, [ddns] => {apikey}
    protected $IP4 = [];        // {oldip, newip, id}
    protected $IP6 = [];        // {oldip, newip, id}
    protected $domrobot;
    protected $logger;

    public function __construct($apidomain = null, $apikey = null) {
        self::setIniFile($apidomain);
        self::startLogger();
        self::readIni();
        self::checkAccess($apikey);
    }

    protected function precheck($str = null) {
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }

    protected function setIniFile($apidomain = null) {
        $domainReturn = [];

        preg_match("/(?!.{253})((?!-)[A-Za-z0-9-]{1,63}(?<!-)\.){1,126}+[A-Za-z]{2,6}/", $apidomain, $domainReturn);
        $domainReturn[0] = str_replace('.', '-', $domainReturn[0]);

        $this->iniFileDomain = $domainReturn[0];

        return;
    }

    protected function startLogger() {
        $logLevel = LogLevel::INFO;
        if (DEBUG) {
            $logLevel = LogLevel::DEBUG;
        }

        $this->logger = new Logger($this->logFilePath . $this->iniFileDomain, $logLevel);
    }

    protected function readIni() {
        // check if ini files exists
        if (!file_exists($this->iniFilePath . $this->iniFileDomain . ".ini")) {
            $this->logger->error('File does not exists | iniFileDomain :' . $this->iniFileDomain . '.ini');
            return false;
        }
        if (!file_exists($this->iniFilePath . $this->iniFileInwx)) {
            $this->logger->error('File does not exists | iniFileDomain :' . $this->iniFileInwx);
            return false;
        }

        // read domain.ini
        $ini = parse_ini_file($this->iniFilePath . $this->iniFileDomain . ".ini", TRUE);

        $this->domain['inwx']['domain'] = self::precheck($ini['inwx']['domain']);
        $this->domain['inwx']['subdomain'] = self::precheck($ini['inwx']['subdomain']);
        $this->domain['ddns']['apikey'] = self::precheck($ini['ddns']['apikey']);

        //read inwx.ini
        $ini = parse_ini_file($this->iniFilePath . $this->iniFileInwx, TRUE);

        $this->inwx['apiurl'] = self::precheck($ini['apiurl']);
        $this->inwx['username'] = self::precheck($ini['username']);
        $this->inwx['password'] = self::precheck($ini['password']);

        return;
    }

    protected function checkAccess($apikey = null) {
        if ($apikey == null || $this->domain['ddns']['apikey'] !== $apikey) {
            $this->logger->error('unauthorisized access from IP: ' . $_SERVER['REMOTE_ADDR']);
            return false;
        }

        return true;
    }

    protected function inwxLogin() {
        // INWX Setup class
        $this->domrobot = new Domrobot($this->inwx['apiurl']);
        $this->domrobot->setDebug(false);
        $this->domrobot->setLanguage('en');

        // INWX Login
        $result = $this->domrobot->login($this->inwx['username'], $this->inwx['password']);

        $this->logger->debug('Result', $result);

        // check result
        if ($result['code'] != 1000) {

            if (OUTPUT) {
                echo('badauth');
            }

            $this->logger->error('inwx login not successfull');
            return false;
        }
        $this->logger->debug('inwx login successfull');

        return true;
    }

    public function inwxLogout() {
        $result = $this->domrobot->logout();

        $this->logger->debug('Result', $result);

        // check result
        if ($result['code'] != 1500) {
            $this->logger->error('inwx logout NOT successfull');
            return false;
        }
        $this->logger->debug('inwx logout successfull');

        return true;
    }

    protected function inwxGetNameserverInfo() {
        $object = "nameserver";
        $methode = "info";

        $params = array();
        $params['domain'] = $this->domain['inwx']['domain'];
        $params['name'] = $this->domain['inwx']['subdomain'];

        $result = $this->domrobot->call($object, $methode, $params);


        $this->logger->debug('Result', $result);

        // check result
        if ($result['code'] != 1000) {
            $this->logger->error('get nameserver info NOT successfull');
            return false;
        }

        foreach ($result["resData"]["record"] as $value) {
            if ($value['type'] == "A") {
                $this->IP4['id'] = $value['id'];
                $this->IP4['oldip'] = $value['content'];
            }
            if ($value['type'] == "AAAA") {
                $this->IP6['id'] = $value['id'];
                $this->IP6['oldip'] = $value['content'];
            }
        }

        $this->logger->debug('get nameserver info successfull');

        return true;
    }

    protected function inwxSetNameserverInfo($type = null) {
        $object = "nameserver";
        $methode = "updateRecord";

        $params = array();

        if ($type == 'ipv4') {
            $params['id'] = $this->IP4['id'];
            $params['content'] = $this->IP4['newip'];
            $oldip = $this->IP4['oldip'];
        } elseif ($type == 'ipv6') {
            $params['id'] = $this->IP6['id'];
            $params['content'] = $this->IP6['newip'];
            $oldip = $this->IP6['oldip'];
        } else {
            $this->logger->warning('set nameserver info type: ' . $type);
            return false;
        }

        $result = $this->domrobot->call($object, $methode, $params);

        $this->logger->debug('Result', $result);

        // check result
        if ($result['code'] != 1000) {
            $this->logger->error('set nameserver info NOT successfull');
            return false;
        }

        $this->logger->debug('set nameserver info successfull');
        $this->logger->info('IP Update successfull | old ip: ' . $oldip . ' | new ip: ' . $params['content']);

        return true;
    }

    public function updateIP($ipv4 = null, $ipv6 = null) {

        if (!self::inwxLogin()) {
            return false;
        }

        if (!self::inwxGetNameserverInfo()) {
            return false;
        }

        if (filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->IP4['newip'] = $ipv4;

            if (!self::inwxSetNameserverInfo('ipv4')) {
                return false;
            }
        }

        if (filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->IP6['newip'] = $ipv6;

            if (!self::inwxSetNameserverInfo('ipv6')) {
                return false;
            }
        }

        if (OUTPUT) {
            echo('good');
        }

        $this->logger->debug('updateIp successfull');

        return true;
    }

}
