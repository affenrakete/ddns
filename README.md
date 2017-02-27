# ddns
A dynamic ddns updater for inwx subdomain.

About
-----
Create your own DDNS Server that supports IPv4 and IPv6.

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

; log data
[log]
filepath = "./log/test-affenrakete-de.log"
```

`./update.php?domain=test.affenrakete.de&password=YOURSECRETPWD!11&ipv4=127.0.0.1&ipv6=::1`
