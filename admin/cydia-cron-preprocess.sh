#!/bin/bash

admindir="/var/www/htdocs/cydia/admin"
tempdir="/tmp"

SCRIPTNAME=`basename $0`
PIDFILE=$admindir/${SCRIPTNAME}.pid

if [ -f ${PIDFILE} ]; then
   OLDPID=`cat ${PIDFILE}`
   RESULT=`ps -ef | grep ${OLDPID} | grep ${SCRIPTNAME}`

   if [ -n "${RESULT}" ]; then
     exit 255
   fi
fi

echo 1
/usr/bin/wget -q "http://apt.thebigboss.org/repofiles/cydia/dists/stable/main/binary-iphoneos-arm/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/bigboss-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/bigboss-packages.txt > $admindir/bigboss-packages.txt

echo 2
/usr/bin/wget -q "http://apt.modmyi.com/dists/stable/main/binary-iphoneos-arm/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/modmyi-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/modmyi-packages.txt > $admindir/modmyi-packages.txt

echo 3
/usr/bin/wget -q "http://apt.saurik.com/dists/ios/main/binary-iphoneos-arm/Packages.bz2" -O - | /usr/bin/bzcat | /bin/sed s/'Package: libstdc++'/'Package: libstdcpp'/ | /bin/sed s/'Package: libsigc++'/'Package: libsigcpp'/ > $tempdir/tangelo-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/tangelo-packages.txt > $admindir/tangelo-packages.txt

echo 4
/usr/bin/wget -q "http://cydia.zodttd.com/repo/cydia/dists/stable/main/binary-iphoneos-arm/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/zodttd-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/zodttd-packages.txt > $admindir/zodttd-packages.txt

echo 5
/usr/bin/wget -q "http://repo666.ultrasn0w.com/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/ultrasn0w-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/ultrasn0w-packages.txt > $admindir/ultrasn0w-packages.txt

echo 6
/usr/bin/wget -q "http://chronzz.mobi/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/chronzz-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/chronzz-packages.txt > $admindir/chronzz-packages.txt

echo 7
/usr/bin/wget -q "http://apps.iphoneislam.com/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/iphoneislam-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/iphoneislam-packages.txt > $admindir/iphoneislam-packages.txt

echo 8
/usr/bin/wget -q "http://cy.itmfr.net/applizing/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/applizing-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/applizing-packages.txt > $admindir/applizing-packages.txt

echo 9
/usr/bin/wget -q "http://djayb6.com/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/djayb6-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/djayb6-packages.txt > $admindir/djayb6-packages.txt

echo 10
/usr/bin/wget -q "http://www.iappdev.com/i/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/iappdev-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/iappdev-packages.txt > $admindir/iappdev-packages.txt

echo 11
/usr/bin/wget -q "http://i-arabia.co.cc/cydia/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/iarabia-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/iarabia-packages.txt > $admindir/iarabia-packages.txt

echo 12
/usr/bin/wget -q "http://repo.ispazio.net/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/ispazio-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/ispazio-packages.txt > $admindir/ispazio-packages.txt

echo 13
/usr/bin/wget -q "http://hitoriblog.com/apt/Packages" -O - > $tempdir/hitoriblog-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/hitoriblog-packages.txt > $admindir/hitoriblog-packages.txt

echo 14
/usr/bin/wget -q "http://repo.modyouri.com/Packages" -O - > $tempdir/modyouri-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/modyouri-packages.txt > $admindir/modyouri-packages.txt

echo 15
/usr/bin/wget -q "http://phajas.xen.prgmr.com/repo/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/peterhajas-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/peterhajas-packages.txt > $admindir/peterhajas-packages.txt

echo 16
/usr/bin/wget -q "http://dreamboard.us/beta/Packages.gz" -O - | /bin/gunzip > $tempdir/dreamboard-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/dreamboard-packages.txt > $admindir/dreamboard-packages.txt

echo 17
/usr/bin/wget -q "http://cydia.pushfix.info/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/pushfix-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/pushfix-packages.txt > $admindir/pushfix-packages.txt

echo 18
/usr/bin/wget -q "http://iwazowski.com/repo/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/iwazowski-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/iwazowski-packages.txt > $admindir/iwazowski-packages.txt

echo 19
/usr/bin/wget -q "http://cydia.styletap.com/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/styletap-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/styletap-packages.txt > $admindir/styletap-packages.txt

echo 20
/usr/bin/wget -q "http://apt.dba-technologies.com/beta/Packages.bz2" -O - | /usr/bin/bzcat > $tempdir/dba-technologies-packages.txt
/usr/bin/awk '{if ($0 ~ /^[[:space:]]/) {printf "%s", $0} else {printf "\n%s", $0}} END {printf "\n"}' /tmp/dba-technologies-packages.txt > $admindir/dba-technologies-packages.txt

rm -f $tempdir/*-packages.txt

#fresh db import DONT DO IT!
#wget -q --http-user=admin --http-password=cydia-password "http://localhost/cydia/admin/db-init-update.php?init=init" -O - > $admindir/cydia-updates.txt

#do updates
wget -q --http-user=admin --http-password=cydia-password "http://localhost/cydia/admin/db-init-update.php?update=update" -O - > $admindir/cydia-updates.txt

rm $PIDFILE
