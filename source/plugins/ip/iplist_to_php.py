# -*- coding: cp936 -*-
from socket import inet_aton
from struct import unpack
import sys
import os

def ip2long(ip_addr):
    return unpack("!L", inet_aton(ip_addr))[0]

def delExSpace(str):
    while str.find("  ")>0:
        str = str.replace("  "," ")
    return str

def sqlWriteHeader(ous):
    slnout = """CREATE TABLE IF NOT EXISTS `ip_location` (
`ip_start` INT( 10 ) NOT NULL ,
`ip_end` INT( 10 ) NOT NULL ,
`ip_location` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY (  `ip_start` ,  `ip_end` )
) ENGINE = MYISAM ;"""
    ous.write(slnout+"\r\n")
    slnout = "INSERT IGNORE INTO `ip_location` (`ip_start`, `ip_end`, `ip_location`) VALUES"
    ous.write(slnout+"\r\n")

def sqlWriteFooter(ous):
    ous.write(";\r\n")

def sqlWriteRecord(ous,lIpA,lIpB,sLoc,sDot):
    slnout = sDot+"("+str(lIpA)+", "+str(lIpB)+", '"+str(sLoc).replace("'","\\'")+"')"
    ous.write(slnout+"\r\n")

if __name__ == '__main__':
    iSucceed=0
    iRead=0
    iSucceedTotal=0
    iLineLen=0
    iOutFileIndex=0
    ins = open( "ip.txt", "r" )
    ous = open( "ip"+str(iOutFileIndex)+".sql", "w" )
    sqlWriteHeader(ous)
    for line in ins:
        line = delExSpace(line.strip())
        if len(line.split(" "))>2:
            if os.path.getsize("ip"+str(iOutFileIndex)+".sql")>2097152:#3670016
                sqlWriteFooter(ous)
                ous.close()
                iOutFileIndex+=1
                iSucceed=0
                ous = open( "ip"+str(iOutFileIndex)+".sql", "w" )
                sqlWriteHeader(ous)
            iRead+=1
            slnprint=""
            try:
                sIpA = line[0:line.find(" ")]
                line = line[line.find(" ")+1:]
                sIpB = line[0:line.find(" ")]
                line = line[line.find(" ")+1:]
                
                lIpA = ip2long(sIpA)
                lIpB = ip2long(sIpB)
                slnprint += str(lIpA)+" "+str(lIpB)+" "

                sqlWriteRecord(ous,lIpA,lIpB,line,',' if iSucceed>0 else '')
                
                iSucceed+=1
                iSucceedTotal+=1
            except: pass
            slnprint = str(iSucceedTotal)+"/"+str(iRead) +" "+ slnprint
            sys.stdout.write('\b'*iLineLen)
            print slnprint,
            iLineLen = len(slnprint)
    sqlWriteFooter(ous)
    ous.close()
    ins.close()
    if raw_input(str1): pass
