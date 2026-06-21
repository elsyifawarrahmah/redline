import paho.mqtt.client as mqtt
import requests
import json

def on_message(client, userdata, msg):
    try:
        data = json.loads(msg.payload.decode())
        print("Data Dari ESP32:", data)
        r = requests.post("http://192.168.6.10/api/mqtt-receiver.php", json=data)
        print("Status simpan:", r.status_code)
    except Exception as e:
        print("Error:", e)

print("Starting MQTT Bridge...")
client = mqtt.Client()
client.on_message = on_message
client.connect("broker.emqx.io", 1883)
client.subscribe("redline/data")
print("Bridge running, waiting for data...")
client.loop_forever()
