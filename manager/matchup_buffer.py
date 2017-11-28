#!/usr/bin/python

import time

import MySQLdb
#import mysql.connector
from server_info import server_info

DEFAULT_BUFFER = 50
MAX_FILL = 60

def log(msg):
    timestamp = time.asctime()
    print "%s: %s" % (timestamp, msg)

def main():
    connection = MySQLdb.connect(autocommit=True,database = server_info["db_name"])
#connection = MySQLdb.connect(host = server_info["db_host"],
    #connection = mysql.connector.connect(host = server_info["db_host"],
    #                             user = server_info["db_username"],
    #                             #passwd = server_info["db_password"],
    #                              auth_plugin='mysql_native_password',
    #                             password = 'g00dby3',
    #                             database = server_info["db_name"])
    cursor = connection.cursor()

    #cursor.callproc("generate_matchup")
    buf_size = DEFAULT_BUFFER
    log("Buffer size set to %d" % (buf_size,))

    fill_size = buf_size
    full = False
    while True:
        cursor.execute("select count(*) from matchup where worker_id is NULL")
        #cursor.execute("select database()")
        #cursor.callproc("generate_matchup")
        cur_buffer = cursor.fetchone()[0]
        #log("Current Buffer: %s" % cur_buffer)
        #sys.exit()
        if cur_buffer >= buf_size:
            log("Buffer full with %d matches in buffer" % (cur_buffer,))
            time.sleep(10)
            if full:
                fill_size = max(buf_size, fill_size * 0.9)
            full = True
        else:
            if not full:
                fill_size = min(MAX_FILL, fill_size * 1.5)
            full = False
            add = int(fill_size) - cur_buffer
            if cur_buffer == 0:
                log("WARNING: Found empty buffer")
            log("Adding %d matches to buffer already having %d" % (
                add, cur_buffer))
            for i in range(add):
                cursor.execute("call generate_matchup")
                cursor.nextset()

if __name__ == "__main__":
    main()

