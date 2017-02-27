<?php
/**
 * INWX DDNS Manager
 *
 * @author Peter Siemer <info@affenrakete.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @link https://affenrakete.de
 *
 */
header('Content-type: text/plain; charset=utf-8'); // Set UFT-8 Header

define("DEBUG", false);
define("OUTPUT", true);

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once('./libraries/domrobot.php');

function precheck($str, $checkEmpty = false)
{
    $str = htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');

    if($checkEmpty && empty($str))
        exit('something went wrong 7');

    return $str;
}

class DDNS
{
    protected $iniFilePath = "./conf/";
    protected $iniFileInwx = "inwx.ini";
    protected $iniFileDomain = "";

    protected $inwx = [];            // {apiurl, username, password}
    protected $domain = [];          // [inwx] => {domain, subdomain}, [ddns] => {apikey}
    protected $IP4 = [];          // {oldip, newip, id}
    protected $IP6 = [];          // {oldip, newip, id}
    
    protected $domrobot;

    public function __construct($apidomain = null, $apikey = null)
    {
        $this->setIniFileDomain($apidomain);
        $this->readIni();
        $this->checkAccess($apikey);
    }

    protected function setIniFileDomain($apidomain = null)
    {
        preg_match("/(?!.{253})((?!-)[A-Za-z0-9-]{1,63}(?<!-)\.){1,126}+[A-Za-z]{2,6}/", $apidomain, $domainReturn);
		$domainReturn[0] = str_replace('.', '-', $domainReturn[0]);
		
        $this->iniFileDomain = $domainReturn[0] . '.ini';

        return;        
    }

    protected function readIni()
    {
        // check if ini files exists
        if(!file_exists($this->iniFilePath . $this->iniFileDomain))
            exit('something went wrong 8');
        if(!file_exists($this->iniFilePath . $this->iniFileInwx))
            exit('something went wrong 1');

        // read domain.ini
        $ini = parse_ini_file($this->iniFilePath . $this->iniFileDomain, TRUE);
        
            $this->domain['inwx']['domain'] = precheck($ini['inwx']['domain'], true);
            $this->domain['inwx']['subdomain'] = precheck($ini['inwx']['subdomain'], true);
            $this->domain['ddns']['apikey'] = precheck($ini['ddns']['apikey'], true);
            $this->domain['log']['filepath'] = precheck($ini['log']['filepath'], true);

        //read inwx.ini
        $ini = parse_ini_file($this->iniFilePath . $this->iniFileInwx, TRUE);

            $this->inwx['apiurl'] =  precheck($ini['apiurl'], true);
            $this->inwx['username'] =  precheck($ini['username'], true);
            $this->inwx['password'] =  precheck($ini['password'], true);

        return;
    }

    protected function checkAccess($apikey = null)
    {
        if($apikey == null || $this->domain['ddns']['apikey'] !== $apikey)
            exit('something went wrong 3');

        return;
    }
    
    protected function inwxLogin()
    {
        // INWX Setup class
        $this->domrobot = new INWX\domrobot($this->inwx['apiurl']);
        $this->domrobot->setDebug(false);
        $this->domrobot->setLanguage('en');
        		
        // INWX Login
        $result = $this->domrobot->login($this->inwx['username'], $this->inwx['password']);

        if(DEBUG)
            print_r($result);        
        
        // check result
        if ($result['code'] != 1000)
        {
            if(OUTPUT)
                exit('badauth');
            
            exit('something went wrong 4');
        }
        
        return;
    }
    
    protected function inwxLogout()
    {
        $result = $this->domrobot->logout();

        if(DEBUG)
            print_r($result);
        
        // check result
        if ($result['code'] != 1500)
        {
            exit('something went wrong 5');
        }
        
        return;
    }
    
    protected function inwxGetNameserverInfo()
    {
        $object = "nameserver";
        $methode = "info";
            
        $params = array();
        $params['domain'] = $this->domain['inwx']['domain'];
        $params['name'] = $this->domain['inwx']['subdomain'];
            
        $result = $this->domrobot->call($object, $methode, $params);
            
        if(DEBUG)
            print_r($result);
        
        // check result        
        if ($result['code'] != 1000)
        {                        
            exit('something went wrong 6');
        }
        
		foreach($result["resData"]["record"] as $value)
		{
			if($value['type'] == "A")
			{
				$this->IP4['id'] = $value['id'];
				$this->IP4['oldip'] = $value['content'];
			}
			if($value['type'] == "AAAA")
			{
				$this->IP6['id'] = $value['id'];
				$this->IP6['oldip'] = $value['content'];
			}
		}
		
        return;
    }
	
	protected function inwxSetNameserverInfo($type = null)
    {
        $object = "nameserver";
        $methode = "updateRecord";
            
        $params = array();
		
		if($type == 'ipv4')
		{
			$params['id'] = $this->IP4['id'];
			$params['content'] = $this->IP4['newip'];
		}
		elseif($type == 'ipv6')
		{
			$params['id'] = $this->IP6['id'];
			$params['content'] = $this->IP6['newip'];
		}
		else
			exit('something went wrong 9');	
            
        $result = $this->domrobot->call($object, $methode, $params);
            
        if(DEBUG)
            print_r($result);
        
        // check result        
        if ($result['code'] != 1000)
        {                        
            exit('something went wrong 10');
        }
		
	if(OUTPUT)
	    echo('good');
        		
        return;
    }
    
    public function updateIP($ipv4 = null, $ipv6 = null)
    {
        $this->inwxLogin();
        
        $this->inwxGetNameserverInfo();
		
		if(filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
		{
			$this->IP4['newip'] = $ipv4;
			$this->inwxSetNameserverInfo('ipv4');
		}
            
		if(filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
		{
			$this->IP6['newip'] = $ipv6;
			$this->inwxSetNameserverInfo('ipv6');
		}
		
        $this->inwxLogout();
        
        return;
    }
}

$input['domain'] = (isset($_GET['domain'])) ? precheck($_GET['domain'], true) : null;
$input['password'] = (isset($_GET['password'])) ? precheck($_GET['password'], true) : null;
$input['ipv4'] = (isset($_GET['ipv4'])) ? precheck($_GET['ipv4'], false) : null;
$input['ipv6'] = (isset($_GET['ipv6'])) ? precheck($_GET['ipv6'], false) : null;

$ddns = new DDNS($input['domain'], $input['password']);
$ddns->updateIP($input['ipv4'], $input['ipv6']);

?>
