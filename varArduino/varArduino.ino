#include <LowPower.h>
#include <DHT11.h>

int pin=4;
DHT11 dht11(pin);

// Your threshold value
int smokeSensorThres;
int tempSensorThres;

float tempC;  // Variable para la temperatura en C
float tempF;  // Variable para la temperatura en F
float hum;    // Variable para la humedad relativa
int smokeVar;  // Variable del sensor de humo
int ID;       // ID de la Mota
float lat;    // Latitud de la mota
float lon;    // Longitud de la mota

void setup(){
  Serial.begin(9600); //turn on serial monitor
  ID = 311;
  lat = 19.17;
  lon = -71.05;
  
  smokeSensorThres = 200;
  tempSensorThres = 95;
  
//  pinMode(12, OUTPUT);    // Temp VCC
//  digitalWrite(12, HIGH); // sets VCC 

}

void loop() {
  // put 5 mins sleep mode
  // As lowpower library support maximam 8s ,we use for loop to take longer (5mins) sleep
  // 5x60=300
  // 300/4=75
  //for(int i=0;i<1;i++)
  //{
  //  LowPower.powerDown(SLEEP_4S, ADC_OFF, BOD_OFF);
  //}
  
  // Sensor de humo
  smokeVar = analogRead(A0);
  
  // Flags
  int tempFlag;
  int smokeFlag;
  
  int err;
  float temp, humi;
  if((err=dht11.read(humi, temp))==0)
  {
    //Serial.print("temperature:");
    tempC = temp;            // Guardando temperatura en C y 
    tempF = tempC*1.8 + 32.; // Convirtiendo de C a F
    
    // Getting the flags
    tempFlag =  tempAlert(tempF,tempSensorThres);
    smokeFlag = smokeAlert(smokeVar, smokeSensorThres);
    
    //Serial.println("This is working?");
    Serial.print(ID);
    Serial.print(",");
    Serial.print(tempF);
    Serial.print(",");
    Serial.print(humi);
    Serial.print(",");
    Serial.print(smokeVar);
    Serial.print(",");
    Serial.print(lat);
    Serial.print(",");
    Serial.print(lon);
    Serial.print(",");
    Serial.print(tempFlag);
    Serial.print(",");
    Serial.println(smokeFlag);    
  }
  else
  {
    Serial.println();
    Serial.print("Error No :");
    Serial.print(err);
    Serial.println();    
  }
  
//  Serial.println(" ");
//  Serial.println("NOT A TEST!!!!!");
//  Serial.println(" ");
//  Serial.print(ID);
//  Serial.print(",");
//  Serial.print(temp);
//  Serial.print(",");
//  Serial.print(humi);
//  Serial.print(",");
//  Serial.println(smokeVar);
//  
  delay(3000);
  //delay(DHT11_RETRY_DELAY); //delay for reread 
}


// Creating the function that will evaluate the flag for Smoke
int tempAlert (int ts, int tst){
  
  int flag;
  // HIGH TEMP ALERT
  if (ts > tst){
    Serial.println("======================================");
    Serial.println("ALERT: HIGH TEMP LEVELS");
    Serial.println("======================================");
    flag=1;
  }
  else {
    flag=0;
  }
  
  return flag;
}

// Creating the function that will evaluate the flag for Smoke
int smokeAlert (int ss, int sst){
  
  int flag;
  // SMOKE ALERT
  if (ss > sst){
    Serial.println("======================================");
    Serial.println("ALERT: HIGH SMOKE LEVELS!!!!!");
    Serial.println("======================================");
    flag = 1;
  }
  else {
    //Serial.println(" ");
    flag = 0;
  }
  
  return flag;
}
