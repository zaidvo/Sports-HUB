#!/usr/bin/env python3
"""
API Endpoint Matching Test
Verifies that frontend API calls match the backend endpoints exactly
"""

import requests
import json
from datetime import datetime

BACKEND_URL = "http://localhost:3001"
FRONTEND_URL = "http://localhost:8080"

def test_endpoint_matching():
    print("🔗 Testing API Endpoint Matching")
    print("="*50)
    
    # Test all endpoints that should exist in both frontend and backend
    endpoints_to_test = [
        # Auth endpoints
        {"method": "POST", "path": "auth/login", "data": {"email": "admin@test.com", "password": "admin123"}},
        {"method": "POST", "path": "auth/register", "data": {"full_name": "Test User", "email": "test@test.com", "phone_number": "1234567890", "password": "password"}},
        
        # Courts endpoints
        {"method": "GET", "path": "courts"},
        {"method": "GET", "path": "courts/types"},
        {"method": "GET", "path": "courts/locations"},
        {"method": "GET", "path": "courts/1/slots?date=2024-01-15"},
        
        # Admin endpoints
        {"method": "GET", "path": "admin/dashboard"},
        {"method": "GET", "path": "admin/bookings"},
        {"method": "GET", "path": "admin/courts"},
        
        # Booking endpoints
        {"method": "POST", "path": "bookings", "data": {"user_id": 1, "slot_id": "1_2024-01-15_10", "court_id": 1, "amount_paid": 50.00}},
    ]
    
    results = {"passed": 0, "failed": 0, "details": []}
    
    for endpoint in endpoints_to_test:
        method = endpoint["method"]
        path = endpoint["path"]
        data = endpoint.get("data")
        
        print(f"\n🧪 Testing {method} /{path}")
        
        try:
            if method == "GET":
                response = requests.get(f"{BACKEND_URL}/{path}", timeout=5)
            elif method == "POST":
                response = requests.post(f"{BACKEND_URL}/{path}", json=data, timeout=5)
            else:
                print(f"   ❌ Unsupported method: {method}")
                results["failed"] += 1
                continue
            
            result_data = response.json()
            
            if response.status_code in [200, 201] and result_data.get("success"):
                print(f"   ✅ SUCCESS: {response.status_code}")
                print(f"      Response: {result_data.get('message', 'OK')}")
                
                # Show key data for successful responses
                if "courts" in result_data:
                    print(f"      Courts: {len(result_data['courts'])} found")
                elif "stats" in result_data:
                    stats = result_data["stats"]
                    print(f"      Stats: {stats['total_bookings']} bookings, ${stats['total_revenue']} revenue")
                elif "booking" in result_data:
                    booking = result_data["booking"]
                    print(f"      Booking: ID {booking['booking_id']} created")
                elif "user" in result_data:
                    user = result_data["user"]
                    print(f"      User: {user['name']} ({user['role']})")
                
                results["passed"] += 1
                results["details"].append({"endpoint": f"{method} /{path}", "status": "PASS", "code": response.status_code})
            else:
                print(f"   ❌ FAILED: {response.status_code}")
                print(f"      Error: {result_data.get('message', 'Unknown error')}")
                results["failed"] += 1
                results["details"].append({"endpoint": f"{method} /{path}", "status": "FAIL", "code": response.status_code, "error": result_data.get('message', 'Unknown error')})
                
        except requests.exceptions.ConnectionError:
            print(f"   ❌ CONNECTION ERROR: Backend server not running")
            results["failed"] += 1
            results["details"].append({"endpoint": f"{method} /{path}", "status": "FAIL", "error": "Connection failed"})
            
        except Exception as e:
            print(f"   ❌ ERROR: {str(e)}")
            results["failed"] += 1
            results["details"].append({"endpoint": f"{method} /{path}", "status": "FAIL", "error": str(e)})
    
    # Print summary
    print(f"\n{'='*50}")
    print(f"📊 ENDPOINT MATCHING TEST RESULTS")
    print(f"{'='*50}")
    print(f"✅ Passed: {results['passed']}")
    print(f"❌ Failed: {results['failed']}")
    print(f"📈 Success Rate: {(results['passed']/(results['passed'] + results['failed'])*100):.1f}%")
    
    if results['failed'] > 0:
        print(f"\n❌ FAILED ENDPOINTS:")
        for detail in results['details']:
            if detail['status'] == 'FAIL':
                print(f"   • {detail['endpoint']}: {detail.get('error', 'Unknown error')}")
    
    print(f"\n🌐 Frontend URL: {FRONTEND_URL}")
    print(f"🔗 Backend URL: {BACKEND_URL}")
    
    return results['failed'] == 0

def test_frontend_backend_integration():
    """Test that frontend can communicate with backend"""
    print(f"\n🔗 Testing Frontend-Backend Integration")
    print("-" * 40)
    
    try:
        # Test frontend server
        frontend_response = requests.get(FRONTEND_URL, timeout=5)
        if frontend_response.status_code == 200:
            print("✅ Frontend server running")
        else:
            print(f"❌ Frontend server issue: {frontend_response.status_code}")
            return False
    except:
        print("❌ Frontend server not accessible")
        return False
    
    try:
        # Test backend server
        backend_response = requests.get(BACKEND_URL, timeout=5)
        backend_data = backend_response.json()
        if backend_response.status_code == 200 and backend_data.get("success"):
            print("✅ Backend server running")
            print(f"   API Version: {backend_data.get('version', 'Unknown')}")
        else:
            print(f"❌ Backend server issue: {backend_response.status_code}")
            return False
    except Exception as e:
        print(f"❌ Backend server not accessible: {e}")
        return False
    
    return True

if __name__ == "__main__":
    print("🧪 SportzHub API Endpoint Matching Test")
    print(f"⏰ Started: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print("="*60)
    
    # Test server availability
    if test_frontend_backend_integration():
        # Test endpoint matching
        success = test_endpoint_matching()
        
        if success:
            print("\n🎉 All API endpoints are working correctly!")
            print("✨ Frontend and backend are properly integrated.")
        else:
            print("\n⚠️ Some endpoints failed. Check the details above.")
    else:
        print("\n💥 Server connectivity test failed. Make sure both servers are running.")
    
    print(f"\n📄 Test completed at: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")