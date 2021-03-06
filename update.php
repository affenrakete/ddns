<?php

namespace Affenrakete;

/**
 * INWX DDNS Manager
 *
 * @author Peter Siemer <info@affenrakete.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU Public License
 * @link https://affenrakete.de
 *
 */
header('Content-type: text/plain; charset=utf-8'); // Set UFT-8 Header

require_once('./includes/ddns.php');
require_once('./vendor/autoload.php');

define("DEBUG", false);
define("OUTPUT", true);

if (DEBUG) {
    error_reporting(-1);
    ini_set("display_errors", 1);
}

$input['domain'] = filter_has_var(INPUT_GET, 'domain') ? filter_input(INPUT_GET, 'domain', FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/(?!.{253})((?!-)[A-Za-z0-9-]{1,63}(?<!-)\.){1,126}+[A-Za-z]{2,6}/']]) : null;
$input['password'] = filter_has_var(INPUT_GET, 'password') ? filter_input(INPUT_GET, 'password') : null;
$input['ipv4'] = filter_has_var(INPUT_GET, 'ipv4') ? filter_input(INPUT_GET, 'ipv4', FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) : null;
$input['ipv6'] = filter_has_var(INPUT_GET, 'ipv6') ? filter_input(INPUT_GET, 'ipv6', FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) : null;

$ddns = new DDNS($input['domain'], $input['password']);

if ($ddns->inwxLogin()) {
    if ($ddns->inwxGetNameserverInfo()) {
        if ($input['ipv4'] != null) {
            $ddns->inwxSetNameserverInfo($input['ipv4'], 'A');
        }
        if ($input['ipv6'] != null) {
            $ddns->inwxSetNameserverInfo($input['ipv6'], 'AAAA');
        }
    }
}

if (OUTPUT) {
    $ddns->printStatus();
}

$ddns->inwxLogout();
