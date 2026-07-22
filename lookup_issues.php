<?php

header('Content-Type: application/json');

$query = isset($_GET['query']) ? strtolower(trim($_GET['query'])) : '';

if (empty($query)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a vehicle name.']);
    exit;
}

$knowledgeBase = [
    'toyota aqua' => [
        'car' => 'Toyota Aqua',
        'points' => [
            'Hybrid Battery Degradation: Battery capacity loss after 5-7 years, especially in hot climates. Cells may need balancing or replacement.',
            'Brake Actuator Noise: Squeaking or grinding sounds from the brake system indicating internal pressure leaks or seal degradation.',
            'Cooling Fan Clogging: Hybrid battery cooling fan accumulates dust leading to overheating and reduced battery performance.'
        ],
        'rating' => 'Medium-High'
    ],
    'toyota axio' => [
        'car' => 'Toyota Axio',
        'points' => [
            'Hybrid Battery Cell Imbalance: Individual cells degrade at different rates requiring recalibration or cell replacement after 150,000km.',
            'Steering Rack Play: Develops rattling noises on uneven roads due to worn bushings and loose connections.',
            'Inverter Coolant Pump Failure: Dedicated pump for hybrid inverter can fail causing system overheating and loss of power.'
        ],
        'rating' => 'High'
    ],
    'toyota prius' => [
        'car' => 'Toyota Prius',
        'points' => [
            'Hybrid Battery Degradation: Common after 8-10 years. Individual cells fail requiring module replacement or reconditioning.',
            'Headlight Condensation: Moisture buildup inside sealed headlight units during monsoon season affecting visibility.',
            'EGR System Clogging: Exhaust Gas Recirculation valve accumulates carbon deposits reducing fuel efficiency and causing rough idling.'
        ],
        'rating' => 'High'
    ],
    'toyota vitz' => [
        'car' => 'Toyota Vitz',
        'points' => [
            'CVT Transmission Issues: Jerking or slipping especially when cold. May require fluid change or complete CVT replacement.',
            'Power Window Motor Failure: Window regulators commonly fail in tropical humidity requiring motor replacement.',
            'Engine Mount Deterioration: Rubber mounts wear faster in hot climates causing excessive vibration at idle.'
        ],
        'rating' => 'Medium-High'
    ],
    'toyota premio' => [
        'car' => 'Toyota Premio',
        'points' => [
            'CVT Transmission Shudder: Hesitation or vibration during acceleration particularly in 2007-2012 models.',
            'Suspension Bushing Wear: Front lower arm bushings deteriorate quickly on rough roads causing clunking sounds.',
            'Alternator Failure: Higher failure rate in humid climates, typical lifespan 5-7 years with warning light appearing.'
        ],
        'rating' => 'Medium-High'
    ],
    
    'honda vezel' => [
        'car' => 'Honda Vezel',
        'points' => [
            'CVT Transmission Judder: Vibration during acceleration especially in hybrid models. Software updates or CVT fluid change may help.',
            'Electric Power Steering Failure: Loss of power assist particularly in hot weather. Steering becomes heavy requiring column replacement.',
            'Brake Booster Issues: Reduced braking pressure in humid conditions. Vacuum leak or internal seal failure common after 100,000km.'
        ],
        'rating' => 'Medium-High'
    ],
    'honda fit' => [
        'car' => 'Honda Fit',
        'points' => [
            'CVT Transmission Problems: Jerking, slipping, or complete failure in 2015-2017 models. Extended warranty recommended.',
            'AC Compressor Failure: Air conditioning compressor seizes or makes loud noises. Common in tropical heat after 5 years.',
            'Door Lock Actuator Issues: Power door locks fail to lock/unlock. Actuator motor burns out requiring replacement.'
        ],
        'rating' => 'Medium'
    ],
    'honda civic' => [
        'car' => 'Honda Civic',
        'points' => [
            'CVT Transmission Shudder: Vibration at low speeds especially in 2016-2018 models. May require software update or CVT replacement.',
            'AC Condenser Damage: Front-mounted condenser vulnerable to road debris causing refrigerant leaks and AC failure.',
            'Infotainment System Freezing: Touchscreen becomes unresponsive or reboots randomly. Software update usually fixes issue.'
        ],
        'rating' => 'Medium-High'
    ],
    
    'nissan leaf' => [
        'car' => 'Nissan Leaf',
        'points' => [
            'Battery Capacity Loss: Significant range reduction after 5+ years in tropical heat. Battery health drops to 70-80% capacity.',
            'Charging Port Connector Wear: Loose connections at charging port causing slow or failed charging. Connector replacement needed.',
            'Inverter Overheating: Reduced performance and power delivery in Sri Lankan climate. Cooling system may need service.'
        ],
        'rating' => 'Medium'
    ],
    'nissan march' => [
        'car' => 'Nissan March',
        'points' => [
            'CVT Transmission Issues: Whining noise, jerking, or slipping. Common after 100,000km requiring CVT fluid change or replacement.',
            'Fuel Pump Failure: Engine stalling or rough running due to weak fuel pump. Typical failure point around 8-10 years.',
            'Wheel Bearing Noise: Humming sound from wheels at highway speeds. Bearings wear faster on rough roads.'
        ],
        'rating' => 'Medium'
    ],
    
    'suzuki wagon r' => [
        'car' => 'Suzuki Wagon R',
        'points' => [
            'ISG System Issues: Integrated Starter Generator fails causing stalling or charging problems. Common in stop-start models.',
            'Front Suspension Bushing Wear: Fast deterioration on uneven Sri Lankan roads causing rattling and poor handling.',
            'Lithium-ion Battery Degradation: ISG battery sensitive to extreme heat. Battery health monitoring essential after 5 years.'
        ],
        'rating' => 'Medium'
    ],
    'suzuki swift' => [
        'car' => 'Suzuki Swift',
        'points' => [
            'Transmission Issues: Manual gearbox synchro wear or CVT shudder common after 100,000km. Gear changes become difficult.',
            'Clutch Wear: In manual models, clutch lasts 60,000-80,000km in city driving. Slipping or hard engagement indicates replacement.',
            'Suspension Noise: Front strut mounts and bushings wear quickly causing clunking sounds over bumps.'
        ],
        'rating' => 'Medium-High'
    ],
    
    'mitsubishi outlander' => [
        'car' => 'Mitsubishi Outlander',
        'points' => [
            'PHEV Battery Degradation: Plugin hybrid battery capacity loss after 5-7 years. Electric range reduces significantly.',
            'Transmission Issues: CVT or automatic transmission jerking, slipping, or overheating. Fluid maintenance critical.',
            'Air Conditioning Problems: AC compressor failure or refrigerant leaks common in tropical climate after 6-8 years.'
        ],
        'rating' => 'Medium'
    ],
    
    'mazda axela' => [
        'car' => 'Mazda Axela (Mazda3)',
        'points' => [
            'Automatic Transmission Issues: Harsh shifting or slipping in 2010-2013 models. Transmission fluid change may help.',
            'Suspension Component Wear: Control arm bushings and ball joints wear faster than competitors on rough roads.',
            'Rust Issues: Undercarriage rust common in coastal areas. Regular rust-proofing treatment recommended.'
        ],
        'rating' => 'Medium-High'
    ],
    'mazda demio' => [
        'car' => 'Mazda Demio (Mazda2)',
        'points' => [
            'Automatic Transmission Problems: Jerky shifts or slipping especially in 4-speed auto models. May need rebuild.',
            'Engine Mount Failure: Worn mounts cause excessive vibration at idle. Common after 80,000km.',
            'Power Window Regulator Failure: Windows move slowly or get stuck. Regulator replacement needed.'
        ],
        'rating' => 'Medium'
    ],
    
    'bmw' => [
        'car' => 'BMW (General)',
        'points' => [
            'Cooling System Failures: Water pump, thermostat, or radiator failures common after 80,000km. Overheating can cause engine damage.',
            'Electronic Issues: Various sensor and module failures. Expensive dealer diagnostics and repairs required.',
            'Oil Leaks: Valve cover gasket, oil filter housing, and oil pan gaskets leak after 100,000km. Regular monitoring essential.'
        ],
        'rating' => 'Medium-Low'
    ],
    
    'mercedes' => [
        'car' => 'Mercedes-Benz (General)',
        'points' => [
            'Air Suspension Failure: Air struts leak or compressor fails. Very expensive repair, can convert to coil springs.',
            'Transmission Issues: 7-speed automatic transmission jerking or rough shifts. Software updates or valve body replacement.',
            'Electrical Problems: Multiple sensor failures, window regulators, seat modules. High repair costs at authorized dealers.'
        ],
        'rating' => 'Medium-Low'
    ],
    
    'audi' => [
        'car' => 'Audi (General)',
        'points' => [
            'Timing Chain Tensioner Failure: Engine rattling on startup. Can cause catastrophic engine damage if not addressed.',
            'Carbon Buildup: Direct injection engines develop intake valve carbon deposits reducing performance and fuel economy.',
            'Electronic Issues: MMI system failures, sensor malfunctions. Expensive dealer diagnostics and parts required.'
        ],
        'rating' => 'Medium-Low'
    ],
    
    'hyundai' => [
        'car' => 'Hyundai (General)',
        'points' => [
            'Engine Failure: Some models (2011-2019) have engine seizure issues due to manufacturing defects. Check recall status.',
            'Transmission Problems: Dual-clutch or automatic transmission shudder, slipping. Software updates available for some models.',
            'Suspension Wear: Front suspension components wear faster than Japanese competitors on rough roads.'
        ],
        'rating' => 'Medium'
    ],
    
    'kia' => [
        'car' => 'KIA (General)',
        'points' => [
            'Engine Seizure: Theta II engine issues in 2011-2019 models. Check for recalls and extended warranties.',
            'Transmission Shudder: Dual-clutch transmissions develop jerking or hesitation. Software updates may help.',
            'Electrical Issues: Infotainment system freezes, sensor failures. Generally less expensive than European brands to repair.'
        ],
        'rating' => 'Medium'
    ]
];

$found = false;
foreach ($knowledgeBase as $key => $data) {
    if (strpos($query, $key) !== false || strpos($key, $query) !== false) {
        echo json_encode([
            'success' => true,
            'results' => [[
                'car' => $data['car'],
                'points' => $data['points'],
                'rating' => $data['rating'] . ' Reliability'
            ]]
        ]);
        $found = true;
        break;
    }
}

if (!$found) {
    $partialMatches = [];
    foreach ($knowledgeBase as $key => $data) {
        $carWords = explode(' ', $key);
        foreach ($carWords as $word) {
            if (strlen($word) > 3 && strpos($query, $word) !== false) {
                $partialMatches[] = $data;
                break;
            }
        }
    }
    
    if (!empty($partialMatches)) {
        echo json_encode([
            'success' => true,
            'results' => array_map(function($data) {
                return [
                    'car' => $data['car'],
                    'points' => $data['points'],
                    'rating' => $data['rating'] . ' Reliability'
                ];
            }, $partialMatches)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No specific data found for "' . htmlspecialchars($query) . '". Try searching for: Toyota Aqua, Honda Vezel, Nissan Leaf, Suzuki Wagon R, BMW, Mercedes, Mazda, etc.'
        ]);
    }
}