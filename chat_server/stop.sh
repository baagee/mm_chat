#!/bin/bash
ps -ef|grep run.php|grep -v grep|awk '{print "kill -9 " $2}'|sh
