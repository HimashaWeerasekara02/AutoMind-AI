# AutoMind AI - Personal Vehicle Management System

An intelligent, AI-powered vehicle management platform that helps users organize vehicle information, track maintenance activities, manage fuel consumption, and receive predictive maintenance insights through integrated Gemini AI.

## 🧠 AI-Powered Features
* **Mechanic Bill OCR Analysis:** Upload a photo of any mechanic bill — Gemini AI extracts line items, costs, and audits prices against market benchmarks.
* **Known Issues Lookup:** Search any automotive keyword (e.g., "Transmission") and receive AI-generated diagnostic information with severity ratings and estimated repair costs.
* **Predictive Maintenance:** AI analyzes your vehicle's service and fuel history to predict upcoming maintenance needs before they become critical.

## 🚗 Core Features
* **Command Center Dashboard:** Real-time telemetry view with estimated fuel level, fuel efficiency (KM/L), estimated range, and a reliability index.
* **Multi-Vehicle Garage:** Manage multiple vehicles with individual profiles, nicknames, and specifications.
* **Maintenance Logging:** Full CRUD for service records with cost tracking and service provider details.
* **Fuel Tracking:** Log fuel entries with odometer readings to calculate real-time fuel efficiency.
* **Digital Glovebox:** Securely store and manage vehicle documents (insurance, licenses) with expiry date tracking and alerts.
* **Diagnostics & Cost Estimator:** Quick cost estimation tool for OEM, aftermarket, and refurbished parts.
* **Support Ticket System:** Built-in customer support with admin reply functionality.

## 🛠️ Tech Stack
* **Frontend:** HTML5, Tailwind CSS (CDN), JavaScript, Chart.js
* **Backend:** PHP (Vanilla)
* **Database:** Firebase Realtime Database (REST API)
* **AI Engine:** Google Gemini 3.6 Flash API
* **Architecture:** Serverless Database + PHP Backend

## ⚙️ Running Locally

1. **Prerequisites:** A local PHP server environment (XAMPP, MAMP, or WAMP).

2. **Clone the repository**
   ```bash
   git clone https://github.com/HimashaWeerasekara02/AutoMind-AI.git
   ```

3. **Move to Server Directory**
   Copy the cloned folder into your local server's `htdocs/` directory.

4. **Run the Application**
   Start Apache in XAMPP/MAMP, then open:
   ```
   http://localhost/AutoMind-AI/
   ```

## 🔮 Future Implementations (Ongoing)
* **Custom Machine Learning Model:** Currently developing a custom PyTorch/TensorFlow ML model trained on Kaggle automotive datasets. This will replace the basic diagnostic heuristics with a true neural-network-driven price prediction engine for automotive parts and labor.