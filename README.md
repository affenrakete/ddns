# ddns
A dynamic ddns updater for inwx. IPv4 and IPv6 support. 

[![Build Status](https://scrutinizer-ci.com/g/affenrakete/ddns/badges/build.png?b=master)](https://scrutinizer-ci.com/g/affenrakete/ddns/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/affenrakete/ddns/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/affenrakete/ddns/?branch=master)
[![Dependency Status](https://gemnasium.com/badges/github.com/affenrakete/ddns.svg)](https://gemnasium.com/github.com/affenrakete/ddns)
[![Packagist](https://img.shields.io/packagist/v/affenrakete/ddns.svg)](https://packagist.org/packages/affenrakete/ddns)
[![License](https://img.shields.io/packagist/l/affenrakete/ddns.svg)](https://packagist.org/packages/affenrakete/ddns.svg)

Quick setup
-----
1. git clone https://github.com/affenrakete/ddns.git
2. composer install
3. copy "/conf/example-inwx.ini" to "/conf/inwx.ini"
4. edit "/conf/inwx.ini" and insert your inwx account data
5. copy "/conf/example-domain-tld.ini" to "/conf/yoursubdomain-yourdomain-yourtld.ini"
6. edit "/conf/yoursubdomain-yourdomain-yourtld.ini" an insert your subdomain specific data
7. run "update.php"

Example
-----
"/conf/inwx.ini"
```INI
; API URL
apiurl = https://api.domrobot.com/xmlrpc/
; inwx username
username = affenrakete_inwx_login
; inwx password
password = inwx_login_pwd_1!!
```

"/conf/test-affenrakete-de.ini"
```INI
 ; subdomain login data
[ddns]
; API Key
apikey = YOURSECRETPWD!11

; domain data
[inwx]
; domain
domain = affenrakete.de
; subdomain
subdomain = test
```

`./update.php?domain=test.affenrakete.de&password=YOURSECRETPWD!11&ipv4=127.0.0.1&ipv6=::1`
