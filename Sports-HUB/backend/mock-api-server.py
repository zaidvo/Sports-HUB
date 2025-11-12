#!/usr/bin/env python3
"""
Mock Backend API Server for SportzHub
Matches the exact PHP backend API structure and endpoints
"""

import json
import sys
from datetime import datetime, timedelta
from http.server import HTTPServer, BaseHTTPRequestHandler
from urllib.parse import urlparse, parse_qs
import uuid

# Mock data matching backend structure
USERS = [
    {"user_id": 1, "full_name": "John Doe", "email": "john@example.com", "phone_number": "1234567890", "role": "user"},
    {"user_id": 2, "full_name": "Admin User", "email": "admin@test.com", "phone_number": "0987654321", "role": "admin"}
]

COURTS = [
    {
        "court_id": 1,
        "court_name": "Futsal Court A",
        "court_type": "Futsal",
        "location": "Downtown Sports Complex",
        "price_per_hour": 50.00,
        "description": "Professional futsal court with artificial grass",
        "image_url": "/images/futsal1.jpg",
        "is_active": True
    },
    {
        "court_id": 2,
        "court_name": "Badminton Court 1",
        "court_type": "Badminton", 
        "location": "City Sports Center",
        "price_per_hour": 30.00,
        "description": "Indoor badminton court with wooden floor",
        "image_url": "/images/badminton1.jpg",
        "is_active": True
    },
    {
        "court_id": 3,
        "court_name": "Padel Court Elite",
        "court_type": "Padel",
        "location": "Premium Sports Club", 
        "price_per_hour": 60.00,
        "description": "Premium padel court with glass walls",
        "image_url": "/images/padel1.jpg",
        "is_active": True
    }
]

BOOKINGS = []

