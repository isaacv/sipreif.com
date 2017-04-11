#include <LowPower.h>
#include <DHT11.h>

int pin=4;
DHT11 dht11(pin); 

float tempC;  // Variable para la temperatura en C
float tempF;  // Variable para la temperatura en F
float hum;    // Variable para la humedad relativa
int ID;       // ID de la Mota
float lat;    // Latitud de la mota
float lon;    // Longitud de la mota

void setup(){
  Serial.begin(9600); //turn on serial monitor
  ID = 111;
  lat = 19.17;
  lon = -71.05;

}

void loop() {
  // put 5 mins sleep mode
  // As lowpower library support maximam 8s ,we use for loop to take longer (5mins) sleep
  // 5x60=300
  // 300/4=75
  for(int i=0;i<1;i++)
  {
    LowPower.powerDown(SLEEP_4S, ADC_OFF, BOD_OFF);
  }
  
  int err;
  float temp, humi;
  if((err=dht11.read(humi, temp))==0)
  {
    //Serial.print("temperature:");
    tempC = temp;            // Guardando temperatura en C y 
    tempF = tempC*1.8 + 32.; // Convirtiendo de C a F
  
    Serial.print(ID);
    Serial.print(",");
    Serial.print(tempF);
    Serial.print(",");
    Serial.print(humi);
    Serial.print(",");
    Serial.print(lat);
    Serial.print(",");
    Serial.println(lon);
  }
  else
  {
    Serial.println();
    Serial.print("Error No :");
    Serial.print(err);
    Serial.println();    
  }
  delay(DHT11_RETRY_DELAY); //delay for reread
  
  
 
}
