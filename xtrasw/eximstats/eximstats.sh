#!/bin/sh
# Eximstats Report Generation Script
#
# Copyright, 2009, by Gordon Dickens and MailHub4u.com, LLC.
#
# Generate report from yesterdays log file.
#
# Copy eximstats-1.58a.src to /usr/local/bin or any other directory and put path to file here:
eximstats_cmd=/usr/local/bin/eximstats-1.58a.src
#
logfile=/var/log/exim/main.log.1
destdir=/home/exim4u/public_html/eximstats
htmlfile=/home/exim4u/public_html/eximstats/eximstats.html
txtfile=/home/exim4u/public_html/eximstats/eximstats.txt
pattern1="Ratelimited /ratelimited/"
pattern2="'HELO Errors' '/HELO error/'"
pattern3="'Local Addr Content' '/Local content not permitted/'"
pattern4="'Not Local or Relay' '/relay not permitted/'"
pattern5="'Sent Domain Failed' '/Cannot verify sender domain/'"
pattern6="'Dictionary Attack' '/Dictionary/'"
pattern7="'Rcpt Callout' '/No such person at this address/'"
pattern8="'Rcpt Callout Cache' '/Previous \(cached\) callout/'"
pattern9="DNSBL '/DNSBL listed/'"
pattern10="'SPF - Sender' '/Sender address not permitted - SPF./'"
pattern11="'File Extension' '/File extension rejected/'"
pattern12="'URL Blacklists' '/Blacklisted URL in message/'"
pattern13="'SPF - From' '/From address not permitted - SPF./'"
pattern14="'MIME Errors' '/contains a MIME error/'"
pattern15="Malware '/message contains malware/'"
pattern16="Spamassassin '/rejected as spam/'"
pattern17="'SA Add Ons' '/rejected as SPAM/'"
pattern18="Greylisted '/rejected after DATA: Greylisted/'"
pattern19="Blackhole '/ditch_spam/'"
pattern20="'Sender Ratelimit' '/ratelimit \(/'"
$eximstats_cmd -pattern $pattern1 \
-pattern $pattern2 \
-pattern $pattern3 \
-pattern $pattern4 \
-pattern $pattern5 \
-pattern $pattern6 \
-pattern $pattern7 \
-pattern $pattern8 \
-pattern $pattern9 \
-pattern $pattern10 \
-pattern $pattern11 \
-pattern $pattern12 \
-pattern $pattern13 \
-pattern $pattern14 \
-pattern $pattern15 \
-pattern $pattern16 \
-pattern $pattern17 \
-pattern $pattern18 \
-pattern $pattern19 \
-pattern $pattern20 \
-nr -ne -charts -chartdir $destdir -html=$htmlfile -txt=$txtfile $logfile
#
# Optionally, email report links to recipients.
# /usr/bin/mutt -s "eximstats report" -a /home/exim4u/public_html/eximstats/eximstats.txt username@domain.tld < /dev/null;
#