class MockAPIHandler(BaseHTTPRequestHandler):
    def _set_cors_headers(self):
        """Set CORS headers for frontend integration"""
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        self.send_header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
        
    def _send_json_response(self, data, status_code=200):
        """Send JSON response with proper headers"""
        self.send_response(status_code)
        self.send_header('Content-Type', 'application/json')
        self._set_cors_headers()
        self.end_headers()
        
        response_json = json.dumps(data, indent=2)
        self.wfile.write(response_json.encode('utf-8'))

    def _get_request_data(self):
        """Get JSON data from POST/PUT requests"""
        content_length = int(self.headers.get('Content-Length', 0))
        if content_length > 0:
            post_data = self.rfile.read(content_length)
            try:
                return json.loads(post_data.decode('utf-8'))
            except json.JSONDecodeError:
                return {}
        return {}

    def do_OPTIONS(self):
        """Handle CORS preflight requests"""
        self.send_response(200)
        self._set_cors_headers()
        self.end_headers()

    def do_GET(self):
        """Handle GET requests"""
        path = urlparse(self.path).path.strip('/')
        query = parse_qs(urlparse(self.path).query)
        segments = path.split('/') if path else []
        
        print(f"GET /{path}")
        
        try:
            if not segments or segments[0] == '':
                self._send_json_response({"success": True, "message": "SportzHub API Server", "version": "1.0"})
                
            elif segments[0] == 'auth':
                self._handle_auth_get(segments, query)
                
            elif segments[0] == 'courts':
                self._handle_courts_get(segments, query)
                
            elif segments[0] == 'admin':
                self._handle_admin_get(segments, query)
                
            elif segments[0] == 'bookings':
                self._handle_bookings_get(segments, query)
                
            else:
                self._send_json_response({"success": False, "message": f"Endpoint not found: /{path}"}, 404)
                
        except Exception as e:
            print(f"Error handling GET request: {e}")
            self._send_json_response({"success": False, "message": f"Internal server error: {str(e)}"}, 500)

    def do_POST(self):
        """Handle POST requests"""
        path = urlparse(self.path).path.strip('/')
        data = self._get_request_data()
        segments = path.split('/') if path else []
        
        print(f"POST /{path} - Data: {data}")
        
        try:
            if segments[0] == 'auth':
                self._handle_auth_post(segments, data)
                
            elif segments[0] == 'bookings':
                self._handle_bookings_post(segments, data)
                
            elif segments[0] == 'admin':
                self._handle_admin_post(segments, data)
                
            else:
                self._send_json_response({"success": False, "message": f"POST endpoint not found: /{path}"}, 404)
                
        except Exception as e:
            print(f"Error handling POST request: {e}")
            self._send_json_response({"success": False, "message": f"Internal server error: {str(e)}"}, 500)

    def _handle_auth_get(self, segments, query):
        """Handle auth GET requests"""
        if len(segments) == 1:
            self._send_json_response({"success": False, "message": "Auth endpoint requires action"}, 400)
        else:
            self._send_json_response({"success": False, "message": f"GET not supported for /auth/{segments[1]}"}, 405)

    def _handle_auth_post(self, segments, data):
        """Handle auth POST requests"""
        if len(segments) < 2:
            self._send_json_response({"success": False, "message": "Auth endpoint requires action"}, 400)
            return
            
        action = segments[1]
        
        if action == 'login':
            email = data.get('email', '')
            password = data.get('password', '')
            
            if email and password:
                if 'admin' in email.lower():
                    user = {"id": 1, "name": "Admin User", "email": email, "role": "admin"}
                else:
                    user = {"id": 2, "name": email.split('@')[0].title(), "email": email, "role": "user"}
                
                response = {
                    "success": True,
                    "token": f"mock_token_{uuid.uuid4().hex[:16]}",
                    "user": user
                }
                self._send_json_response(response)
            else:
                self._send_json_response({"success": False, "message": "Email and password required"}, 400)
                
        elif action == 'register':
            required_fields = ['full_name', 'email', 'phone_number', 'password']
            if all(field in data for field in required_fields):
                user = {
                    "id": len(USERS) + 1,
                    "name": data['full_name'],
                    "email": data['email'],
                    "role": "user"
                }
                response = {
                    "success": True,
                    "token": f"mock_token_{uuid.uuid4().hex[:16]}",
                    "user": user
                }
                self._send_json_response(response, 201)
            else:
                self._send_json_response({"success": False, "message": "Missing required fields"}, 400)
                
        elif action == 'logout':
            self._send_json_response({"success": True, "message": "Logged out successfully"})
            
        else:
            self._send_json_response({"success": False, "message": f"Auth action not found: {action}"}, 404)

    def _handle_courts_get(self, segments, query):
        """Handle courts GET requests"""
        if len(segments) == 1:
            # GET /courts
            filtered_courts = COURTS.copy()
            
            if 'type' in query:
                court_type = query['type'][0]
                filtered_courts = [c for c in filtered_courts if c['court_type'].lower() == court_type.lower()]
                
            self._send_json_response({"success": True, "courts": filtered_courts})
            
        elif len(segments) == 2:
            if segments[1] == 'types':
                # GET /courts/types
                types = list(set(court['court_type'] for court in COURTS))
                self._send_json_response({"success": True, "types": types})
                
            elif segments[1] == 'locations':
                # GET /courts/locations
                locations = list(set(court['location'] for court in COURTS))
                self._send_json_response({"success": True, "locations": locations})
                
            elif segments[1] == 'search':
                # GET /courts/search
                filtered_courts = COURTS.copy()
                self._send_json_response({"success": True, "courts": filtered_courts})
                
            elif segments[1].isdigit():
                # GET /courts/{id}
                court_id = int(segments[1])
                court = next((c for c in COURTS if c['court_id'] == court_id), None)
                if court:
                    self._send_json_response({"success": True, "court": court})
                else:
                    self._send_json_response({"success": False, "message": "Court not found"}, 404)
            else:
                self._send_json_response({"success": False, "message": "Invalid court endpoint"}, 404)
                
        elif len(segments) == 3 and segments[1].isdigit() and segments[2] == 'slots':
            # GET /courts/{id}/slots
            court_id = int(segments[1])
            date = query.get('date', [datetime.now().strftime('%Y-%m-%d')])[0]
            
            # Generate mock time slots
            slots = []
            for hour in range(8, 22):
                slots.append({
                    "slot_id": f"{court_id}_{date}_{hour}",
                    "court_id": court_id,
                    "date": date,
                    "start_time": f"{hour:02d}:00",
                    "end_time": f"{hour+1:02d}:00",
                    "is_booked": hour in [12, 15, 18]  # Some slots are booked
                })
            
            available_slots = [s for s in slots if not s['is_booked']]
            self._send_json_response({"success": True, "slots": available_slots})
            
        else:
            self._send_json_response({"success": False, "message": "Court endpoint not found"}, 404)

    def _handle_bookings_get(self, segments, query):
        """Handle bookings GET requests"""
        if len(segments) == 1:
            # GET /bookings (user's bookings)
            self._send_json_response({"success": True, "bookings": BOOKINGS})
        elif len(segments) == 2 and segments[1].isdigit():
            # GET /bookings/{id}
            booking_id = int(segments[1])
            booking = next((b for b in BOOKINGS if b['booking_id'] == booking_id), None)
            if booking:
                self._send_json_response({"success": True, "booking": booking})
            else:
                self._send_json_response({"success": False, "message": "Booking not found"}, 404)
        else:
            self._send_json_response({"success": False, "message": "Booking endpoint not found"}, 404)

    def _handle_bookings_post(self, segments, data):
        """Handle bookings POST requests"""
        if len(segments) == 1:
            # POST /bookings
            required_fields = ['user_id', 'slot_id']
            if all(field in data for field in required_fields):
                booking = {
                    "booking_id": len(BOOKINGS) + 1,
                    "user_id": data['user_id'],
                    "slot_id": data['slot_id'],
                    "court_id": data.get('court_id', 1),
                    "booking_date": datetime.now().isoformat(),
                    "amount_paid": data.get('amount_paid', 50.00),
                    "payment_status": data.get('payment_status', 'Confirmed'),
                    "status": "Confirmed",
                    "confirmation_message": f"Booking confirmed for slot {data['slot_id']}"
                }
                BOOKINGS.append(booking)
                self._send_json_response({"success": True, "booking": booking, "message": "Booking created successfully"}, 201)
            else:
                self._send_json_response({"success": False, "message": "Missing required fields"}, 400)
        else:
            self._send_json_response({"success": False, "message": "Invalid booking endpoint"}, 404)

    def _handle_admin_get(self, segments, query):
        """Handle admin GET requests"""
        if len(segments) < 2:
            self._send_json_response({"success": False, "message": "Admin endpoint requires action"}, 400)
            return
            
        action = segments[1]
        
        if action == 'dashboard':
            stats = {
                "total_bookings": len(BOOKINGS),
                "total_revenue": sum(b.get('amount_paid', 0) for b in BOOKINGS),
                "active_courts": len(COURTS),
                "todays_bookings": len([b for b in BOOKINGS if b.get('date') == datetime.now().strftime('%Y-%m-%d')])
            }
            self._send_json_response({"success": True, "stats": stats})
            
        elif action == 'bookings':
            if len(segments) == 2:
                # GET /admin/bookings
                self._send_json_response({"success": True, "bookings": BOOKINGS})
            elif len(segments) == 3 and segments[2] == 'today':
                # GET /admin/bookings/today
                today = datetime.now().strftime('%Y-%m-%d')
                todays_bookings = [b for b in BOOKINGS if b.get('date') == today]
                self._send_json_response({"success": True, "bookings": todays_bookings})
            else:
                self._send_json_response({"success": False, "message": "Admin booking endpoint not found"}, 404)
                
        elif action == 'courts':
            # GET /admin/courts
            self._send_json_response({"success": True, "courts": COURTS})
            
        else:
            self._send_json_response({"success": False, "message": f"Admin action not found: {action}"}, 404)

    def _handle_admin_post(self, segments, data):
        """Handle admin POST requests"""
        if len(segments) < 2:
            self._send_json_response({"success": False, "message": "Admin endpoint requires action"}, 400)
            return
            
        action = segments[1]
        
        if action == 'courts':
            # POST /admin/courts - create new court
            required_fields = ['court_name', 'court_type', 'location', 'price_per_hour']
            if all(field in data for field in required_fields):
                new_court = {
                    "court_id": len(COURTS) + 1,
                    "court_name": data['court_name'],
                    "court_type": data['court_type'],
                    "location": data['location'],
                    "price_per_hour": float(data['price_per_hour']),
                    "description": data.get('description', ''),
                    "image_url": data.get('image_url', ''),
                    "is_active": True
                }
                COURTS.append(new_court)
                self._send_json_response({"success": True, "court": new_court, "message": "Court created successfully"}, 201)
            else:
                self._send_json_response({"success": False, "message": "Missing required fields"}, 400)
        else:
            self._send_json_response({"success": False, "message": f"Admin POST action not found: {action}"}, 404)

    def log_message(self, format, *args):
        """Override to reduce log noise"""
        pass

def run_server(port=3001):
    """Start the mock API server"""
    server_address = ('', port)
    httpd = HTTPServer(server_address, MockAPIHandler)
    
    print(f"🚀 SportzHub Mock API Server starting on port {port}")
    print(f"📍 Available endpoints (matching PHP backend structure):")
    print(f"   POST http://localhost:{port}/auth/login")
    print(f"   POST http://localhost:{port}/auth/register")
    print(f"   GET  http://localhost:{port}/courts")
    print(f"   GET  http://localhost:{port}/courts/types")
    print(f"   GET  http://localhost:{port}/courts/locations")
    print(f"   GET  http://localhost:{port}/courts/{{id}}/slots")
    print(f"   POST http://localhost:{port}/bookings")
    print(f"   GET  http://localhost:{port}/admin/dashboard")
    print(f"   GET  http://localhost:{port}/admin/bookings")
    print(f"   GET  http://localhost:{port}/admin/courts")
    print(f"\n✨ Frontend can now connect to this backend!")
    print(f"🔄 Press Ctrl+C to stop the server\n")
    
    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        print(f"\n🛑 Server stopped")
        httpd.server_close()

if __name__ == "__main__":
    port = int(sys.argv[1]) if len(sys.argv) > 1 else 3001
    run_server(port)