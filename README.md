# ddns

DDNS inwx Updater
=================
A dynamic update for inwx subdomain.

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
<pre>
; API URL
apiurl = https://api.domrobot.com/xmlrpc/
; inwx username
username = affenrakete_inwx_login
; inwx password
password = inwx_login_pwd_1!!
</pre>

"/conf/test-affenrakete-de.ini"
<pre>
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
</pre>

https://ddns.affenrakete.de/update.php?domain=test.affenrakete.de&password=YOURSECRETPWD!11&ipv4=127.0.0.1&ipv6=::1
