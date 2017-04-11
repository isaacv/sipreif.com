import serial 		# import Serial Library
import sqlite3		# importar libreria SQLite
import numpy as np	# Import numpy
import matplotlib
import matplotlib.pyplot as plt #import matplotlib library
import matplotlib.dates as md
from drawnow import *
import datetime as dt
import time
import urllib

import smtplib
from email.MIMEMultipart import MIMEMultipart
from email.MIMEText import MIMEText


# CREACION DE LA DB Y CONEXIONES A LA MISMA

conn = sqlite3.connect('/var/www/html/db/project1.db')
conn.text_factory = str
c = conn.cursor()
data = c.fetchall()

# Declarando las variables de los sensores
ID= []
tempF= []
humidity=[]
latitude= []
longitude= []

tempF1= []
humidity1=[]

tempF2=[]
humidity2=[]

tempF3=[]
humidity3=[]

fecha = []

panic = []

subject = ""

# OBJETOS PARA PLOT Y CONEXION SERIAL

arduinoData = serial.Serial('/dev/ttyACM0', 9600) #Creating our serial object named arduinoData
# sensor2Data = serial.Serial('/dev/ttyACM1', 9600) #Creating our serial object named arduinoData
plt.ion() #Tell matplotlib you want interactive mode to plot live data
cnt=0
cnt2=0
cID = 0


# FUNCION PARA CREAR LA TABLA DE VALORES
def create_table():
	c.execute('CREATE TABLE IF NOT EXISTS varBoscosas (unix REAL, ID INTEGER, Temperature REAL, Humidity REAL, Latitude REAL, Longitude INTEGER)')

# FUNCION PARA LEER LOS VALORES RECIBIDOS POR EL GATEWAY Y ALMACENARLOS EN LA DB
def data_entry():
	unix = time.time()
	date = str(dt.datetime.fromtimestamp(unix).strftime('%d-%m-%y %H:%M:%S'))
	c.execute("INSERT INTO varBoscosas (unix, ID, Temperature, Humidity, Latitude, Longitude, panic) VALUES (?, ?, ?, ?, ?, ?, ?)",
				(unix, ident, temp, H, lat, longit, panic))
	conn.commit()

def data_entry2():
    unix = time.time()
    date = str(dt.datetime.fromtimestamp(unix).strftime('%d-%m-%y %H:%M:%S'))
    c.execute("INSERT INTO varBoscosas (unix, ID, Temperature, Humidity, Latitude, Longitude,panic) VALUES (?, ?, ?, ?, ?, ?)",
                (unix, ident2, temp2, h2, lat2, lon2))
    conn.commit()

def makeFig(): #Create a function that makes our desired plot
	#ft=matplotlib.dates.date2num(fecha)
	plt.ylim(50,120)                                #Set y min and max values
	plt.title('Temperatura (F) y Humedad (%)')      #Plot the title
	plt.grid(True)                                  #Turn the grid on
	plt.ylabel('Temp F')                            #Set ylabels
	plt.plot(tempF1, 'ro-', label='Grados F')        #plot the temperature
	plt.legend(loc='upper left')                    #plot the legen
	plt.plot(tempF2, 'yo-')
	plt.plot(tempF3, 'go-')

	plt2=plt.twinx()                                #Create a second y axis
	plt.ylim(30,90)                           		#Set limits of second y axis- adjust to readings you are getting
	plt2.plot(humidity1, 'b^-', label='Humedad (%)') #plot humidity data
	plt2.set_ylabel('Humedad (%)')                  #label second y axis
	plt2.ticklabel_format(useOffset=False)          #Force matplotlib to NOT autoscale y axis
	plt2.legend(loc='upper right')                  #plot the legend
	plt2.plot(humidity2, 'b^-')
	plt2.plot(humidity3, 'b^-')

# FUNCION PARA ENVIO DE CORREOS

def email(subject):
	fromaddr = "isaac.vilchez91@gmail.com"
	toaddr = "isaac.vilchez91@gmail.com"
	msg = MIMEMultipart()
	msg['From'] = fromaddr
	msg['To'] = toaddr
	msg['Subject'] = subject

	body = "Uno de los sensores esta generando una alerta"
	msg.attach(MIMEText(body, 'plain'))

	server = smtplib.SMTP('smtp.gmail.com', 587)
	server.starttls()
	server.login(fromaddr, "Another1*")
	text = msg.as_string()
	server.sendmail(fromaddr, toaddr, text)
	server.quit()


# FUNCION PRINCIPAL

while True: # While loop that loops forever
    while (arduinoData.inWaiting()==0):			#Wait here until there is data
        pass #do nothing
    arduinoString = arduinoData.readline() 		#read the line of text from the serial port
    dataArray = 	arduinoString.split(',')    	#Split it into an array called dataArray
    ident = 		( dataArray[0])			#ID de la mota
    temp = 			float( dataArray[1])    #Convert first element to floating number and put in temp
    H =    			float( dataArray[2])    #Convert second element to floating number and put in P
    lat =  			float( dataArray[3])	#Latitud de la mota
    longit= 		float( dataArray[4])		#Longitud de la mota
    panic= 		float( dataArray[5])		#Boton de Panico	
    cID = ident
    print ident
    
    if ident=='111':
    	tempF1.append(temp)
    	humidity1.append(H)
	panic1.append(panic)
    	url = "http://sipreif.com/boscosas-insert?id="+str(ident)+"&temp="+str(temp)+"&hum="+str(H)+"&panic="+str(panic)
        urllib.urlopen(url)
        if (temp > 90):
    		subject = "ALERTA SENSOR 111"
    		email(subject)
    elif ident=='211':
    	tempF2.append(temp)
    	humidity2.append(H)
	panic2.append(panic)
        urllib.urlopen("http://sipreif.com/boscosas-insert?id="+str(ident)+"&temp="+str(temp)+"&hum="+str(H)+"&panic="+str(panic))
    	if (temp > 90):
    		subject="ALERTA SENSOR 211"
    		email(subject)
    elif ident=='311':
    	tempF3.append(temp)
    	humidity3.append(H)
	panic3.append(panic)
        urllib.urlopen("http://sipreif.com/boscosas-insert?id="+str(ident)+"&temp="+str(temp)+"&hum="+str(H)+"&panic="+str(panic))
    	if (temp > 90):
    		subject="ALERTA SENSOR 311" 
    		email(subject)



    tempF.append(temp)                     #Build our tempF array by appending temp readings
    humidity.append(H)                     #Building our humidity array by appending P readings
    #drawnow(makeFig)                       #Call drawnow to update our live graph
    data_entry()

    # if (sensor2Data.inWaiting()!=0):
    #     sensorString = sensor2Data.readline()
    #     data2Array = sensorString.split(',')
    #     ident2 = (data2Array[0])
    #     temp2 = float(data2Array[1])
    #     h2 = float( data2Array[2])
    #     lat2 = float( data2Array[3])
    #     lon2 = float( data2Array[4])
    #     data_entry2()

    #plt.pause(0.8)                     #Pause Briefly. Important to keep drawnow from crashing
    #cnt=cnt+1
    #cnt2=cnt2+1
    #if(cnt>30):
    #	plt.clf()                            #If you have 50 or more points, delete the first one from the array
        #tempF1.pop(0)                       #This allows us to just see the last 50 data points
    #     humidity1.pop(0)
    # if(cnt2>102):
    # 	tempF2.pop(0)
    # 	humidity2.pop(0)
    
c.close()
conn.close()

