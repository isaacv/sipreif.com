# 1. You have a log file in CSV format with >1 billion lines
#    a. Extract every 10,000th line
#    b. Extract just the 2nd and 10th columns
#    c. Add another column which is the sum of the previous two, with a $ prefix
#    d. Find only the lines that have a date of the format "YYYY-MM-DD" followed by a tab or space, and the line ends in '!'
#    e. How will that change if the file is gzipped?
#    f. Sort the data based on the 2nd column (which is numberic) and remove duplicates based on the 'sum' column you created previously.
# 2. Make that script run on midnight every Sunday.
# 3. Find all the files in a folder which changed in the past 24 hours and are named *.log
#    a. Rename them to *.new.log
# 4. Create a file with the machine's IP data named IP.txt, Copy this file 10 times with the prefix 01 - 10
# ---------------------------------------------------------------------------
# grep, sed, awk, vi

#! /bin/bash

# DECLARING VARIABLES
# Defining path to log file
read -p "Indicate the path to the log file as '/path/to/log/file': " LOGPATH

# Defining the functions that we will use
# 1.a) logExtract() is the function that will extract every 10,000th line
# The lines read by sed will be saved to a new file in the /tmp folder
function logExtract() {
	touch "/tmp/logExtract.csv"
	sed -n '0~10p' $LOGPATH > "/tmp/logExtract.csv"
	echo "Write completed to /tmp/logExtract.csv"
}

# 1.b) Extract just the 2nd and 10th columns
# Assuming the delimiter is , 
function columnSplit(){

	touch "/tmp/cutCol.csv"
	cut -d "," -f3,4  "/tmp/logExtract.csv" > "/tmp/cutCol.csv"

	# 1.c) Add another column which is the sum of the previous two, with a $ prefix
	awk 'BEGIN{FS=OFS=","} {print $0, "$"$1+$2}' "/tmp/cutCol.csv"
}

# 1.d) Find only the lines that have a date of the format "YYYY-MM-DD" followed by a tab or space, and the line ends in '!'
function filterLines(){
	touch "/tmp/filteredFile.csv"
	sed -n '/[0-9][0-9][0-9][0-9]\-[0-1][0-9]\-[0-3][0-9] /p' Documents/testLog.csv | sed -n '/!$/p' > "/tmp/filteredFile.csv" 

	echo "Doing the stuff..."
}

# 1.e) How will that change if the file is gzipped?
# A: If the file is gzipped I will need to use zgrep to grep the compressed file and then pipe it to the filterLines() function

# 1.f) Sort the data based on the 2nd column (which is numberic) and remove duplicates based on the 'sum' column you created previously.
function sortClear(){
	# PARAMS:
	# -n: numeric sort
	# -u: unique. Remove dupes
	# -k: Column to sort
	sort -n -u -k2 "/tmp/filteredFile.csv" > "/tmp/sortedFile.csv"
}

# 2. Make that script run on midnight every Sunday.

# Cronjob cheatsheet
# 1. Entry: Minute when the process will be started [0-60]
# 2. Entry: Hour when the process will be started [0-23]
# 3. Entry: Day of the month when the process will be started [1-28/29/30/31]
# 4. Entry: Month of the year when the process will be started [1-12]
# 5. Entry: Weekday when the process will be started [0-6] [0 is Sunday]
#
# all x min = */x

## Run the script every Sunday at midnight
# crontab -e 
# 0 0 * * 0 ./path/to/script.sh

# 3. Find all the files in a folder which changed in the past 24 hours and are named *.log
#    a. Rename them to *.new.log

## Finding all log files modified in the past 24h
# find . -name \*.log -mtime 0 | ls -l

## a. Rename them to *.new.log
# find . -name \*.log -mtime 0 | xargs rename s/.log/.new.log/

# 4. Create a file with the machine's IP data named IP.txt, Copy this file 10 times with the prefix 01 - 10
# ifconfig | awk '{match($0,/[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+/); ip = substr($0,RSTART,RLENGTH); print ip}' > "/tmp/IP.txt"
# for i in {1..10}; do cp "/tmp/IP.txt" "/tmp/$i-IP.txt"; done