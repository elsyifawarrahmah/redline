import paho.mqtt.client as mqtt
import requests
import json

def on_connect(client, userdata, flags, rc):
    print("Connected to MQTT broker, rc:", rc)
    client.subscribe("redline/data")

def on_message(client, userdata, msg):
    try:
        data = json.loads(msg.payload.decode())
        print("Data Dari ESP32:", data)
        r = requests.post(
            "https://redline-production-6b62.up.railway.app/api/mqtt-receiver.php",
            json=data,
            timeout=10
        )
        print("Status simpan:", r.status_code, r.text)
    except Exception as e:
        print("Error:", e)

print("Starting MQTT Bridge...")
client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message
client.connect("broker.emqx.io", 1883, 60)
print("Bridge running, waiting for data...")
client.loop_forever()
