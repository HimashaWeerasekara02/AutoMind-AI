import json
import random

cars = [
    {"make": "Suzuki", "model": "WagonR", "multiplier": 0.85},
    {"make": "Suzuki", "model": "Celerio", "multiplier": 0.85},
    {"make": "Suzuki", "model": "Alto", "multiplier": 0.80},
    {"make": "Toyota", "model": "Axio", "multiplier": 1.0},
    {"make": "Toyota", "model": "Vitz", "multiplier": 1.0},
    {"make": "Toyota", "model": "Aqua", "multiplier": 1.1},
    {"make": "Honda", "model": "Fit", "multiplier": 1.0},
    {"make": "Honda", "model": "Vezel", "multiplier": 1.35},
    {"make": "Toyota", "model": "Land Cruiser Prado", "multiplier": 2.5},
]


parts_db = [
    {"name": "Front Bumper", "base": 25000},
    {"name": "Brake Pads (Set)", "base": 8500},
    {"name": "Oil Filter", "base": 2500},
    {"name": "Air Filter", "base": 3500},
    {"name": "Headlight Assembly", "base": 18000},
    {"name": "Tail Light Assembly", "base": 9000},
    {"name": "Shock Absorber", "base": 15000},
    {"name": "Alternator", "base": 35000},
    {"name": "Starter Motor", "base": 32000},
    {"name": "Radiator", "base": 20000},
    {"name": "AC Compressor", "base": 45000},
    {"name": "Brake Disc", "base": 12000},
]

num_rows = 1000
data = {}

print(f"Generating {num_rows} records for Sri Lankan market...")

for i in range(num_rows):
    vehicle = random.choice(cars)
    part = random.choice(parts_db)
    condition = random.choice(["New", "Used"])
    
    variance = random.uniform(0.9, 1.1)
    price = int(part["base"] * vehicle["multiplier"] * variance)
    
    if condition == "Used":
        price = int(price * 0.5)

    price = int(round(price / 50) * 50)

    node_id = f"part_{i+1:04d}"
    
    data[node_id] = {
        "make": vehicle["make"],
        "model": vehicle["model"],
        "part_name": part["name"],
        "condition": condition,
        "price_lkr": price
    }

output_file = "sl_car_parts_dataset.json"
with open(output_file, "w") as f:
    json.dump(data, f, indent=4)

print(f"Successfully created: {output_file}")
print("You can now import this file into your Firebase node 'car_parts_dataset'.")