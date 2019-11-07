import serial 		# import Serial Library
import sqlite3		# importar libreria SQLite
import numpy as np	# Import numpy
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
id= 		[]
tempF=		[]
humidity=	[]
sVar=	[]
latitude=	[]
longitude=	[]
tFlag=	[]
sFlag=	[]

tempF1=		[]
humidity1=	[]
smokeVar1=	[]
tempFlag1=	[]
smokeFlag1= []

tempF2=		[]
humidity2=	[]
smokeVar2=	[]
tempFlag2=	[]
smokeFlag2= []

tempF3=		[]
humidity3=	[]
smokeVar3=	[]
tempFlag3=	[]
smokeFlag3= []

fecha = []

panic = []

subject = ""

# OBJETOS PARA PLOT Y CONEXION SERIAL
arduinoData = serial.Serial('/dev/ttyACM0', 9600) #Creating our serial object named arduinoData
# sensor2Data = serial.Serial('/dev/ttyACM1', 9600) #Creating our serial object named arduinoData

ident = 0		#ID de la mota
temp = 0		#Convertir valor de temp a Float
H =	0			#Convertir valor de humedad a Float
smokeVar = 0	#Valor de sensor de Humo
lat = 0			#Latitude
longit = 0		#Longitude
tempFlag = 0	#Alerta de Temp
smokeFlag = 0	#Alerta de humo

# plt.ion() #Tell matplotlib you want interactive mode to plot live data
cnt=0
cnt2=0
cID = 0


# FUNCION PARA CREAR LA TABLA DE VALORES
def create_table():
	c.execute('CREATE TABLE IF NOT EXISTS varBoscosas (unix REAL, id INTEGER, temperature REAL, humidity REAL, smokeVar REAL, latitude REAL, longitude INTEGER, tempFlag INTEGER, smokeFlag INTEGER)')

# FUNCION PARA LEER LOS VALORES RECIBIDOS POR EL GATEWAY Y ALMACENARLOS EN LA DB
def data_entry():
	unix = time.time()
	date = str(dt.datetime.fromtimestamp(unix).strftime('%d-%m-%y %H:%M:%S'))
	c.execute("INSERT INTO varBoscosas (unix, id, temperature, humidity, smokeVar, latitude, longitude, tempFlag, smokeFlag) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
				(unix, ident, temp, H, smokeVar, lat, longit, tempFlag, smokeFlag))
	conn.commit()

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

def populateVar(dataArray):
	ident = (dataArray[0])			#ID de la mota
	temp =	float(dataArray[1])		#Convertir valor de temp a Float
	H =		float(dataArray[2])		#Convertir valor de humedad a Float
	smokeVar =	float(dataArray[3])	#Valor de sensor de Humo
	lat =	float(dataArray[4])		#Latitude
	longit =	float(dataArray[5])	#Longitude
	tempFlag =	float(dataArray[6])	#Alerta de Temp
	smokeFlag =	float(dataArray[7])	#Alerta de humo

	cID = ident
	print ident


# FUNCION PRINCIPAL
while True: # While loop that loops forever
    while (arduinoData.inWaiting()==0):			#Wait here until there is data
        pass #do nothing
    arduinoString = arduinoData.readline() 		#read the line of text from the serial port
    # data_raw = arduinoData.readline().decode().strip()
    # if data_raw:
    #     arduinoString = data_raw
    dataArray = 	arduinoString.split(',')    #Split it into an array called dataArray
    dataArrayTemp = dataArray

    print dataArray
    # ident = 		( dataArray[0])			
    # temp = 			float( dataArray[1])    
    # H =    			float( dataArray[2])    #Convert second element to floating number and put in P
    # smokeVar =		float( dataArray[3])	#Sensor de humo
    # lat =  			float( dataArray[4])	#Latitud de la mota
    # longit= 		float( dataArray[5])	#Longitud de la mota
    # tempFlag=		( dataArray[6])			#Flag de Sensor de Temp
    # smokeFlag=		( dataArray[7])			#Flag de Sensor de humo	
    # cID = ident
    # print ident
    

    # populateVar(dataArray)
    
    if dataArrayTemp[0]=='111':
    	populateVar(dataArrayTemp)
    	tempF1.append(temp)
    	humidity1.append(H)
    	smokeVar1.append(smokeVar)
    	tempFlag1.append(tempFlag)
    	smokeFlag1.append(smokeFlag)
    	url = "http://sipreif.isaacvilchez.com/boscosas-insert?id="+str(ident)+"&temp="+str(temp)+"&hum="+str(H)+"&smokeVar="+str(smokeVar)+"&tempFlag="+str(tempFlag)+"&smokeFlag="+str(smokeFlag)
        urllib.urlopen(url)
        if (temp > 90):
    		subject = "ALERTA SENSOR 111"
    		email(subject)
    elif dataArrayTemp[0]=='211':
    	populateVar(dataArrayTemp)
    	tempF2.append(temp)
    	humidity2.append(H)
    	smokeVar2.append(smokeVar)
    	tempFlag2.append(tempFlag)
    	smokeFlag2.append(smokeFlag)
    	url = "http://sipreif.isaacvilchez.com/boscosas-insert?id="+str(ident)+"&temp="+str(temp)+"&hum="+str(H)+"&smokeVar="+str(smokeVar)+"&tempFlag="+str(tempFlag)+"&smokeFlag="+str(smokeFlag)
    	urllib.urlopen(url)
    	if (temp > 90):
    		subject="ALERTA SENSOR 211"
    		email(subject)
    elif dataArrayTemp[0]=='311':
    	populateVar(dataArrayTemp)
    	tempF3.append(temp)
    	humidity3.append(H)
    	smokeVar3.append(smokeVar)
    	tempFlag3.append(tempFlag)
    	smokeFlag3.append(smokeFlag)
    	url = "http://sipreif.isaacvilchez.com/boscosas-insert?id="+str(ident)+"&temp="+str(temp)+"&hum="+str(H)+"&smokeVar="+str(smokeVar)+"&tempFlag="+str(tempFlag)+"&smokeFlag="+str(smokeFlag)
    	urllib.urlopen(url)
    	if (temp > 90):
    		subject="ALERTA SENSOR 311" 
    		email(subject)

    tempF.append(temp)	#Build our tempF array by appending temp readings
    humidity.append(H)	#Building our humidity array by appending P readings
    sVar.append(smokeVar)
    tFlag.append(tempFlag)
    sFlag.append(smokeFlag)

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

